<?php

namespace App\Repository;

use App\Entity\Tache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tache::class);
    }

    public function findByColocation(int $colocationId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.colocation = :colId')
            ->setParameter('colId', $colocationId)
            ->orderBy('t.dateEcheance', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
