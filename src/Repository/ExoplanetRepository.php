<?php

namespace App\Repository;

use App\Entity\Exoplanet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Exoplanet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Exoplanet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Exoplanet[]    findAll()
 * @method Exoplanet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExoplanetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exoplanet::class);
    }

    // /**
    //  * @return Exoplanet[] Returns an array of Exoplanet objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Exoplanet
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
