<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Turno;
use App\Entity\Reserva;
use App\Entity\MomentoReserva;
use App\Form\TurnoType;
use App\Entity\DiaSemana;
use App\Service\TurnoGeneratorService;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


final class TurnoController extends AbstractController{


 public function __construct(EntityManagerInterface $em, TurnoGeneratorService $turnoGeneratorService)
    {
        $this->em = $em;
        $this->turnoGeneratorService = $turnoGeneratorService;
    }

    
#[Route('/turnos/gestion', name: 'gestion_turnos')]
public function calendario3Meses(EntityManagerInterface $em): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    // Fecha de inicio: primer día del mes actual
    $hoy = new \DateTime('first day of this month');

    // Fecha fin: último día del tercer mes (mes actual + 2)
    $fin = (clone $hoy)->modify('+2 months')->modify('last day of this month');

    // Repositorio de Turnos
    $repoTurno = $em->getRepository(Turno::class);

    // Obtenemos todos los turnos desde $hoy hasta $fin
    $turnos = $repoTurno->createQueryBuilder('t')
        ->where('t.fecha BETWEEN :inicio AND :fin')
        ->setParameter('inicio', $hoy->format('Y-m-d'))
        ->setParameter('fin', $fin->format('Y-m-d'))
        ->orderBy('t.fecha', 'ASC')
        ->getQuery()
        ->getResult();

    // Vamos a organizar los turnos en un array por año-mes-dia
    $turnosPorFecha = [];
    foreach ($turnos as $turno) {
        $fechaStr = $turno->getFecha()->format('Y-m-d');
        if (!isset($turnosPorFecha[$fechaStr])) {
            $turnosPorFecha[$fechaStr] = [];
        }
        $turnosPorFecha[$fechaStr][] = $turno;
    }

    // Construir el calendario para los 3 meses
    $calendarios = [];

    $periodoInicio = clone $hoy;

    for ($i = 0; $i < 3; $i++) {
        $primerDiaMes = (clone $periodoInicio)->modify('first day of this month');
        $ultimoDiaMes = (clone $primerDiaMes)->modify('last day of this month');

        // Array de semanas. Cada semana es array de 7 días (pueden ser null si día fuera de mes)
        $semanas = [];

        $diaActual = (clone $primerDiaMes);

        // La semana comienza en lunes, PHP: format('N') 1=lunes ... 7=domingo
        $primerDiaSemana = (int) $primerDiaMes->format('N');

        // Primer semana - rellenamos con null hasta el primer día real
        $semana = array_fill(0, 7, null);
        for ($j = $primerDiaSemana - 1; $j < 7; $j++) {
            $fechaStr = $diaActual->format('Y-m-d');
            $semana[$j] = [
                'fecha' => clone $diaActual,
                'turnos' => $turnosPorFecha[$fechaStr] ?? [],
            ];
            $diaActual->modify('+1 day');
            if ($diaActual > $ultimoDiaMes) break;
        }
        $semanas[] = $semana;

        // Semanas siguientes hasta terminar el mes
        while ($diaActual <= $ultimoDiaMes) {
            $semana = [];
            for ($d = 0; $d < 7; $d++) {
                if ($diaActual <= $ultimoDiaMes) {
                    $fechaStr = $diaActual->format('Y-m-d');
                    $semana[] = [
                        'fecha' => clone $diaActual,
                        'turnos' => $turnosPorFecha[$fechaStr] ?? [],
                    ];
                    $diaActual->modify('+1 day');
                } else {
                    $semana[] = null;
                }
            }
            $semanas[] = $semana;
        }

        // Guardamos calendario del mes: año-mes y semanas
        $calendarios[] = [
            'anyoMes' => $primerDiaMes->format('Y-m'),
            'nombreMes' => $primerDiaMes->format('F Y'),
            'semanas' => $semanas,
        ];

        // Avanzamos al siguiente mes
        $periodoInicio->modify('+1 month');
    }

    return $this->render('turno/gestion.html.twig', [
        'calendarios' => $calendarios,
    ]);
}



