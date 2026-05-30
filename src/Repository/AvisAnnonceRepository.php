<?php

namespace App\Repository;

use App\Entity\AvisAnnonce;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AvisAnnonceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AvisAnnonce::class);
    }

    public function findByAnnonce(int $annonceId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.annonce = :id')
            ->setParameter('id', $annonceId)
            ->orderBy('a.creeLe', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByAuteurAndAnnonce(int $auteurId, int $annonceId): ?AvisAnnonce
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.auteur = :auteur')
            ->andWhere('a.annonce = :annonce')
            ->setParameter('auteur', $auteurId)
            ->setParameter('annonce', $annonceId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
