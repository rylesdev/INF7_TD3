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

    public function hasVisited(int $annonceId, string $ip, ?int $userId = null): bool
    {
        $qb = $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->andWhere('v.annonce = :id')
            ->setParameter('id', $annonceId);

        if ($userId !== null) {
            $qb->andWhere('v.user = :userId')->setParameter('userId', $userId);
        } else {
            $qb->andWhere('v.ipAddress = :ip')->setParameter('ip', $ip);
        }

        return (bool) $qb->getQuery()->getSingleScalarResult();
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
        $depuis = (new \DateTimeImmutable("-$jours days"))->format('Y-m-d H:i:s');

        $conn = $this->getEntityManager()->getConnection();
        $sql  = 'SELECT DATE(visite_le) AS jour, COUNT(id) AS nb
                 FROM visite_annonce
                 WHERE annonce_id = :id AND visite_le >= :depuis
                 GROUP BY DATE(visite_le)
                 ORDER BY jour ASC';

        $rows = $conn->executeQuery($sql, ['id' => $annonceId, 'depuis' => $depuis])->fetchAllAssociative();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['jour']] = (int) $row['nb'];
        }
        return $result;
    }
}
