<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

use App\Entity\Menu;
use App\Repository\MenuRepository;



 class MenuController extends AbstractController
{
    private $menuRepository;


    public function  __construct ( MenuRepository $menuRepository){
        $this->menuRepository = $menuRepository;
    }


    #[Route('/', name: 'inicio')]
    public function index(): Response
    {
        return $this->render('inicio.html.twig'); // Una vista que extienda de base y diga "Bienvenido"
    }

    #[Route('/menu-lateral', name: 'render_menuLateral')]
    public function menuLateral(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        $menus = $this->menuRepository->findMenu();

        return $this->render('menus/menu_lateral.html.twig', [
            'menus' => $menus
        ]);
    }

    #[Route('/menu-desplegable', name: 'render_menuDesplegable')]
    public function menuDesplegable(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        $menus = $this->menuRepository->findMenu();

        return $this->render('menus/menu_desplegable.html.twig', [
            'menus' => $menus
        ]);
    }


    #[Route('/carta', name: 'carta')]
    public function carta(): Response
    {
        return $this->redirectToRoute('listar_categorias');
    }

}
