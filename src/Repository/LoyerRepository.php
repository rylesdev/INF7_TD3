<?php

namespace App\Repository;

use App\Entity\Loyer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LoyerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loyer::class);
    }

    public function findByColocation(int $colocationId): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.colocation = :colId')
            ->setParameter('colId', $colocationId)
            ->orderBy('l.annee', 'DESC')
            ->addOrderBy('l.mois', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
