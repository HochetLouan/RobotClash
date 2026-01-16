<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\Equipe;
use Doctrine\ORM\Query;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Competition>
 */
class CompetitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competition::class);
    }

    //    /**
    //     * @return TCOMPETITIONCPT[] Returns an array of TCOMPETITIONCPT objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TCOMPETITIONCPT
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * @return Competition[]
     */
    public function getAll(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function trouveUnParId($Id): ?Competition
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.id = :id')
            ->setParameter('id', $Id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function trouveCompetionInscription($Id): array
    {
        $query = $this->getEntityManager()->createQuery(
            '
        SELECT e
        FROM App\Entity\Equipe e
        WHERE e.competitionId = :competitionId
        ORDER BY e.id
        '
        )->setParameter('competitionId', $Id);

        return $query->getResult(Query::HYDRATE_ARRAY);
    }

    public function trouveCompetitionAvecEquipes(): array
    {
        $query = $this->getEntityManager()->createQuery(
            '
        SELECT c AS competition, e AS equipe
        FROM App\Entity\TCOMPETITIONCPT c
        LEFT JOIN App\Entity\Equipe e WITH e.competitionId = c.id
        ORDER BY c.id
        '
        );

        $rows = $query->getResult(Query::HYDRATE_ARRAY);

        $data = [];

        foreach ($rows as $row) {
            $competitionId = $row['competition']['id'];

            if (!isset($data[$competitionId])) {
                $data[$competitionId] = [
                    'competition' => $row['competition'],
                    'equipes' => [],
                ];
            }

            if ($row['equipe'] !== null) {
                $data[$competitionId]['equipes'][] = $row['equipe'];
            }
        }

        return array_values($data);
    }

    public function compteCompetitionParNom(string $q, string $status = 'all'): int
    {
        $qb = $this->createQueryBuilder('c')
        ->select('COUNT(c.id)');

        if ($q !== '') {
            $qb->andWhere('c.nom LIKE :q')
            ->setParameter('q', $q . '%');
        }

        $today = new \DateTimeImmutable('today');

        if ($status === 'upcoming') {
            $qb->andWhere('c.dateDebut > :today');
        } elseif ($status === 'ongoing') {
            $qb->andWhere('c.dateDebut <= :today AND c.dateFin >= :today');
        } elseif ($status === 'finished') {
            $qb->andWhere('c.dateFin < :today');
        }

        if ($status !== 'all') {
            $qb->setParameter('today', $today);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
    public function compteCompetitionParNomParOrga(string $q, int $id, string $status = 'all'): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)');

        if ($q !== '') {
            $qb->andWhere('c.nom LIKE :q')
                ->setParameter('q', $q . '%');
        }
        $qb->andWhere('c.idUTL = :id')
            ->setParameter('id', $id);

        $today = new \DateTimeImmutable('today');

        if ($status === 'upcoming') {
            $qb->andWhere('c.dateDebut > :today');
        } elseif ($status === 'ongoing') {
            $qb->andWhere('c.dateDebut <= :today AND c.dateFin >= :today');
        } elseif ($status === 'finished') {
            $qb->andWhere('c.dateFin < :today');
        }

        if ($status !== 'all') {
            $qb->setParameter('today', $today);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findCompetitionsByName(string $q, string $status = 'all', int $limit = 10, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('c')
            ->addSelect(
                "(CASE 
                    WHEN c.dateDebut <= CURRENT_DATE() AND c.dateFin >= CURRENT_DATE() THEN 1
                    WHEN c.dateDebut > CURRENT_DATE() THEN 2
                    ELSE 3
                END) AS HIDDEN competitionStatus"
            )
            ->orderBy('competitionStatus', 'ASC')
            ->addOrderBy('c.dateDebut', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($q !== '') {
            $qb->andWhere('c.nom LIKE :q')
            ->setParameter('q', $q . '%');
        }

        $today = new \DateTimeImmutable('today');

        if ($status === 'upcoming') {
            $qb->andWhere('c.dateDebut > :today');
        } elseif ($status === 'ongoing') {
            $qb->andWhere('c.dateDebut <= :today AND c.dateFin >= :today');
        } elseif ($status === 'finished') {
            $qb->andWhere('c.dateFin < :today');
        }

        if ($status !== 'all') {
            $qb->setParameter('today', $today);
        }

        return $qb->getQuery()->getResult();
    }

    public function trouveCompetitionParNomParCreateur(string $q, int $limit, int $offset, int $id, string $status = 'all')
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($q !== '') {
            $qb->andWhere('c.nom LIKE :q')
                ->setParameter('q', $q . '%');
        }
        $qb->andWhere('c.idUTL = :id')
            ->setParameter('id', $id);

        $today = new \DateTimeImmutable('today');

        if ($status === 'upcoming') {
            $qb->andWhere('c.dateDebut > :today');
        } elseif ($status === 'ongoing') {
            $qb->andWhere('c.dateDebut <= :today AND c.dateFin >= :today');
        } elseif ($status === 'finished') {
            $qb->andWhere('c.dateFin < :today');
        }

        if ($status !== 'all') {
            $qb->setParameter('today', $today);
        }

        return $qb->getQuery()->getResult();
    }

    public function trouveCompetitionParEquipe(int $equipeId)
    {
        return $this->createQueryBuilder('c')
            ->join('c.equipes', 'e')
            ->where('e.id = :equipeId')
            ->setParameter('equipeId', $equipeId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getAllParDate()
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.dateDebut', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
