<?php

namespace App\Controller;

use App\Entity\Mesa;
use App\Form\MesaType;
use App\Repository\MesaRepository;
use App\Service\MesaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



#[Route('/Gestion/Mesas', name: 'gestion_mesas_')]
class MesaController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(MesaRepository $mesaRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $mesas = $mesaRepository->findAll();

        return $this->render('gestion_mesas/index.html.twig', [
            'mesas' => $mesas,
        ]);
    }


    #[Route('/nueva', name: 'nueva')]
    public function nueva(Request $request, EntityManagerInterface $em, MesaService $mesaService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $mesa = new Mesa();
        $form = $this->createForm(MesaType::class, $mesa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $em->persist($mesa);
            $em->flush();
           
            //Llamamos al servicio para generar momentos reserva
            $mesaService->regenerarMomentosParaMesa($mesa);

            return $this->redirectToRoute('gestion_mesas_index');
        }

        return $this->render('gestion_mesas/formulario.html.twig', [
            'form' => $form->createView(),
            'editar' => false,
        ]);
    }

    
    #[Route('/editar/{id}', name: 'editar')]
    public function editar(Mesa $mesa, Request $request, MesaService $mesaService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Guardamos el valor original ANTES de handleRequest
        $originalOperativa = $mesa->isOperativa();
        $originalCapacidad = $mesa->getCapacidad();
        $originalIdentificador = $mesa->getIdentificador();

        $form = $this->createForm(MesaType::class, $mesa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cambios = [];

            if ($originalOperativa !== $mesa->isOperativa()) {
                $cambios['operativa'] = $mesa->isOperativa();
            }
            if ($originalCapacidad !== $mesa->getCapacidad()) {
                $cambios['capacidad'] = $mesa->getCapacidad();
            }
            if ($originalIdentificador !== $mesa->getIdentificador()) {
                $cambios['identificador'] = $mesa->getIdentificador();
            }

            // PASAMOS el valor original para que el servicio compare correctamente
            $mesaService->editarMesa($mesa, $cambios, $originalOperativa);

            return $this->redirectToRoute('gestion_mesas_index');
        }

        return $this->render('gestion_mesas/formulario.html.twig', [
            'form' => $form->createView(),
            'editar' => true,
        ]);
    }


    #[Route('/confirmar-edicion/{id}', name: 'confirmar_edicion')]
    public function confirmarEdicion(Mesa $mesa, MesaService $mesaService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $hayReservas = $mesaService->hayReservasFuturas($mesa);

        if (!$hayReservas) 
        {
            // Si no hay reservas futuras, redirigimos directamente a la edición
            return $this->redirectToRoute('gestion_mesas_editar', ['id' => $mesa->getId()]);
        }

        return $this->render('gestion_mesas/confirmar_edicion.html.twig', [
            'mesa' => $mesa,
            'hayReservas' => $hayReservas,
        ]);
    }




    #[Route('/eliminar/{id}', name: 'eliminar')]
    public function eliminar(Mesa $mesa,  MesaService $mesaService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

       $mesaService->eliminarMesa($mesa);

        return $this->redirectToRoute('gestion_mesas_index');
    }
}
