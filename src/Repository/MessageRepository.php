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
