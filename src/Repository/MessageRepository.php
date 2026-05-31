<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findNonLus(int $userId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.destinataire = :userId')
            ->andWhere('m.lu = false')
            ->setParameter('userId', $userId)
            ->orderBy('m.envoyeLe', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countNonLus(int $userId): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.destinataire = :userId')
            ->andWhere('m.lu = false')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function marquerLus(int $expediteurId, int $destinataireId, int $colocationId): void
    {
        $this->createQueryBuilder('m')
            ->update()
            ->set('m.lu', 'true')
            ->andWhere('m.expediteur = :exp')
            ->andWhere('m.destinataire = :dest')
            ->andWhere('m.colocation = :col')
            ->andWhere('m.lu = false')
            ->setParameter('exp', $expediteurId)
            ->setParameter('dest', $destinataireId)
            ->setParameter('col', $colocationId)
            ->getQuery()
            ->execute();
    }

    public function findAllConversationsForUser(int $userId): array
    {
        $messages = $this->createQueryBuilder('m')
            ->andWhere('m.expediteur = :uid OR m.destinataire = :uid')
            ->setParameter('uid', $userId)
            ->orderBy('m.envoyeLe', 'DESC')
            ->getQuery()
            ->getResult();

        // Une seule conversation par interlocuteur, peu importe la colocation
        $conversations = [];
        foreach ($messages as $msg) {
            $other = $msg->getExpediteur()->getId() === $userId
                ? $msg->getDestinataire()
                : $msg->getExpediteur();
            $key = $other->getId();
            if (!isset($conversations[$key])) {
                $conversations[$key] = [
                    'user'           => $other,
                    'colocation'     => $msg->getColocation(),
                    'dernierMessage' => mb_substr($msg->getContenu(), 0, 60),
                    'nonLus'         => 0,
                ];
            }
            if (!$msg->isLu() && $msg->getDestinataire()->getId() === $userId) {
                $conversations[$key]['nonLus']++;
            }
        }

        return array_values($conversations);
    }

    public function findAllBetweenUsers(int $user1Id, int $user2Id): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('(m.expediteur = :u1 AND m.destinataire = :u2) OR (m.expediteur = :u2 AND m.destinataire = :u1)')
            ->setParameter('u1', $user1Id)
            ->setParameter('u2', $user2Id)
            ->orderBy('m.envoyeLe', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findConversation(int $user1Id, int $user2Id, int $colocationId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.colocation = :colId')
            ->andWhere(
                '(m.expediteur = :u1 AND m.destinataire = :u2) OR (m.expediteur = :u2 AND m.destinataire = :u1)'
            )
            ->setParameter('colId', $colocationId)
            ->setParameter('u1', $user1Id)
            ->setParameter('u2', $user2Id)
            ->orderBy('m.envoyeLe', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
