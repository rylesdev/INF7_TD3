<?php

namespace App\Repository;

use App\Entity\Tantieme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TantiemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tantieme::class);
    }

    public function findByProprietaire(int $proprietaireId): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.charge', 'ch')
            ->join('ch.colocation', 'col')
            ->join('t.chambre', 'cam')
            ->where('col.proprietaire = :id')
            ->setParameter('id', $proprietaireId)
            ->orderBy('col.nom', 'ASC')
            ->addOrderBy('ch.date', 'DESC')
            ->addOrderBy('cam.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
