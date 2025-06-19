<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;


use App\Entity\Turno;
use App\Entity\TurnoGeneral;
use App\Entity\Mesa;
use App\Entity\MomentoReserva;
use App\Entity\Reserva;
use App\Entity\Atencion;
use App\Entity\Pedido;
use App\Entity\PedidoItem;
use App\Entity\Producto;


use App\Repository\ReservaRepository;
use App\Repository\TurnoRepository;
use App\Repository\ProductoRepository;


use Doctrine\ORM\EntityManagerInterface;
use App\Service\GestionSalaService;
use Symfony\Component\HttpFoundation\Request;


final class GestionSalaController extends AbstractController
{
    private EntityManagerInterface $em;
    private GestionSalaService $gestionSalaService;

    public function __construct(EntityManagerInterface $em, GestionSalaService $gestionSalaService)
    {
        $this->em = $em;
        $this->gestionSalaService = $gestionSalaService;
    }


    #[Route('/Gestion/Sala', name: 'gestion_sala_menu')]
    public function menuGestionSala(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        $fechaHoy = new \DateTime(); // Fecha actual
        return $this->render('gestion_sala/menu.html.twig', [
            'fechaHoy' => $fechaHoy->format('Y-m-d')
        ]);
    }



    #[Route('/gestion/sala/estado', name: 'gestion_sala_estado')]
    public function index(Request $request, TurnoRepository $turnoRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        // Fecha de inicio: primer día del mes actual
        $hoy = new \DateTime('first day of this month');

        // Fecha fin: último día del tercer mes (mes actual + 2)
        $fin = (clone $hoy)->modify('+1 month')->modify('last day of this month');

        // Repositorio de Turnos (usamos $this->em en vez de $em)
        $repoTurno = $this->em->getRepository(Turno::class);

        // Obtenemos todos los turnos desde $hoy hasta $fin
        $turnos = $repoTurno->createQueryBuilder('t')
            ->where('t.fecha BETWEEN :inicio AND :fin')
            ->setParameter('inicio', $hoy->format('Y-m-d'))
            ->setParameter('fin', $fin->format('Y-m-d'))
            ->orderBy('t.fecha', 'ASC')
            ->getQuery()
            ->getResult();

        // Organizamos los turnos por fecha
        $turnosPorFecha = [];
        foreach ($turnos as $turno) {
            $fechaStr = $turno->getFecha()->format('Y-m-d');
            $turnosPorFecha[$fechaStr][] = $turno;
        }

        // Construimos el calendario
        $calendarios = [];
        $periodoInicio = clone $hoy;

        for ($i = 0; $i < 2; $i++) {
            $primerDiaMes = (clone $periodoInicio)->modify('first day of this month');
            $ultimoDiaMes = (clone $primerDiaMes)->modify('last day of this month');
            $semanas = [];

            $diaActual = clone $primerDiaMes;
            $primerDiaSemana = (int) $primerDiaMes->format('N');
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

            $calendarios[] = [
                'anyoMes' => $primerDiaMes->format('Y-m'),
                'nombreMes' => $primerDiaMes->format('F Y'),
                'semanas' => $semanas,
            ];

            $periodoInicio->modify('+1 month');
        }

        return $this->render('gestion_sala/index.html.twig', [
            'calendarios' => $calendarios,
        ]);
    }


    #[Route('/Gestion/Seleccionar-Turno', name: 'gestion_mostrar_turnos')]
    public function gestionMostrarDia(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

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

        return $this->render('gestion_sala/ver_dia.html.twig', [
            'fecha' => $fecha,
            'turnos' => $turnos,
        ]);
    }



    #[Route('/sala/disponibilidad/{id}', name: 'sala_disponibilidad')]
    public function verDisponibilidad(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        $turno = $this->em->getRepository(Turno::class)->find($id);
        if (!$turno) {
            throw $this->createNotFoundException('Turno no encontrado');
        }

        $momentos = $this->gestionSalaService->getMomentosReservaPorTurno($turno);

        $horaActual = new \DateTime();

        return $this->render('gestion_sala/disponibilidad.html.twig', [
            'turno' => $turno,
            'momentos' => $momentos,
            'horaActual' => $horaActual,
        ]);
    }



