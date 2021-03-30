<?php

namespace App\Repository;

use App\Entity\PointUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PointUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method PointUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method PointUser[]    findAll()
 * @method PointUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PointUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PointUser::class);
    }

    // /**
    //  * @return PointUser[] Returns an array of PointUser objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PointUser
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
