<?php

namespace App\Repository;

use App\Entity\DroitsUtilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DroitsUtilisateur|null find($id, $lockMode = null, $lockVersion = null)
 * @method DroitsUtilisateur|null findOneBy(array $criteria, array $orderBy = null)
 * @method DroitsUtilisateur[]    findAll()
 * @method DroitsUtilisateur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DroitsUtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DroitsUtilisateur::class);
    }

    // /**
    //  * @return DroitsUtilisateur[] Returns an array of DroitsUtilisateur objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DroitsUtilisateur
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
