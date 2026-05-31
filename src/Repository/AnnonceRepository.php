<?php

namespace App\Repository;

use App\Entity\Annonce;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AnnonceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Annonce::class);
    }

    public function findDisponibles(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.statut = :statut')
            ->setParameter('statut', Annonce::STATUT_DISPONIBLE)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findDisponiblesFiltered(array $filters = [], int $page = 1, int $perPage = 6): array
    {
        $qb = $this->buildFilteredQuery($filters)
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);
        return $qb->getQuery()->getResult();
    }

    public function countDisponiblesFiltered(array $filters = []): int
    {
        return (int) $this->buildFilteredQuery($filters)
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findVillesDisponibles(): array
    {
        $rows = $this->createQueryBuilder('a')
            ->select('DISTINCT c.ville')
            ->join('a.colocation', 'c')
            ->andWhere('a.statut = :statut')
            ->setParameter('statut', Annonce::STATUT_DISPONIBLE)
            ->orderBy('c.ville', 'ASC')
            ->getQuery()
            ->getScalarResult();
        return array_column($rows, 'ville');
    }

    private function buildFilteredQuery(array $filters): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.colocation', 'c')
            ->andWhere('a.statut = :statut')
            ->setParameter('statut', Annonce::STATUT_DISPONIBLE)
            ->orderBy('a.createdAt', 'DESC');

        if (!empty($filters['ville'])) {
            $qb->andWhere('c.ville = :ville')->setParameter('ville', $filters['ville']);
        }
        if (isset($filters['prix_min']) && $filters['prix_min'] !== '') {
            $qb->andWhere('a.prix >= :pmin')->setParameter('pmin', $filters['prix_min']);
        }
        if (isset($filters['prix_max']) && $filters['prix_max'] !== '') {
            $qb->andWhere('a.prix <= :pmax')->setParameter('pmax', $filters['prix_max']);
        }
        return $qb;
    }

    public function findByProprietaire(int $userId): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.colocation', 'c')
            ->andWhere('c.proprietaire = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
