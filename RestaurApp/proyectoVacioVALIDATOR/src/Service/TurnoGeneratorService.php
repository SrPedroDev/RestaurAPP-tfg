<?php

namespace App\Service;

use App\Entity\Turno;
use App\Entity\TurnoGeneral;
use App\Entity\Mesa;
use App\Entity\MomentoReserva;
use App\Repository\TurnoRepository;
use App\Repository\TurnoGeneralRepository;
use Doctrine\ORM\EntityManagerInterface;

class TurnoGeneratorService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Genera turnos desde hoy hasta 30 días después.
     * Si ya existe un turno para una fecha y tipo, no se genera.
     */
    public function generarTurnos(): void
    {
        $hoy = new \DateTime('today');
        $fin = (clone $hoy)->modify('+30 days');

        $repoTurno = $this->em->getRepository(Turno::class);
        $repoTurnoGeneral = $this->em->getRepository(TurnoGeneral::class);

        // Cargamos todos los turnos generales una vez
        $turnosGenerales = $repoTurnoGeneral->findAll();

        // Indexamos por número de día y tipo
        $generalesPorDiaYTipo = [];
        foreach ($turnosGenerales as $tg) {
            $numeroDia = $tg->getDiaSemana()->getNumero(); // 1=Lunes ... 7=Domingo
            $tipo = strtoupper($tg->getTipo());
            $generalesPorDiaYTipo[$numeroDia][$tipo] = $tg;
        }

        $fecha = clone $hoy;

        while ($fecha <= $fin) {
            $numeroDia = (int) $fecha->format('N'); // 1=Lunes ... 7=Domingo

            foreach (['COMIDAS', 'CENAS'] as $tipo) {
                // Si ya existe un turno para esta fecha y tipo, lo ignoramos
                $existe = $repoTurno->findOneBy([
                    'fecha' => $fecha,
                    'tipo' => $tipo,
                ]);

                if ($existe) {
                    continue;
                }

                // Obtenemos el turno general de este día y tipo
                $tg = $generalesPorDiaYTipo[$numeroDia][$tipo] ?? null;
                if (!$tg) {
                    continue;
                }

                // Creamos y persistimos un nuevo turno
                $nuevoTurno = new Turno();
                $nuevoTurno->setFecha(clone $fecha);
                $nuevoTurno->setTipo($tipo);
                $nuevoTurno->setHoraInicio($tg->getHoraInicio());
                $nuevoTurno->setHoraFin($tg->getHoraFin());
                $nuevoTurno->setDiaSemana($tg->getDiaSemana());
                $nuevoTurno->setreservasPorMesa($tg->getreservasPorMesa());

                $this->em->persist($nuevoTurno);
                $this->em->flush(); // Flush para que el turno tenga ID y se pueda relacionar

                // Generamos los momentos de reserva para este turno
                $this->generarMomentosReservaParaTurno($nuevoTurno);

                // Flush para guardar los momentos reservas
                $this->em->flush();
            }

            $fecha->modify('+1 day');
        }
    }


    public function generarMomentosReservaParaTurno(Turno $turno): void     // CAMBIAR ESTA PARTE?
    {
        // Eliminar momentos antiguos
        $momentosAntiguos = $this->em->getRepository(MomentoReserva::class)->findBy(['turno' => $turno]);
        foreach ($momentosAntiguos as $momento) {
            $this->em->remove($momento);
        }
        $this->em->flush();

        $mesas = $this->em->getRepository(Mesa::class)->findBy(['operativa' => true]);

        $horaInicio = clone $turno->getHoraInicio();
        $horaFin = clone $turno->getHoraFin();
        $reservasPorMesa = $turno->getreservasPorMesa();

        if ($reservasPorMesa <= 0) {
            return;
        }

        $pausaEntreReservas = 5 * 60; // segundos
        $intervaloTotal = $horaFin->getTimestamp() - $horaInicio->getTimestamp();
        $duracionBloque = (int) floor($intervaloTotal / $reservasPorMesa);

        foreach ($mesas as $mesa) {
            $inicioBloque = clone $horaInicio;

            for ($i = 0; $i < $reservasPorMesa; $i++) {
                $finBloque = (clone $inicioBloque)->modify("+$duracionBloque seconds");

                $momento = new MomentoReserva();
                $momento->setMesa($mesa);
                $momento->setTurno($turno);

                // Establecemos la fecha del turno
                $momento->setFecha(clone $turno->getFecha());

                // Guardamos solo la hora en TIME
                $momento->setHoraInicio(\DateTime::createFromFormat('H:i:s', $inicioBloque->format('H:i:s')));
                $momento->setHoraFin(\DateTime::createFromFormat('H:i:s', $finBloque->format('H:i:s')));

                $this->em->persist($momento);

                // Siguiente bloque
                $inicioBloque = (clone $finBloque)->modify("+$pausaEntreReservas seconds");
            }
        }
    }


    


}