    #[Route('/reserva/nueva/{id}', name: 'reserva_opciones')]
    public function seleccionarTipoReserva(int $id, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        $momento = $em->getRepository(MomentoReserva::class)->find($id);

        if (!$momento || $momento->getReserva()) {
            return $this->redirectToRoute('sala_disponibilidad', ['id' => $momento?->getTurno()->getId()]);
        }

        return $this->render('gestion_sala/reserva_opciones.html.twig', [
            'momento' => $momento
        ]);
    }


    #[Route('/reserva/asignar/{id}', name: 'reserva_asignar')]
    public function asignarMesa(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        $momento = $em->getRepository(MomentoReserva::class)->find($id);

        if (!$momento || $momento->getReserva()) {
            return $this->redirectToRoute('sala_disponibilidad', ['id' => $momento?->getTurno()->getId()]);
        }

        if ($request->isMethod('POST')) {
            $nombre = $request->request->get('nombre');

            $reserva = new Reserva();
            $reserva->setNombreCliente($nombre ?: 'Cliente Local');
            $reserva->setNumeroComensales(0); // Número de comensales por defecto
            $reserva->setFechaHora(new \DateTime($momento->getFecha()->format('Y-m-d') . ' ' . $momento->getHoraInicio()->format('H:i:s')));
            $reserva->setMesa($momento->getMesa());
            $reserva->setMomentoReserva($momento);
            $reserva->setTelefono('999999999'); // Teléfono por defecto
            $reserva->setEmail('fakeEmail@gmail.com'); // Email por defecto

            $em->persist($reserva);
            $em->flush();

            return $this->redirectToRoute('sala_disponibilidad', ['id' => $momento->getTurno()->getId()]);
        }

        return $this->render('gestion_sala/asignar_mesa.html.twig', [
            'momento' => $momento
        ]);
    }

    #[Route('/reserva/crear/{id}', name: 'reserva_crear_formulario')]
    public function crearFormulario(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        $momento = $em->getRepository(MomentoReserva::class)->find($id);

        if (!$momento || $momento->getReserva()) {
            return $this->redirectToRoute('sala_disponibilidad', ['id' => $momento?->getTurno()->getId()]);
        }

        if ($request->isMethod('POST')) {
            $reserva = new Reserva();
            $reserva->setNombreCliente($request->request->get('nombre'));
            $reserva->setTelefono($request->request->get('telefono'));
            $reserva->setEmail($request->request->get('email'));
            $reserva->setNumeroComensales($request->request->get('comensales'));
            $reserva->setFechaHora(new \DateTime($momento->getFecha()->format('Y-m-d') . ' ' . $momento->getHoraInicio()->format('H:i:s')));
            $reserva->setMesa($momento->getMesa());
            $reserva->setMomentoReserva($momento);

            $em->persist($reserva);
            $em->flush();

            return $this->redirectToRoute('sala_disponibilidad', ['id' => $momento->getTurno()->getId()]);
        }

        return $this->render('gestion_sala/crear_reserva.html.twig', [
            'momento' => $momento
        ]);
    }



    
    #[Route('/Gestion/Reserva/Eliminar/{id}', name: 'gestion_reserva_eliminar')]
    public function eliminarReserva(Reserva $reserva, EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        // Confirmación por método POST
        if ($request->isMethod('POST')) {
            // Desvincula del MomentoReserva si existe
            $momento = $reserva->getMomentoReserva();
            if ($momento) {
                $momento->setReserva(null);
            }

            $em->remove($reserva);
            $em->flush();

            return $this->redirectToRoute('gestion_sala_estado'); 
        }

        return $this->render('gestion_sala/confirmar_eliminacion.html.twig', [
            'reserva' => $reserva,
        ]);
    }



