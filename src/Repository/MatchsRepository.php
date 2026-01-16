<?php

namespace App\Repository;

use App\Entity\Matchs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Equipe;

/**
 * @extends ServiceEntityRepository<Matchs>
 */
class MatchsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Matchs::class);
    }

    public function trouveEquipesParCompetition(int $competitionId): array
    {
        $matchs = $this->createQueryBuilder('m')
            ->select('m.id, m.score, m.commentaire, m.statusMatchId, m.forfaitEquipeA, m.forfaitEquipeB, m.horaire, m.terrain, ea.nom AS equipeA, eb.nom AS equipeB, ea.seqId AS statusA, eb.seqId AS statusB, 
        ea.dateCreation AS dateA, eb.dateCreation AS dateB')
            ->join('App\Entity\Equipe', 'ea', 'WITH', 'ea.id = m.equipeAId')
            ->join('App\Entity\Equipe', 'eb', 'WITH', 'eb.id = m.equipeBId')
            ->where('m.competitionId = :cid')
            ->andWhere('m.statusMatchId != 2')
            ->setParameter('cid', $competitionId)
            ->orderBy('m.id', 'DESC')
            ->getQuery()
            ->getArrayResult();

        foreach ($matchs as &$m) {
            $score = $m['score'];
            [$scoreA, $scoreB] = explode('-', $score);
            $m['scoreA'] = (int) $scoreA;
            $m['scoreB'] = (int) $scoreB;
            $m['score'] = $score;
        }

        return $matchs;
    }


    public function trouveEquipesParCompetitionPagine(int $competitionId, int $limit, int $offset): array
    {
        $matchs = $this->createQueryBuilder('m')
            ->select('m.id, m.score, m.commentaire, m.forfaitEquipeA, m.forfaitEquipeB, m.statusMatchId, m.horaire,
                 ea.nom AS equipeA, eb.nom AS equipeB, ea.seqId AS statusA, eb.seqId AS statusB, ea.dateCreation AS dateA, eb.dateCreation AS dateB')
            ->join('App\Entity\Equipe', 'ea', 'WITH', 'ea.id = m.equipeAId')
            ->join('App\Entity\Equipe', 'eb', 'WITH', 'eb.id = m.equipeBId')
            ->where('m.competitionId = :cid')
            ->setParameter('cid', $competitionId)
            ->orderBy('m.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        foreach ($matchs as &$m) {
            [$scoreA, $scoreB] = explode('-', $m['score']);
            $m['scoreA'] = (int) $scoreA;
            $m['scoreB'] = (int) $scoreB;
        }

        return $matchs;
    }

    public function compteParCompetition(int $competitionId): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.competitionId = :cid')
            ->setParameter('cid', $competitionId)
            ->getQuery()
            ->getSingleScalarResult();
    }



    public function findMatchsValidesParCompetition(int $competitionId): array
    {
        return $this->createQueryBuilder('m')
        ->join('App\Entity\Equipe', 'ea', 'WITH', 'ea.id = m.equipeAId')
        ->join('App\Entity\Equipe', 'eb', 'WITH', 'eb.id = m.equipeBId')
        ->where('m.competitionId = :cid')
        ->andWhere('m.statusMatchId != 2')
        ->andWhere('ea.seqId = 1')
        ->andWhere('eb.seqId = 1')
        ->setParameter('cid', $competitionId)
        ->getQuery()
        ->getResult();
    }

    public function findMatchsByPhase(int $competitionId, string $phasePrefix): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.competitionId = :cid')
            ->andWhere('m.commentaire LIKE :phase')
            ->andWhere('m.statusMatchId != 2')
            ->setParameter('cid', $competitionId)
            ->setParameter('phase', $phasePrefix . '%')
            ->orderBy('m.commentaire', 'ASC')
            ->getQuery()
            ->getResult();
    }



    //    /**
    //     * @return Matchs[] Returns an array of Matchs objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Matchs
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
