<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findOnlyUsers(int $limit, int $offset): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.role = :roleUser')
            ->setParameter('roleUser', 2)
            ->orderBy('u.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function countOnlyUsers(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.role = :roleUser')
            ->setParameter('roleUser', 2)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countUsersSearch(string $q, string $field): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.role = :role')
            ->setParameter('role', 2);

        if ($q !== '') {
            $qLike = $q . '%';

            if ($field === 'nom') {
                $qb->andWhere('u.nom LIKE :q')->setParameter('q', $qLike);
            } elseif ($field === 'prenom') {
                $qb->andWhere('u.prenom LIKE :q')->setParameter('q', $qLike);
            } elseif ($field === 'email') {
                $qb->andWhere('u.email LIKE :q')->setParameter('q', $qLike);
            } else {
                $qb->andWhere('u.nom LIKE :q OR u.prenom LIKE :q OR u.email LIKE :q')
                ->setParameter('q', $qLike);
            }
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findUsersSearch(string $q, string $field, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('u')
            ->andWhere('u.role = :role')
            ->setParameter('role', 2)
            ->orderBy('u.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($q !== '') {
            $qLike = $q . '%';

            if ($field === 'nom') {
                $qb->andWhere('u.nom LIKE :q')->setParameter('q', $qLike);
            } elseif ($field === 'prenom') {
                $qb->andWhere('u.prenom LIKE :q')->setParameter('q', $qLike);
            } elseif ($field === 'email') {
                $qb->andWhere('u.email LIKE :q')->setParameter('q', $qLike);
            } else {
                $qb->andWhere('u.nom LIKE :q OR u.prenom LIKE :q OR u.email LIKE :q')
                ->setParameter('q', $qLike);
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function countOrganisateursSearch(string $q, string $field): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.role = :role')
            ->setParameter('role', 1);

        if ($q !== '') {
            $qLike = $q . '%';

            if ($field === 'nom') {
                $qb->andWhere('u.nom LIKE :q')->setParameter('q', $qLike);
            } elseif ($field === 'prenom') {
                $qb->andWhere('u.prenom LIKE :q')->setParameter('q', $qLike);
            } elseif ($field === 'email') {
                $qb->andWhere('u.email LIKE :q')->setParameter('q', $qLike);
            } else {
                $qb->andWhere('u.nom LIKE :q OR u.prenom LIKE :q OR u.email LIKE :q')
                ->setParameter('q', $qLike);
            }
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findOrganisateursSearch(string $q, string $field, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('u')
            ->andWhere('u.role = :role')
            ->setParameter('role', 1)
            ->orderBy('u.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($q !== '') {
            $qLike = $q . '%';

            if ($field === 'nom') {
                $qb->andWhere('u.nom LIKE :q')->setParameter('q', $qLike);
            } elseif ($field === 'prenom') {
                $qb->andWhere('u.prenom LIKE :q')->setParameter('q', $qLike);
            } elseif ($field === 'email') {
                $qb->andWhere('u.email LIKE :q')->setParameter('q', $qLike);
            } else {
                $qb->andWhere('u.nom LIKE :q OR u.prenom LIKE :q OR u.email LIKE :q')
                ->setParameter('q', $qLike);
            }
        }

        return $qb->getQuery()->getResult();
    }
    public function findEquipeByUserId(int $userId): array
    {
        return $this->createQueryBuilder('e')
            ->join('e.users', 'u')
            ->where('u.id = :id')
            ->setParameter('id', $userId)
            ->getQuery()
            ->getResult();
    }
}
