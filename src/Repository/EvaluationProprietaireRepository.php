<?php

namespace App\Repository;

use App\Entity\EvaluationProprietaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvaluationProprietaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvaluationProprietaire::class);
    }

    public function findByProprietaire(int $proprietaireId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.proprietaire = :id')
            ->setParameter('id', $proprietaireId)
            ->orderBy('e.creeLe', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByLocataire(int $locataireId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.locataire = :id')
            ->setParameter('id', $locataireId)
            ->orderBy('e.creeLe', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByParties(int $locataireId, int $proprietaireId, int $colocationId): ?EvaluationProprietaire
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.locataire = :loc')
            ->andWhere('e.proprietaire = :pro')
            ->andWhere('e.colocation = :col')
            ->setParameter('loc', $locataireId)
            ->setParameter('pro', $proprietaireId)
            ->setParameter('col', $colocationId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function moyenneNoteProprietaire(int $proprietaireId): float
    {
        $result = $this->createQueryBuilder('e')
            ->select('AVG(e.note) as moyenne')
            ->andWhere('e.proprietaire = :id')
            ->setParameter('id', $proprietaireId)
            ->getQuery()
            ->getSingleScalarResult();
        return round((float) $result, 1);
    }
}
