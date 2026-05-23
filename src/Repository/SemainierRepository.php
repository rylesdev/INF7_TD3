<?php

namespace App\Repository;

use App\Entity\Semainier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SemainierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Semainier::class);
    }

    public function findSemaineCourante(int $colocationId): array
    {
        $debut = new \DateTime('monday this week');
        $fin = new \DateTime('sunday this week');

        return $this->createQueryBuilder('s')
            ->join('s.tache', 't')
            ->andWhere('t.colocation = :colId')
            ->andWhere('s.dateDebut >= :debut')
            ->andWhere('s.dateFin <= :fin')
            ->setParameter('colId', $colocationId)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('s.jourSemaine', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