#[Route('/turnos/fecha/mostrar', name: 'turnos_mostrar_dia')]
public function turnoMostrarDia(Request $request, EntityManagerInterface $em): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    $fechaString = $request->query->get('fecha');

    if (!$fechaString) {
        throw $this->createNotFoundException('No se ha proporcionado la fecha.');
    }

    $fecha = new \DateTime($fechaString);
    $repoTurno = $em->getRepository(Turno::class);

    $turnos = [
        'COMIDAS' => $repoTurno->findOneBy(['fecha' => $fecha, 'tipo' => 'COMIDAS']),
        'CENAS' => $repoTurno->findOneBy(['fecha' => $fecha, 'tipo' => 'CENAS']),
    ];

    return $this->render('turno/ver_dia.html.twig', [
        'fecha' => $fecha,
        'turnos' => $turnos,
    ]);
}


#[Route('/turnos/fecha/editar', name: 'turno_editar')]
public function turnoEditar(Request $request, EntityManagerInterface $em): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    $id = $request->query->get('id');
    $turno = $em->getRepository(Turno::class)->find($id);

    if (!$turno) {
        throw $this->createNotFoundException('Turno no encontrado.');
    }

    $momentoReservas = $em->getRepository(MomentoReserva::class)->findBy(['turno' => $turno]);

    $hayReservas = false;
    foreach ($momentoReservas as $momento) {
        if ($momento->getReserva()) {
            $hayReservas = true;
            break;
        }
    }

    if ($request->isMethod('POST')) {
        $nuevaHoraInicio = $request->request->get('hora_inicio');
        $nuevaHoraFin = $request->request->get('hora_fin');
        $nuevasreservasPorMesa = $request->request->get('reservas_por_turno');


        if ($hayReservas) {
            return $this->render('turno/confirmar_edicion.html.twig', [
                'turno' => $turno,
                'nuevaHoraInicio' => $nuevaHoraInicio,
                'nuevaHoraFin' => $nuevaHoraFin,
                'reservasPorMesa' => $nuevasreservasPorMesa,
            ]);
        }

        $turno->setHoraInicio(new \DateTime($nuevaHoraInicio));
        $turno->setHoraFin(new \DateTime($nuevaHoraFin));
        $turno->setreservasPorMesa((int)$nuevasreservasPorMesa);
        $em->flush();

        $this->turnoGeneratorService->generarMomentosReservaParaTurno($turno);
        $em->flush(); // Aseguramos que los momentos de reserva se guarden

        return $this->redirectToRoute('turnos_mostrar_dia', ['fecha' => $turno->getFecha()->format('Y-m-d')]);
    }

    return $this->render('turno/formulario_turno.html.twig', [
        'turno' => $turno,
        'modo' => 'editar',
        'hayReservas' => $hayReservas,
    ]);
}


#[Route('/turnos/fecha/editar/confirmar', name: 'turno_editar_confirmar')]
public function confirmarEdicion(Request $request, EntityManagerInterface $em): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    $id = $request->request->get('id');
    $horaInicio = $request->request->get('hora_inicio');
    $horaFin = $request->request->get('hora_fin');
    $reservasPorMesa = $request->request->get('reservas_por_turno');


    $turno = $em->getRepository(Turno::class)->find($id);
    if (!$turno) {
        throw $this->createNotFoundException('Turno no encontrado.');
    }

    $momentoReservas = $em->getRepository(MomentoReserva::class)->findBy(['turno' => $turno]);

    foreach ($momentoReservas as $momento) {
        if ($reserva = $momento->getReserva()) {
            $em->remove($reserva);
        }
        $em->remove($momento);
    }

    $turno->setHoraInicio(new \DateTime($horaInicio));
    $turno->setHoraFin(new \DateTime($horaFin));
    $turno->setreservasPorMesa((int)$reservasPorMesa);
    $em->flush();

    $this->turnoGeneratorService->generarMomentosReservaParaTurno($turno);
    $em->flush(); // Aseguramos que los momentos de reserva se guarden

    return $this->redirectToRoute('turnos_mostrar_dia', ['fecha' => $turno->getFecha()->format('Y-m-d')]);
}





