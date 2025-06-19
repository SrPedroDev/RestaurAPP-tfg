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




final class HistoricoController extends AbstractController
{
    #[Route('/historico', name: 'historico_reservas_calendario')]
    public function calendarioHistoricoReservas(ReservaRepository $reservaRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        $inicio = (new \DateTime('first day of this month'))->modify('-2 months');
        $fin = (clone $inicio)->modify('+2 months')->modify('last day of this month');

        $reservas = $reservaRepo->createQueryBuilder('r')
            ->join('r.atencion', 'a')
            ->where('a.fin IS NOT NULL')
            ->andWhere('r.fechaHora BETWEEN :inicio AND :fin')
            ->setParameter('inicio', $inicio->format('Y-m-d 00:00:00'))
            ->setParameter('fin', $fin->format('Y-m-d 23:59:59'))
            ->getQuery()
            ->getResult();

        $reservasPorFecha = [];
        foreach ($reservas as $reserva) {
            $fechaStr = $reserva->getFechaHora()->format('Y-m-d');
            $reservasPorFecha[$fechaStr][] = $reserva;
        }

        $calendarios = [];
        $periodoInicio = clone $inicio;

        for ($i = 0; $i < 3; $i++) { // 3 meses: -2, -1, actual
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
                    'reservas' => $reservasPorFecha[$fechaStr] ?? [],
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
                            'reservas' => $reservasPorFecha[$fechaStr] ?? [],
                        ];
                        $diaActual->modify('+1 day');
                    } else {
                        $semana[] = null;
                    }
                }
                $semanas[] = $semana;
            }

            $esMesActual = $primerDiaMes->format('Y-m') === (new \DateTime())->format('Y-m');

            $calendarios[] = [
                'anyoMes' => $primerDiaMes->format('Y-m'),
                'nombreMes' => $primerDiaMes->format('F Y'),
                'semanas' => $semanas,
                'esMesActual' => $esMesActual,
            ];

            $periodoInicio->modify('+1 month');
        }

        return $this->render('historico/calendario.html.twig', [
            'calendarios' => $calendarios,
        ]);
    }



    #[Route('/historico/reservas/{fecha}', name: 'historico_reservas_dia')]
    public function reservasDelDia(string $fecha, ReservaRepository $reservaRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        $fechaInicio = new \DateTime($fecha . ' 00:00:00');
        $fechaFin = new \DateTime($fecha . ' 23:59:59');

        $reservas = $reservaRepo->createQueryBuilder('r')
            ->join('r.atencion', 'a')
            ->where('a.fin IS NOT NULL')
            ->andWhere('r.fechaHora BETWEEN :inicio AND :fin')
            ->setParameter('inicio', $fechaInicio)
            ->setParameter('fin', $fechaFin)
            ->orderBy('r.fechaHora', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('historico/reservas_dia.html.twig', [
            'fecha' => $fechaInicio,
            'reservas' => $reservas,
        ]);
    }


    #[Route('/historico/reserva/{id}', name: 'historico_reserva_detalle')]
    public function detalleReserva(int $id, ReservaRepository $reservaRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        $reserva = $reservaRepo->find($id);
        if (!$reserva) {
            throw $this->createNotFoundException('Reserva no encontrada');
        }

        $atencion = $reserva->getAtencion();
        $pedido = $atencion ? $atencion->getPedido() : null;

        return $this->render('historico/reserva_detalle.html.twig', [
            'reserva' => $reserva,
            'atencion' => $atencion,
            'pedido' => $pedido,
        ]);
    }




}
