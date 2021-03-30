<?php

namespace App\Repository;

use App\Entity\DownloadSpace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DownloadSpace|null find($id, $lockMode = null, $lockVersion = null)
 * @method DownloadSpace|null findOneBy(array $criteria, array $orderBy = null)
 * @method DownloadSpace[]    findAll()
 * @method DownloadSpace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DownloadSpaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DownloadSpace::class);
    }

    // /**
    //  * @return DownloadSpace[] Returns an array of DownloadSpace objects
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
    public function findOneBySomeField($value): ?DownloadSpace
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findLikeFilename($file)
    {
        try {
            return $this->createQueryBuilder('d')
                ->select('d.id', 'd.file')
                ->where('d.file LIKE :file')
                ->setParameter('file', '%'.$file.'%')
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return $this->createQueryBuilder('d')
                ->select('d.file')
                ->where('d.file LIKE :file')
                ->setParameter('file', '%'.$file.'%')
                ->getQuery()
                ->setMaxResults(1)
                ->getResult();
        }
    }
}
