<?php

namespace App\Repository;

use App\Entity\NewsLetterMembre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewsLetterMembre|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsLetterMembre|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsLetterMembre[]    findAll()
 * @method NewsLetterMembre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsLetterMembreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsLetterMembre::class);
    }

    // /**
    //  * @return NewsLetterMembre[] Returns an array of NewsLetterMembre objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NewsLetterMembre
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
