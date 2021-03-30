<?php

namespace App\Repository;

use App\Entity\NewLetter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewLetter|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewLetter|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewLetter[]    findAll()
 * @method NewLetter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewLetterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewLetter::class);
    }

    // /**
    //  * @return NewLetter[] Returns an array of NewLetter objects
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
    public function findOneBySomeField($value): ?NewLetter
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
