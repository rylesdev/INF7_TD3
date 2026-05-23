<?php

namespace App\Controller;

use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profil')]
#[IsGranted('ROLE_USER')]
class ProfilController extends AbstractController
{
    #[Route('', name: 'app_profil', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(ProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                if (in_array($photoFile->getMimeType(), $allowedMimes, true)) {
                    $safeName    = $slugger->slug($user->getEmail()) ?? 'profil';
                    $newFilename = $safeName . '-' . bin2hex(random_bytes(4)) . '.' . $photoFile->guessExtension();
                    try {
                        $photoFile->move($this->getParameter('profils_photos_dir'), $newFilename);
                        $user->setPhotoProfil($newFilename);
                    } catch (FileException) {
                        $this->addFlash('error', "Erreur lors de l'upload de la photo.");
                    }
                }
            }

            $em->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profil/edit.html.twig', ['form' => $form->createView()]);
    }
}
