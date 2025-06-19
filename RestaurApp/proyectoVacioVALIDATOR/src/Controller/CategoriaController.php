<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Categoria;
use App\Repository\CategoriaRepository;
use App\Repository\ProductoRepository;
use Doctrine\ORM\EntityManagerInterface;



final class CategoriaController extends AbstractController{
    #[Route('/carta', name: 'listar_categorias')]
    public function listarCategorias(CategoriaRepository $categoriaRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');


        $categorias = $categoriaRepository->findAll();

        return $this->render('categoria/mostrar_categorias.html.twig', [
            'categorias' => $categorias,        
        ]);
    }

    
    #[Route('/carta/productos/{id}', name: 'productos_por_categoria')]
    public function productosPorCategoria(Categoria $categoria, ProductoRepository $productoRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLEADO');

        $productos = $productoRepository->findBy(['categoria' => $categoria]);

        return $this->render('carta/productos_categoria.html.twig', [
            'categoria' => $categoria,
            'productos' => $productos
        ]);
    }









}
