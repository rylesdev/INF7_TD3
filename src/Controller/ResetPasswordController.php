<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    #[Route('', name: 'app_forgot_password')]
    public function forgotPassword(Request $request): Response
    {
        $submitted = false;
        if ($request->isMethod('POST')) {
            // Simulation : pas de vrai email envoyé
            $submitted = true;
        }

        return $this->render('security/forgot_password.html.twig', [
            'submitted' => $submitted,
        ]);
    }

    #[Route('/new', name: 'app_reset_password')]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        UserRepository $userRepo
    ): Response {
        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data  = $form->getData();
            $email = $data['email'] ?? '';
            $user  = $userRepo->findOneBy(['email' => strtolower(trim($email))]);

            if ($user) {
                $user->setPassword($hasher->hashPassword($user, $data['password']));
                $em->flush();
            }

            $this->addFlash('success', 'Mot de passe mis à jour avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', ['form' => $form->createView()]);
    }
}
