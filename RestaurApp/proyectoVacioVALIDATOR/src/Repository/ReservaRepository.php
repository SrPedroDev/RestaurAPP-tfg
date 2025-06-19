<?php

namespace App\Repository;

use App\Entity\Reserva;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Mesa;

/**
 * @extends ServiceEntityRepository<Reserva>
 */
class ReservaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reserva::class);
    }


    public function findFuturasByMesa(Mesa $mesa): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.mesa = :mesa')
            ->andWhere('r.fechaHora > :ahora')
            ->setParameter('mesa', $mesa)
            ->setParameter('ahora', new \DateTime())
            ->getQuery()
            ->getResult();
    }


    public function findReservasDelDiaAgrupadasPorMesa(\DateTimeInterface $fecha): array
    {
        $inicio = (clone $fecha)->setTime(0, 0, 0);
        $fin = (clone $fecha)->setTime(23, 59, 59);

        return $this->createQueryBuilder('r')
            ->leftJoin('r.mesa', 'm')
            ->addSelect('m')
            ->where('r.fechaHora BETWEEN :inicio AND :fin')
            ->setParameter('inicio', $inicio)
            ->setParameter('fin', $fin)
            ->orderBy('m.identificador', 'ASC')  // Usamos el campo correcto
            ->addOrderBy('r.fechaHora', 'ASC')
            ->getQuery()
            ->getResult();
    }


//    /**
//     * @return Reserva[] Returns an array of Reserva objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Reserva
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


}
