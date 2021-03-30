<?php

namespace App\Repository;

use App\Entity\Communaute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Communaute|null find($id, $lockMode = null, $lockVersion = null)
 * @method Communaute|null findOneBy(array $criteria, array $orderBy = null)
 * @method Communaute[]    findAll()
 * @method Communaute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommunauteRepository extends ServiceEntityRepository
{
    public function findall2($motCle){
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT c
            FROM App\Entity\Communaute c ORDER BY c.'
        )->setParameter('motcle', '%'.$motCle.'%');

        // returns an array of Product objects
        return $query->getResult();
    }
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Communaute::class);
    }
    public function findByUser($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user != :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
            ;
    }
    public function recherche($motCle){
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT c
            FROM App\Entity\Communaute c
            WHERE c.titre like :motcle or c.sousTitre like :motcle or c.descCourt like :motcle'
        )->setParameter('motcle', '%'.$motCle.'%');

        // returns an array of Product objects
        return $query->getResult();
    }
    public function autreResultat($motCle){
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT c
            FROM App\Entity\Communaute c
            WHERE c.descLong like :motcle or c.descCourt like :motcle'
        )->setParameter('motcle', '%'.$motCle.'%');

        // returns an array of Product objects
        return $query->getResult();
    }
    public function findByUrlPublic($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.urlPublic = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
            ;
    }
    // /**
    //  * @return Communaute[] Returns an array of Communaute objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Communaute
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
