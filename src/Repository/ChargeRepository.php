<?php

namespace App\Repository;

use App\Entity\Charge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChargeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Charge::class);
    }

    public function findByColocationAndAnnee(int $colocationId, int $annee): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.colocation = :colId')
            ->andWhere('c.annee = :annee')
            ->setParameter('colId', $colocationId)
            ->setParameter('annee', $annee)
            ->orderBy('c.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalParTypeEtAnnee(int $colocationId, int $annee): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.type, SUM(c.montant) as total')
            ->andWhere('c.colocation = :colId')
            ->andWhere('c.annee = :annee')
            ->setParameter('colId', $colocationId)
            ->setParameter('annee', $annee)
            ->groupBy('c.type')
            ->getQuery()
            ->getResult();
    }
}
