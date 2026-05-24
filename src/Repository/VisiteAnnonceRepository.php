<?php

namespace App\Repository;

use App\Entity\VisiteAnnonce;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VisiteAnnonceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VisiteAnnonce::class);
    }

    public function findByAnnonce(int $annonceId): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.annonce = :id')
            ->setParameter('id', $annonceId)
            ->orderBy('v.visiteLe', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByAnnonce(int $annonceId): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->andWhere('v.annonce = :id')
            ->setParameter('id', $annonceId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return array<string, int>  date => count */
    public function countParJour(int $annonceId, int $jours = 30): array
    {
        $depuis = new \DateTimeImmutable("-$jours days");

        $rows = $this->createQueryBuilder('v')
            ->select("DATE(v.visiteLe) AS jour, COUNT(v.id) AS nb")
            ->andWhere('v.annonce = :id')
            ->andWhere('v.visiteLe >= :depuis')
            ->setParameter('id', $annonceId)
            ->setParameter('depuis', $depuis)
            ->groupBy('jour')
            ->orderBy('jour', 'ASC')
            ->getQuery()
            ->getScalarResult();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['jour']] = (int) $row['nb'];
        }
        return $result;
    }
}
