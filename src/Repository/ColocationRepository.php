<?php

namespace App\Repository;

use App\Entity\Colocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ColocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Colocation::class);
    }

    public function findByProprietaire(int $userId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.proprietaire = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