#[Route('/turnos/fecha/eliminar', name: 'turno_eliminar')]
public function eliminarTurno(Request $request, EntityManagerInterface $em): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    $id = $request->query->get('id');
    $turno = $em->getRepository(Turno::class)->find($id);

    if (!$turno) {
        throw $this->createNotFoundException('Turno no encontrado.');
    }

    $momentoReservas = $em->getRepository(MomentoReserva::class)->findBy(['turno' => $turno]);

    $hayReservas = false;
    foreach ($momentoReservas as $momento) {
        if ($momento->getReserva()) {
            $hayReservas = true;
            break;
        }
    }

    // Si tiene reservas, mostramos pantalla de confirmación
    if ($hayReservas) {
        return $this->render('turno/confirmar_eliminacion.html.twig', [
            'turno' => $turno,
        ]);
    }

    // Si no tiene reservas, se elimina directamente
    foreach ($momentoReservas as $momento) {
        $em->remove($momento);
    }

    $fecha = $turno->getFecha()->format('Y-m-d');
    $em->remove($turno);
    $em->flush();

    return $this->redirectToRoute('turnos_mostrar_dia', ['fecha' => $fecha]);
}



#[Route('/turnos/fecha/eliminar/confirmar', name: 'turno_eliminar_confirmar', methods: ['POST'])]
public function confirmarEliminacion(Request $request, EntityManagerInterface $em): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    $id = $request->request->get('id');
    $turno = $em->getRepository(Turno::class)->find($id);

    if (!$turno) {
        throw $this->createNotFoundException('Turno no encontrado.');
    }

    $fecha = $turno->getFecha()->format('Y-m-d');

    $momentoReservas = $em->getRepository(MomentoReserva::class)->findBy(['turno' => $turno]);

    foreach ($momentoReservas as $momento) {
        if ($reserva = $momento->getReserva()) {
            $em->remove($reserva);
        }
        $em->remove($momento);
    }

    $em->remove($turno);
    $em->flush();

    return $this->redirectToRoute('turnos_mostrar_dia', ['fecha' => $fecha]);
}





#[Route('/turnos/fecha/crear', name: 'turno_crear')]
public function crearTurno(Request $request, EntityManagerInterface $em): Response
{

    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    $fechaParam = $request->query->get('fecha');
    $tipo = $request->query->get('tipo');

    $fecha = $fechaParam ? new \DateTime($fechaParam) : null;

    $numeroDiaSemana = (int) $fecha->format('N');
    $diaSemana = $em->getRepository(DiaSemana::class)->findOneBy(['numero' => $numeroDiaSemana]);

    if (!$diaSemana) {
        throw $this->createNotFoundException('No se encontró el día de la semana con número: ' . $numeroDiaSemana);
    }

    if (!$fecha || !$tipo) {
        throw $this->createNotFoundException('Fecha o tipo no proporcionado.');
    }

    $turno = new Turno();
    $turno->setFecha($fecha);
    $turno->setTipo($tipo); 

    if ($request->isMethod('POST')) {
        $horaInicio = new \DateTime($request->request->get('hora_inicio'));
        $horaFin = new \DateTime($request->request->get('hora_fin'));
        $reservasPorMesa = $request->request->get('reservas_por_turno');

        $turno->setHoraInicio($horaInicio);
        $turno->setHoraFin($horaFin);
        $turno->setTipo($tipo);
        $turno->setDiaSemana($diaSemana);
        $turno->setreservasPorMesa((int)$reservasPorMesa);

        $em->persist($turno);
        $em->flush();

        $this->turnoGeneratorService->generarMomentosReservaParaTurno($turno);
        $em->flush(); // Aseguramos que los momentos de reserva se guarden


        return $this->redirectToRoute('turnos_mostrar_dia', ['fecha' => $fecha->format('Y-m-d')]);
    }

    return $this->render('turno/formulario_turno.html.twig', [
        'turno' => $turno,
        'modo' => 'crear',
        'hayReservas' => false,
    ]);
}


}

