<?php

namespace App\Repository;

use App\Entity\MomentoReserva;
use App\Entity\Mesa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MomentoReserva>
 */
class MomentoReservaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MomentoReserva::class);
    }

    
    public function findFuturosByMesa(Mesa $mesa): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.mesa = :mesa')
            ->andWhere('m.fecha > :ahora')
            ->setParameter('mesa', $mesa)
            ->setParameter('ahora', new \DateTime())
            ->getQuery()
            ->getResult();
    }


//    /**
//     * @return MomentoReserva[] Returns an array of MomentoReserva objects
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

//    public function findOneBySomeField($value): ?MomentoReserva
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
