<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function findNonLues(int $userId): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :userId')
            ->andWhere('n.lue = false')
            ->setParameter('userId', $userId)
            ->orderBy('n.creeLe', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllByUser(int $userId): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('n.creeLe', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
