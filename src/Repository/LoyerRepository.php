<?php

namespace App\Repository;

use App\Entity\Loyer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class LoyerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loyer::class);
    }

    public function marquerEnRetard(EntityManagerInterface $em): int
    {
        return $this->createQueryBuilder('l')
            ->update()
            ->set('l.statut', ':retard')
            ->andWhere('l.statut = :impaye')
            ->andWhere('l.dateEcheance < :now')
            ->setParameter('retard', Loyer::STATUT_EN_RETARD)
            ->setParameter('impaye', Loyer::STATUT_IMPAYE)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }

    public function findByColocation(int $colocationId): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.colocation = :colId')
            ->setParameter('colId', $colocationId)
            ->orderBy('l.annee', 'DESC')
            ->addOrderBy('l.mois', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
