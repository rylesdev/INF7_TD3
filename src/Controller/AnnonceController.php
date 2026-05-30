<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\AvisAnnonce;
use App\Entity\PhotoAnnonce;
use App\Entity\VisiteAnnonce;
use App\Form\AnnonceType;
use App\Form\AvisAnnonceType;
use App\Repository\AnnonceRepository;
use App\Repository\AvisAnnonceRepository;
use App\Repository\VisiteAnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/annonces')]
class AnnonceController extends AbstractController
{
    #[Route('', name: 'app_annonces', methods: ['GET'])]
    public function index(AnnonceRepository $repo, Request $request): Response
    {
        $annonces = $repo->findDisponibles();

        return $this->render('annonce/index.html.twig', [
            'annonces' => $annonces,
        ]);
    }

    #[Route('/{id}', name: 'app_annonce_show', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function show(Annonce $annonce, EntityManagerInterface $em, Request $request, VisiteAnnonceRepository $visiteRepo, AvisAnnonceRepository $avisRepo): Response
    {
        $ip = $request->getClientIp() ?? 'unknown';
        $userId = $this->isGranted('ROLE_USER') ? $this->getUser()->getId() : null;
        if (!$visiteRepo->hasVisited($annonce->getId(), $ip, $userId)) {
            $visite = new VisiteAnnonce();
            $visite->setAnnonce($annonce);
            $visite->setIpAddress($ip);
            if ($userId !== null) {
                $visite->setUser($this->getUser());
            }
            $em->persist($visite);
            $em->flush();
        }

        $avisForm = null;
        $dejaAvis = false;
        if ($this->isGranted('ROLE_USER') && !$this->isGranted('ROLE_PROPRIETAIRE')) {
            $dejaAvis = $avisRepo->findByAuteurAndAnnonce($this->getUser()->getId(), $annonce->getId()) !== null;
            if (!$dejaAvis) {
                $avis = new AvisAnnonce();
                $avisForm = $this->createForm(AvisAnnonceType::class, $avis);
                $avisForm->handleRequest($request);
                if ($avisForm->isSubmitted() && $avisForm->isValid()) {
                    $avis->setAnnonce($annonce);
                    $avis->setAuteur($this->getUser());
                    $em->persist($avis);
                    $em->flush();
                    $this->addFlash('success', 'Votre avis a été publié. Merci !');
                    return $this->redirectToRoute('app_annonce_show', ['id' => $annonce->getId()]);
                }
            }
        }

        return $this->render('annonce/show.html.twig', [
            'annonce'  => $annonce,
            'avis'     => $avisRepo->findByAnnonce($annonce->getId()),
            'avisForm' => $avisForm?->createView(),
            'dejaAvis' => $dejaAvis,
        ]);
    }

    #[Route('/new', name: 'app_annonce_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROPRIETAIRE')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $annonce = new Annonce();
        $form    = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePhotosUpload($form, $annonce, $slugger);
            $em->persist($annonce);
            $em->flush();

            $this->addFlash('success', 'Annonce créée avec succès.');
            return $this->redirectToRoute('app_proprietaire_annonces');
        }

        return $this->render('annonce/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}/edit', name: 'app_annonce_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_PROPRIETAIRE')]
    public function edit(
        Annonce $annonce,
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePhotosUpload($form, $annonce, $slugger);
            $em->flush();

            $this->addFlash('success', 'Annonce mise à jour.');
            return $this->redirectToRoute('app_proprietaire_annonces');
        }

        return $this->render('annonce/edit.html.twig', [
            'form'    => $form->createView(),
            'annonce' => $annonce,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_annonce_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_PROPRIETAIRE')]
    public function delete(Annonce $annonce, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $annonce->getId(), $request->request->get('_token'))) {
            foreach ($annonce->getPhotos() as $photo) {
                $this->deletePhotoFile($photo->getFilename());
            }
            $em->remove($annonce);
            $em->flush();
            $this->addFlash('success', 'Annonce supprimée.');
        }

        return $this->redirectToRoute('app_proprietaire_annonces');
    }

    private function handlePhotosUpload($form, Annonce $annonce, SluggerInterface $slugger): void
    {
        $photoFiles = $form->get('photoFiles')->getData();
        if (!$photoFiles) return;

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $position     = $annonce->getPhotos()->count();

        foreach ($photoFiles as $photoFile) {
            if (!in_array($photoFile->getMimeType(), $allowedMimes, true)) continue;

            $originalName = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName     = $slugger->slug($originalName) ?? 'annonce';
            $newFilename  = $safeName . '-' . bin2hex(random_bytes(6)) . '.' . $photoFile->guessExtension();

            try {
                $photoFile->move($this->getParameter('annonces_photos_dir'), $newFilename);
                $photo = new PhotoAnnonce();
                $photo->setFilename($newFilename);
                $photo->setAlt($annonce->getTitre());
                $photo->setPosition($position++);
                $annonce->addPhoto($photo);
            } catch (FileException) {
                $this->addFlash('error', "Erreur lors de l'upload d'une photo.");
            }
        }
    }

    private function deletePhotoFile(string $filename): void
    {
        $uploadDir = realpath($this->getParameter('annonces_photos_dir'));
        $safeFile  = $uploadDir . DIRECTORY_SEPARATOR . basename($filename);
        if (file_exists($safeFile) && str_starts_with($safeFile, $uploadDir)) {
            unlink($safeFile);
        }
    }
}
