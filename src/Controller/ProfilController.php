<?php

namespace App\Controller;

use App\Form\ProfilType;
use App\Repository\AnnonceRepository;
use App\Repository\EvaluationLocataireRepository;
use App\Repository\EvaluationProprietaireRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profil')]
class ProfilController extends AbstractController
{
    #[Route('', name: 'app_profil', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function monProfil(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        AnnonceRepository $annonceRepo,
        EvaluationLocataireRepository $evalLocRepo,
        EvaluationProprietaireRepository $evalProRepo
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(ProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $safeName    = $slugger->slug(pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME));
                $newFilename = $safeName . '-' . bin2hex(random_bytes(4)) . '.' . $photoFile->guessExtension();
                $uploadDir   = $this->getParameter('kernel.project_dir') . '/public/uploads/profils';
                try {
                    $photoFile->move($uploadDir, $newFilename);
                    $user->setPhotoProfil($newFilename);
                } catch (FileException) {
                    $this->addFlash('error', "Erreur lors de l'upload de la photo.");
                }
            }
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour.');
            return $this->redirectToRoute('app_profil');
        }

        $isProprio   = $this->isGranted('ROLE_PROPRIETAIRE');
        $annonces    = $isProprio ? $annonceRepo->findByProprietaire($user->getId()) : [];
        $evaluations = $isProprio
            ? $evalLocRepo->findByProprietaire($user->getId())
            : $evalLocRepo->findByLocataire($user->getId());
        $evalDonnees = $isProprio ? [] : $evalProRepo->findByLocataire($user->getId());
        $moyenne     = $isProprio
            ? $evalProRepo->moyenneNoteProprietaire($user->getId())
            : $evalLocRepo->moyenneNoteLocataire($user->getId());

        return $this->render('profil/edit.html.twig', [
            'form'        => $form->createView(),
            'annonces'    => $annonces,
            'evaluations' => $evaluations,
            'evalDonnees' => $evalDonnees,
            'moyenne'     => $moyenne,
            'isProprio'   => $isProprio,
        ]);
    }

    #[Route('/proprietaire/{id}', name: 'app_profil_proprietaire', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function proprietaire(
        int $id,
        UserRepository $userRepo,
        AnnonceRepository $annonceRepo,
        EvaluationProprietaireRepository $evalRepo
    ): Response {
        $proprietaire = $userRepo->find($id);
        if (!$proprietaire || !in_array('ROLE_PROPRIETAIRE', $proprietaire->getRoles())) {
            throw $this->createNotFoundException('Proprietaire introuvable.');
        }

        $annonces    = $annonceRepo->findByProprietaire($id);
        $evaluations = $evalRepo->findByProprietaire($id);
        $moyenne     = $evalRepo->moyenneNoteProprietaire($id);

        return $this->render('profil/proprietaire.html.twig', [
            'proprietaire' => $proprietaire,
            'annonces'     => $annonces,
            'evaluations'  => $evaluations,
            'moyenne'      => $moyenne,
        ]);
    }

    #[Route('/locataire/{id}', name: 'app_profil_locataire', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function locataire(
        int $id,
        UserRepository $userRepo,
        EvaluationLocataireRepository $evalLocRepo,
        EvaluationProprietaireRepository $evalProRepo
    ): Response {
        $locataire = $userRepo->find($id);
        if (!$locataire) {
            throw $this->createNotFoundException('Locataire introuvable.');
        }

        $evaluationsRecues = $evalLocRepo->findByLocataire($id);
        $evalDonnees       = $evalProRepo->findByLocataire($id);
        $moyenne           = $evalLocRepo->moyenneNoteLocataire($id);

        return $this->render('profil/locataire.html.twig', [
            'locataire'         => $locataire,
            'evaluationsRecues' => $evaluationsRecues,
            'evalDonnees'       => $evalDonnees,
            'moyenne'           => $moyenne,
        ]);
    }
}
