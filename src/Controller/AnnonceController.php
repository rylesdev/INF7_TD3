<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\AvisAnnonce;
use App\Entity\Notification;
use App\Entity\PhotoAnnonce;
use App\Entity\VisiteAnnonce;
use App\Form\AnnonceType;
use App\Form\AvisAnnonceType;
use App\Repository\AnnonceRepository;
use App\Repository\AvisAnnonceRepository;
use App\Repository\CandidatureRepository;
use App\Repository\EvaluationProprietaireRepository;
use App\Repository\LoyerRepository;
use App\Repository\VisiteAnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $perPage = 6;
        $page    = max(1, (int) $request->query->get('page', 1));
        $filters = [
            'ville'    => $request->query->get('ville', ''),
            'prix_min' => $request->query->get('prix_min', ''),
            'prix_max' => $request->query->get('prix_max', ''),
        ];

        $annonces    = $repo->findDisponiblesFiltered($filters, $page, $perPage);
        $total       = $repo->countDisponiblesFiltered($filters);
        $totalPages  = max(1, (int) ceil($total / $perPage));
        $villes      = $repo->findVillesDisponibles();

        return $this->render('annonce/index.html.twig', [
            'annonces'   => $annonces,
            'filters'    => $filters,
            'villes'     => $villes,
            'page'       => $page,
            'totalPages' => $totalPages,
            'total'      => $total,
        ]);
    }

    #[Route('/{id}', name: 'app_annonce_show', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function show(Annonce $annonce, EntityManagerInterface $em, Request $request, VisiteAnnonceRepository $visiteRepo, AvisAnnonceRepository $avisRepo, LoyerRepository $loyerRepo, CandidatureRepository $candidatureRepo, EvaluationProprietaireRepository $evalProRepo): Response
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
        $peutNoter = false;
        if ($this->isGranted('ROLE_USER') && !$this->isGranted('ROLE_PROPRIETAIRE')) {
            $user = $this->getUser();
            $dejaAvis = $avisRepo->findByAuteurAndAnnonce($user->getId(), $annonce->getId()) !== null;

            // Vérifie si le locataire a un loyer payé pour cette colocation
            $colocation = $annonce->getColocation();
            if ($colocation) {
                $loyersPaies = $loyerRepo->findBy(['colocation' => $colocation, 'statut' => 'payé']);
                foreach ($loyersPaies as $l) {
                    if ($l->getChambre()?->getLocataire()?->getId() === $user->getId()) {
                        $peutNoter = true;
                        break;
                    }
                }
            }

            if (!$dejaAvis && $peutNoter) {
                $avis = new AvisAnnonce();
                $avisForm = $this->createForm(AvisAnnonceType::class, $avis);
                $avisForm->handleRequest($request);
                if ($avisForm->isSubmitted() && $avisForm->isValid()) {
                    $avis->setAnnonce($annonce);
                    $avis->setAuteur($user);
                    $em->persist($avis);

                    $proprio = $annonce->getColocation()?->getProprietaire();
                    if ($proprio) {
                        $notif = new Notification();
                        $notif->setUser($proprio);
                        $notif->setType(Notification::TYPE_INFO);
                        $notif->setTitre('Nouvel avis sur votre annonce');
                        $notif->setMessage($user->getPrenom() . ' a laisse un avis sur "' . $annonce->getTitre() . '".');
                        $em->persist($notif);
                    }

                    $em->flush();
                    $this->addFlash('success', 'Votre avis a été publié. Merci !');
                    return $this->redirectToRoute('app_annonce_show', ['id' => $annonce->getId()]);
                }
            }
        }

        $dejaCandidat    = false;
        $aDejaUneChambre = false;
        if ($this->isGranted('ROLE_USER') && !$this->isGranted('ROLE_PROPRIETAIRE')) {
            $user            = $this->getUser();
            $dejaCandidat    = $candidatureRepo->findOneByLocataireAnnonce($user->getId(), $annonce->getId()) !== null;
            $aDejaUneChambre = $user->getChambres()->count() > 0;
        }

        $proprioId     = $annonce->getColocation()?->getProprietaire()?->getId();
        $moyenneProprio = $proprioId ? $evalProRepo->moyenneNoteProprietaire($proprioId) : 0;
        $nbAvisProprio  = $proprioId ? count($evalProRepo->findByProprietaire($proprioId)) : 0;

        return $this->render('annonce/show.html.twig', [
            'annonce'         => $annonce,
            'avis'            => $avisRepo->findByAnnonce($annonce->getId()),
            'avisForm'        => $avisForm?->createView(),
            'dejaAvis'        => $dejaAvis,
            'peutNoter'       => $peutNoter,
            'dejaCandidat'    => $dejaCandidat,
            'aDejaUneChambre' => $aDejaUneChambre,
            'moyenneProprio'  => $moyenneProprio,
            'nbAvisProprio'   => $nbAvisProprio,
        ]);
    }

    #[Route('/new', name: 'app_annonce_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROPRIETAIRE')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        \App\Repository\ColocationRepository $colRepo
    ): Response {
        $annonce = new Annonce();
        $form    = $this->createForm(AnnonceType::class, $annonce, [
            'colocations' => $colRepo->findByProprietaire($this->getUser()->getId()),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $col = $annonce->getColocation();
            if ($col?->getProprietaire()?->getId() !== $this->getUser()->getId()) {
                throw $this->createAccessDeniedException();
            }
            $this->handlePhotosUpload($form, $annonce, $slugger);
            $em->persist($annonce);
            $em->flush();

            $this->addFlash('success', 'Annonce "' . $annonce->getTitre() . '" créée avec succès.');
            return $this->redirectToRoute('app_annonce_show', ['id' => $annonce->getId()]);
        }

        return $this->render('annonce/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}/edit', name: 'app_annonce_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_PROPRIETAIRE')]
    public function edit(
        Annonce $annonce,
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        \App\Repository\ColocationRepository $colRepo
    ): Response {
        if ($annonce->getColocation()?->getProprietaire()?->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(AnnonceType::class, $annonce, [
            'colocations' => $colRepo->findByProprietaire($this->getUser()->getId()),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePhotosUpload($form, $annonce, $slugger);
            $em->flush();

            $this->addFlash('success', 'Annonce mise à jour.');
            return $this->redirectToRoute('app_proprietaire_annonces');
        }

        $sortedPhotos = $annonce->getPhotos()->toArray();
        usort($sortedPhotos, fn($a, $b) => $a->getPosition() <=> $b->getPosition());

        return $this->render('annonce/edit.html.twig', [
            'form'         => $form->createView(),
            'annonce'      => $annonce,
            'sortedPhotos' => $sortedPhotos,
        ]);
    }

    #[Route('/photos/{photoId}/delete', name: 'app_photo_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PROPRIETAIRE')]
    public function deletePhoto(int $photoId, Request $request, EntityManagerInterface $em): Response
    {
        $photo = $em->find(PhotoAnnonce::class, $photoId);
        if (!$photo) {
            throw $this->createNotFoundException();
        }
        $annonce = $photo->getAnnonce();
        if ($annonce->getColocation()?->getProprietaire()?->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }
        if ($annonce->getPhotos()->count() <= 1) {
            $this->addFlash('error', 'Vous devez conserver au moins une photo.');
            return $this->redirectToRoute('app_annonce_edit', ['id' => $annonce->getId()]);
        }
        if ($this->isCsrfTokenValid('delete_photo_' . $photoId, $request->request->get('_token'))) {
            $this->deletePhotoFile($photo->getFilename());
            $em->remove($photo);
            $em->flush();
        }
        return $this->redirectToRoute('app_annonce_edit', ['id' => $annonce->getId()]);
    }

    #[Route('/{id}/photos/reorder', name: 'app_annonce_photos_reorder', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_PROPRIETAIRE')]
    public function reorderPhotos(Annonce $annonce, Request $request, EntityManagerInterface $em): JsonResponse
    {
        if ($annonce->getColocation()?->getProprietaire()?->getId() !== $this->getUser()->getId()) {
            return $this->json(['error' => 'Acces refuse'], 403);
        }
        $data  = json_decode($request->getContent(), true);
        $order = $data['order'] ?? [];
        foreach ($annonce->getPhotos() as $photo) {
            $pos = array_search($photo->getId(), $order);
            if ($pos !== false) {
                $photo->setPosition((int) $pos);
            }
        }
        $em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/{id}/delete', name: 'app_annonce_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_PROPRIETAIRE')]
    public function delete(Annonce $annonce, Request $request, EntityManagerInterface $em): Response
    {
        if ($annonce->getColocation()?->getProprietaire()?->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }
        if ($this->isCsrfTokenValid('delete_annonce_' . $annonce->getId(), $request->request->get('_token'))) {
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
