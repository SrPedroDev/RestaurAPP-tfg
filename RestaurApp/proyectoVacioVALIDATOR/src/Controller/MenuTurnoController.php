<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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



final class MenuTurnoController extends AbstractController{

#[Route('/turno/inicio', name: 'inicio_turno')]
public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('/turno/index.html.twig');
    }





}
