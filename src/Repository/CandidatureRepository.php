<?php

namespace App\Repository;

use App\Entity\Candidature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CandidatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidature::class);
    }

    public function findByLocataire(int $userId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.locataire = :id')
            ->setParameter('id', $userId)
            ->orderBy('c.creeLe', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByProprietaire(int $proprietaireId): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.annonce', 'a')
            ->join('a.colocation', 'col')
            ->andWhere('col.proprietaire = :id')
            ->setParameter('id', $proprietaireId)
            ->orderBy('c.creeLe', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByLocataireAnnonce(int $userId, int $annonceId): ?Candidature
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.locataire = :user')
            ->andWhere('c.annonce = :annonce')
            ->setParameter('user', $userId)
            ->setParameter('annonce', $annonceId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countEnAttenteByProprietaire(int $proprietaireId): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.annonce', 'a')
            ->join('a.colocation', 'col')
            ->andWhere('col.proprietaire = :id')
            ->andWhere('c.statut = :statut')
            ->setParameter('id', $proprietaireId)
            ->setParameter('statut', Candidature::STATUT_EN_ATTENTE)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