    #[Route('/Gestion/Reservas', name: 'gestion_reservas_listado')]
    public function listarReservasFuturas(ReservaRepository $reservaRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        $hoy = new \DateTime('today');

        // Obtenemos las reservas futuras ordenadas por fecha y que su atención no haya finalizado.
        $reservas = $reservaRepository->createQueryBuilder('r')     
            ->leftJoin('r.atencion', 'a')
            ->where('r.fechaHora >= :hoy')
            ->andWhere('a.id IS NULL OR a.fin IS NULL')     //En teoría funciona, está comprobado a medias, pero debería de funcionar
            ->setParameter('hoy', $hoy)
            ->orderBy('r.fechaHora', 'ASC')
            ->getQuery()
            ->getResult();

        // Agrupar por fecha (formateada como Y-m-d)
        $reservasAgrupadas = [];
        foreach ($reservas as $reserva) {
            $fecha = $reserva->getFechaHora()->format('Y-m-d');
            $reservasAgrupadas[$fecha][] = $reserva;
        }

        return $this->render('gestion_sala/listado_reservas.html.twig', [
            'reservasPorDia' => $reservasAgrupadas,
        ]);
    }


    #[Route('/Gestion/Reserva/Detalle/{id}', name: 'gestion_reserva_detalle')]
    public function detalleReserva(Reserva $reserva, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');
        $urlAnterior = $request->headers->get('referer');

        return $this->render('gestion_sala/detalle_reserva.html.twig', [
            'reserva' => $reserva,
            'volverA' => $urlAnterior,
        ]);
    }





    
    #[Route('/reservas/hoy', name: 'reservas_hoy')]
    public function reservasHoy(ReservaRepository $reservaRepository): Response
    {

        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        $hoy = new \DateTimeImmutable();

        $reservas = $reservaRepository->findReservasDelDiaAgrupadasPorMesa($hoy);

        // Agrupar por identificador de mesa
        $agrupadasPorMesa = [];
        foreach ($reservas as $reserva) {
            $mesa = $reserva->getMesa();
            $identificador = $mesa ? $mesa->getIdentificador() : 'Sin mesa asignada';

            $agrupadasPorMesa[$identificador][] = $reserva;
        }

        ksort($agrupadasPorMesa); // orden alfabético por identificador

        return $this->render('gestion_sala/lista_hoy.html.twig', [
            'agrupadasPorMesa' => $agrupadasPorMesa,
        ]);
    }





    #[Route('/atencion/crear/{id}', name: 'crear_atencion', methods: ['POST'])]
    public function crearAtencion(Reserva $reserva, EntityManagerInterface $em): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        // Evita crear atención si ya existe en esta reserva
        if ($reserva->getAtencion() !== null) {
            return $this->redirectToRoute('reservas_hoy');
        }

        $mesa = $reserva->getMesa();

        // Buscar atención activa sin cerrar para esa mesa
        $qb = $em->createQueryBuilder();
        $atencionActiva = $qb->select('a')
            ->from(\App\Entity\Atencion::class, 'a')
            ->join('a.reserva', 'r')
            ->where('r.mesa = :mesa')
            ->andWhere('a.fin IS NULL')
            ->setParameter('mesa', $mesa)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($atencionActiva !== null) {
            $fechaInicio = $atencionActiva->getInicio()->format('Y-m-d');
            $hoy = (new \DateTimeImmutable())->format('Y-m-d');

            if ($fechaInicio < $hoy) {
                // Cerrar automáticamente la atención anterior
                $atencionActiva->setFin(new \DateTimeImmutable());
                $em->persist($atencionActiva);
                $em->flush();
            } else {
                // Si la atención activa es de hoy, no permitir otra
                return $this->redirectToRoute('reservas_hoy');
            }
        }

        // Crear atención nueva
        $pedido = new Pedido();
        $atencion = new Atencion();
        $atencion->setPedido($pedido);
        $atencion->setInicio(new \DateTimeImmutable());

