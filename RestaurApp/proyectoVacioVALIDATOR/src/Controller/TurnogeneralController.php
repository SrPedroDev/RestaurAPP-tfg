<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\TurnoType; // Formulario simple para Turno (horaInicio, horaFin)
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DiaSemanaRepository;
use App\Repository\TurnoGeneralRepository;
use App\Entity\TurnoGeneral;
use App\Entity\Turno;
use App\Entity\DiaSemana;
use App\Repository\MesaRepository;
use App\Repository\ReservaRepository;
use App\Entity\Mesa;
use App\Entity\Reserva;
use App\Repository\MomentoReservaRepository;
use App\Entity\MomentoReserva;
use App\Service\TurnoGeneratorService;


final class TurnogeneralController extends AbstractController{
#[Route('/turno/general', name: 'config_turno_general')]
public function configTurnoGeneral(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // 1. Obtener los 7 días
        $dias = $em->getRepository(DiaSemana::class)->findAll();

        // 2. Obtener todos los turnos generales existentes
        $turnos = $em->getRepository(TurnoGeneral::class)->findAll();

        // 3. Organizar turnos por día y tipo para facilitar la vista
        $turnosPorDiaYTipo = [];
        foreach ($turnos as $turno) {
            $diaId = $turno->getDiaSemana()->getId();
            $tipo = $turno->getTipo(); // "COMIDAS" o "CENAS"
            $turnosPorDiaYTipo[$diaId][$tipo] = $turno;
        }

        return $this->render('/turno/turnogeneral/turnogeneral.html.twig', [
            'dias' => $dias,
            'turnosPorDiaYTipo' => $turnosPorDiaYTipo,
        ]);
    }




#[Route('/turno/general/guardar', name: 'guardar_turno_general', methods: ['POST'])]
public function guardarTurnoGeneral(Request $request, EntityManagerInterface $em): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    $datos = $request->request->all('turnos');

    $repoDiaSemana = $em->getRepository(DiaSemana::class);
    $repoTurnoGeneral = $em->getRepository(TurnoGeneral::class);

    foreach ($datos as $diaId => $tipos) {
        $dia = $repoDiaSemana->find($diaId);
        if (!$dia) continue;

        foreach (['COMIDAS', 'CENAS'] as $tipo) {
            $inicio = $tipos[$tipo]['inicio'] ?? null;
            $fin = $tipos[$tipo]['fin'] ?? null;
            $reservasPorMesa = $tipos[$tipo]['reservas'] ?? null;

            $turno = $repoTurnoGeneral->findOneBy([
                'diaSemana' => $dia,
                'tipo' => $tipo,
            ]);

            if ($inicio && $fin) {
                $horaInicio = \DateTime::createFromFormat('H:i', $inicio);
                $horaFin = \DateTime::createFromFormat('H:i', $fin);

                if ($horaInicio && $horaFin && $horaInicio < $horaFin) {
                    if ($turno) {
                        $turno->setHoraInicio($horaInicio);
                        $turno->setHoraFin($horaFin);
                    } else {
                        $turno = new TurnoGeneral();
                        $turno->setDiaSemana($dia);
                        $turno->setTipo($tipo);
                        $turno->setHoraInicio($horaInicio);
                        $turno->setHoraFin($horaFin);
                        $em->persist($turno);
                    }

                    if ($reservasPorMesa !== null) {
                        $turno->setreservasPorMesa((int)$reservasPorMesa);
                    }
                } else {
                    return $this->redirectToRoute('config_turno_general');
                }
            } elseif ($turno) {
                $em->remove($turno);
            }
        }
    }

    $em->flush();

    return $this->redirectToRoute('config_turno_general');
}









}
