<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Roles;
use App\Entity\Communaute;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Roles|null find($id, $lockMode = null, $lockVersion = null)
 * @method Roles|null findOneBy(array $criteria, array $orderBy = null)
 * @method Roles[]    findAll()
 * @method Roles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RolesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Roles::class);
    }

    // /**
    //  * @return Roles[] Returns an array of Roles objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Roles
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param Communaute $communaute
     * @param User $user
     * @throws NonUniqueResultException
     */
    public function findRoleCommunity(Communaute $communaute, User $user)
    {
        return $this->createQueryBuilder('r')
            ->select('r.libelle', 'r.id')
            ->leftJoin('r.membres', 'm')
            ->addSelect('m.dateDebut')
            ->andWhere('m.user = :user')
            ->andWhere('m.communaute = :community')
            ->setParameter('user', $user)
            ->setParameter('community', $communaute)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
