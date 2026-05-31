<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/notifications')]
#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    #[Route('/count', name: 'app_notifications_count')]
    public function count(NotificationRepository $repo): JsonResponse
    {
        $count = count($repo->findNonLues($this->getUser()->getId()));
        return $this->json(['count' => $count]);
    }

    #[Route('/messages/count', name: 'app_messages_count')]
    public function messagesCount(MessageRepository $messageRepo): JsonResponse
    {
        $count = $messageRepo->countNonLus($this->getUser()->getId());
        return $this->json(['count' => $count]);
    }

    #[Route('/mark-all-read', name: 'app_notifications_mark_read', methods: ['POST'])]
    public function markAllRead(NotificationRepository $repo, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\Response
    {
        $notifications = $repo->findNonLues($this->getUser()->getId());
        foreach ($notifications as $n) {
            $n->setLue(true);
        }
        $em->flush();

        return $this->redirectToRoute('app_locataire_notifications');
    }
}