        $reserva->setAtencion($atencion);

        $em->persist($pedido);
        $em->persist($atencion);
        $em->persist($reserva);
        $em->flush();

        return $this->redirectToRoute('reservas_hoy');
    }




    #[Route('/atencion/finalizar/{id}', name: 'finalizar_atencion', methods: ['POST'])]
    public function finalizarAtencion(Atencion $atencion, EntityManagerInterface $em): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        if ($atencion->getFin() !== null) {
            return $this->redirectToRoute('reservas_hoy');
        }

        $fin = new \DateTimeImmutable();
        $atencion->setFin($fin);

        $finConLimpieza = $fin->modify('+5 minutes');

        // Actualizar hora de fin del momentoReserva
        $reserva = $atencion->getReserva();
        if ($reserva && $reserva->getMomentoReserva()) 
        {
            $reserva->getMomentoReserva()->setHoraFin($finConLimpieza);
        }

        $em->flush();

        return $this->redirectToRoute('reservas_hoy');
    }


    #[Route('/atencion/gestionar/{id}', name: 'gestion_atencion', methods: ['GET', 'POST'])]
    public function gestionarAtencion(Request $request, Atencion $atencion, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        $pedido = $atencion->getPedido();

        if ($request->isMethod('POST')) {
            $productoId = $request->request->get('producto_id');
            $cantidad = (int) $request->request->get('cantidad', 1);

            if ($productoId && $cantidad > 0) {
                $producto = $em->getRepository(Producto::class)->find($productoId);

                if ($producto) {
                    $item = new PedidoItem();
                    $item->setPedido($pedido);
                    $item->setCantidad($cantidad);
                    $item->setNombreProducto($producto->getNombre());
                    $item->setPrecioUnitario($producto->getPrecio());
                    $em->persist($item);
                    $em->flush();

                    return $this->redirectToRoute('gestion_atencion', ['id' => $atencion->getId()]);
                }
            }
        }

        // Obtener productos agrupados por categoría
        $productos = $em->getRepository(Producto::class)->findAll();
        $productosPorCategoria = [];

        foreach ($productos as $producto) {
            $categoria = $producto->getCategoria()?->getNombre() ?? 'Sin categoría';
            $productosPorCategoria[$categoria][] = $producto;
        }

        return $this->render('gestion_sala/gestionar.html.twig', [
            'atencion' => $atencion,
            'pedido' => $pedido,
            'productosPorCategoria' => $productosPorCategoria,
        ]);
    }




    #[Route('/pedido/item/eliminar/{id}', name: 'eliminar_pedido_item', methods: ['POST'])]
    public function eliminarPedidoItem(PedidoItem $item, EntityManagerInterface $em): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        $atencionId = $item->getPedido()->getAtencion()->getId();

        $em->remove($item);
        $em->flush();

        return $this->redirectToRoute('gestion_atencion', ['id' => $atencionId]);
    }


    #[Route('/pedido-item/editar/{id}', name: 'editar_pedido_item', methods: ['GET', 'POST'])]
    public function editarPedidoItem(Request $request, PedidoItem $item, EntityManagerInterface $em): Response
    {

        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        if ($request->isMethod('POST')) {
            $cantidad = (int) $request->request->get('cantidad', $item->getCantidad());
            $precioUnitario = floatval(str_replace(',', '.', $request->request->get('precioUnitario', $item->getPrecioUnitario())));

            if ($cantidad > 0 && $precioUnitario >= 0) {
                $item->setCantidad($cantidad);
                $item->setPrecioUnitario($precioUnitario);
                $em->flush();


                // Redirigimos a la gestión de la atención para la que pertenece el pedido
                return $this->redirectToRoute('gestion_atencion', ['id' => $item->getPedido()->getAtencion()->getId()]);
            } 
        }

        return $this->render('gestion_sala/editar_pedido_item.html.twig', [
            'item' => $item,
        ]);
    }


}
