<?php

namespace App\Repository;

use App\Entity\EvaluationLocataire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvaluationLocataireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvaluationLocataire::class);
    }

    public function findByLocataire(int $locataireId): array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.colocation', 'c')
            ->andWhere('e.locataire = :id')
            ->setParameter('id', $locataireId)
            ->orderBy('e.creeLe', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByProprietaire(int $proprietaireId): array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.colocation', 'c')
            ->andWhere('e.proprietaire = :id')
            ->setParameter('id', $proprietaireId)
            ->orderBy('e.creeLe', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function moyenneNoteProprietaire(int $proprietaireId): float
    {
        $result = $this->createQueryBuilder('e')
            ->select('AVG(e.note)')
            ->innerJoin('e.colocation', 'c')
            ->andWhere('e.proprietaire = :id')
            ->setParameter('id', $proprietaireId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? round((float) $result, 1) : 0.0;
    }

    public function findOneByParties(int $locataireId, int $proprietaireId, int $colocationId): ?EvaluationLocataire
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.locataire = :loc')
            ->andWhere('e.proprietaire = :prop')
            ->andWhere('e.colocation = :col')
            ->setParameter('loc', $locataireId)
            ->setParameter('prop', $proprietaireId)
            ->setParameter('col', $colocationId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function moyenneNoteLocataire(int $locataireId): float
    {
        $result = $this->createQueryBuilder('e')
            ->select('AVG(e.note)')
            ->andWhere('e.locataire = :id')
            ->setParameter('id', $locataireId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? round((float) $result, 1) : 0.0;
    }
}
