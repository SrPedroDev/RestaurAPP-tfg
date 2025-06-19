<?php

namespace App\Repository;

use App\Entity\Turno;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Turno>
 */
class TurnoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Turno::class);
    }

public function findTurnosFuturos(\DateTimeImmutable $ahora): array
{
    // Trae todos los turnos desde hoy
    $turnos = $this->createQueryBuilder('t')
        ->where('t.fecha >= :hoy')
        ->setParameter('hoy', $ahora->format('Y-m-d'))
        ->getQuery()
        ->getResult();

    // Filtra en PHP los turnos que realmente sean futuros
    return array_filter($turnos, function($turno) use ($ahora) 
    {
        $fechaHora = (clone $turno->getFecha())->setTime(
            $turno->getHoraInicio()->format('H'),
            $turno->getHoraInicio()->format('i'),
            $turno->getHoraInicio()->format('s')
        );
        return $fechaHora >= $ahora;
    });
}


//    /**
//     * @return Turno[] Returns an array of Turno objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Turno
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
