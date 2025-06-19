<?php

namespace App\Service;

use App\Entity\Mesa;
use App\Entity\MomentoReserva;
use App\Entity\Turno;
use Doctrine\ORM\EntityManagerInterface;

class GestionSalaService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Obtiene el estado de disponibilidad de las mesas para un turno.
     */
public function getMomentosReservaPorTurno(Turno $turno): array
{
    $momentos = $this->em->getRepository(MomentoReserva::class)
        ->createQueryBuilder('m')
        ->where('m.turno = :turno')
        ->setParameter('turno', $turno)
        ->orderBy('m.horaInicio', 'ASC')
        ->getQuery()
        ->getResult();

    // Agrupamos por mesa
    $resultado = [];

    foreach ($momentos as $momento) {
        $mesaId = $momento->getMesa()->getId();

        if (!isset($resultado[$mesaId])) {
            $resultado[$mesaId] = [
                'mesa' => $momento->getMesa(),
                'momentos' => [],
            ];
        }

        $resultado[$mesaId]['momentos'][] = $momento;
    }

    return $resultado;
}

   
}
