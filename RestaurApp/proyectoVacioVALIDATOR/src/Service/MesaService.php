<?php
namespace App\Service;

use App\Entity\Mesa;
use App\Repository\ReservaRepository;
use App\Repository\MomentoReservaRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Turno;
use App\Entity\MomentoReserva;


class MesaService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReservaRepository $reservaRepo,
        private MomentoReservaRepository $momentoRepo,
    ) {}



    public function hayReservasFuturas(Mesa $mesa, \DateTimeImmutable $now = null): array
    {
        if ($now === null) {
            $now = new \DateTimeImmutable();
        }
        return $this->reservaRepo->findFuturasByMesa($mesa, $now);
    }

    
    public function eliminarMesa(Mesa $mesa): void
    {
        // Eliminar todas las momentos reserva (futuros y pasados)
        $momentos = $this->momentoRepo->findBy(['mesa' => $mesa]);

        foreach ($momentos as $momento) {
            $this->em->remove($momento);
        }

        // Eliminar reservas futuras
        $now = new \DateTimeImmutable();
        $reservasFuturas = $this->reservaRepo->findFuturasByMesa($mesa, $now);
        foreach ($reservasFuturas as $reserva) {
            $this->em->remove($reserva);
        }

        // Finalmente eliminar mesa
        $this->em->remove($mesa);
        $this->em->flush();
    }




    public function editarMesa(Mesa $mesa, array $cambios, bool $originalOperativa): void
    {
        $now = new \DateTimeImmutable();
        $nuevaOperativa = $cambios['operativa'] ?? $mesa->isOperativa();

        // Asignar cambios
        if (isset($cambios['capacidad'])) {
            $mesa->setCapacidad($cambios['capacidad']);
        }
        if (isset($cambios['identificador'])) {
            $mesa->setIdentificador($cambios['identificador']);
        }
        if (isset($cambios['operativa'])) {
            $mesa->setOperativa($cambios['operativa']);
        }

        $reservasFuturas = $this->reservaRepo->findFuturasByMesa($mesa, $now);
        foreach ($reservasFuturas as $reserva) {
            $this->em->remove($reserva);
        }

        // Si la mesa se volvió no operativa → eliminar momentos futuros
        if ($originalOperativa && !$nuevaOperativa) {
            $momentosEliminar = $this->momentoRepo->findBy(['mesa' => $mesa]);
            foreach ($momentosEliminar as $momento) {
                $this->em->remove($momento);
            }
        }

        $this->em->flush();

        // Si la mesa se volvió operativa → regenerar momentos si no existen
        if (!$originalOperativa && $nuevaOperativa) {
            $this->regenerarMomentosParaMesa($mesa);
        }
    }




    
    public function regenerarMomentosParaMesa(Mesa $mesa): void
    {
        if (!$mesa->isOperativa()) {
            return; // no generamos para mesas no operativas
        }

        $now = new \DateTimeImmutable();
        $turnos = $this->em->getRepository(Turno::class)->findTurnosFuturos($now);

        foreach ($turnos as $turno) {
            // Solo generar si no existen ya momentos para esta mesa y turno
            $existen = $this->em->getRepository(MomentoReserva::class)
                ->findOneBy(['mesa' => $mesa, 'turno' => $turno]);

            if ($existen) {
                continue;
            }

            $horaInicio = clone $turno->getHoraInicio();
            $horaFin = clone $turno->getHoraFin();
            $reservasPorMesa = $turno->getreservasPorMesa();

            if ($reservasPorMesa <= 0) {
                continue;
            }

            $pausa = 5 * 60; // segundos
            $intervalo = $horaFin->getTimestamp() - $horaInicio->getTimestamp();
            $bloque = (int) floor($intervalo / $reservasPorMesa);
            $inicio = clone $horaInicio;

            for ($i = 0; $i < $reservasPorMesa; $i++) {
                $fin = (clone $inicio)->modify("+$bloque seconds");

                $momento = new MomentoReserva();
                $momento->setMesa($mesa);
                $momento->setTurno($turno);
                $momento->setFecha(clone $turno->getFecha());
                $momento->setHoraInicio(\DateTime::createFromFormat('H:i:s', $inicio->format('H:i:s')));
                $momento->setHoraFin(\DateTime::createFromFormat('H:i:s', $fin->format('H:i:s')));

                $this->em->persist($momento);

                $inicio = (clone $fin)->modify("+$pausa seconds");
            }
        }

        $this->em->flush();
    }









    public function regenerarMomentosFaltantesParaMesasOperativas(): void
    {
        $now = new \DateTimeImmutable();
        $turnos = $this->em->getRepository(Turno::class)->findTurnosFuturos($now);
        
        $mesas = $this->em->getRepository(Mesa::class)->findBy(['operativa' => true]);

        foreach ($turnos as $turno) {
            foreach ($mesas as $mesa) {
                $this->generarMomentosFaltantesParaMesaYTurno($mesa, $turno);
            }
        }

        $this->em->flush();
    }


    private function generarMomentosFaltantesParaMesaYTurno(Mesa $mesa, Turno $turno): void
    {
        $repo = $this->em->getRepository(MomentoReserva::class);
        $horaInicio = clone $turno->getHoraInicio();
        $horaFin = clone $turno->getHoraFin();
        $reservas = $turno->getReservasPorMesa();

        if ($reservas <= 0) return;

        $pausa = 5 * 60;
        $intervalo = $horaFin->getTimestamp() - $horaInicio->getTimestamp();
        $bloque = (int) floor($intervalo / $reservas);

        $inicio = clone $horaInicio;

        for ($i = 0; $i < $reservas; $i++) {
            $hora = \DateTime::createFromFormat('H:i:s', $inicio->format('H:i:s'));

            $existe = $repo->findOneBy([
                'mesa' => $mesa,
                'turno' => $turno,
                'horaInicio' => $hora
            ]);

            if (!$existe) {
                $momento = new MomentoReserva();
                $momento->setMesa($mesa);
                $momento->setTurno($turno);
                $momento->setFecha(clone $turno->getFecha());
                $momento->setHoraInicio($hora);
                $momento->setHoraFin(\DateTime::createFromFormat('H:i:s', $inicio->modify("+$bloque seconds")->format('H:i:s')));
                $this->em->persist($momento);
            }

            $inicio->modify("+$pausa seconds");
        }
    }

}
