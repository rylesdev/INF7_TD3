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
            ->innerJoin('e.colocation', 'c')
            ->andWhere('e.proprietaire = :id')
            ->setParameter('id', $proprietaireId)
            ->orderBy('e.creeLe', 'DESC')
            ->getQuery()
            ->getResult();
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

    public function findTopRated(int $limit = 3): array
    {
        $conn = $this->getEntityManager()->getConnection();
        return $conn->executeQuery('
            SELECT u.id, u.prenom, u.nom, u.photo_profil,
                   ROUND(AVG(e.note), 1) AS moyenne,
                   COUNT(e.id) AS nb_avis
            FROM evaluation_proprietaire e
            INNER JOIN user u ON u.id = e.proprietaire_id
            GROUP BY e.proprietaire_id, u.id, u.prenom, u.nom, u.photo_profil
            HAVING COUNT(e.id) >= 1
            ORDER BY moyenne DESC, nb_avis DESC
            LIMIT ' . (int) $limit
        )->fetchAllAssociative();
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
