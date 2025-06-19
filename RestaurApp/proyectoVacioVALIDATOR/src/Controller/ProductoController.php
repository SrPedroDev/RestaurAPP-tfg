<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\String\Slugger\SluggerInterface;

use App\Entity\Producto;
use App\Entity\Categoria;
use App\Repository\ProductoRepository;
use Doctrine\ORM\EntityManagerInterface;





final class ProductoController extends AbstractController{


    #[Route('/producto/editar/{id}', name: 'producto_editar')]
    public function editarProducto(int $id, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $producto = $entityManager->getRepository(Producto::class)->find($id);

        if (!$producto) {
            throw $this->createNotFoundException('Producto no encontrado');
        }

        $form = $this->createFormBuilder($producto)
            ->add('nombre', TextType::class)
            ->add('precio', NumberType::class, ['scale' => 2])
            ->add('descripcion', TextareaType::class, ['required' => false])
            ->add('imagen', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Imagen del producto (jpeg, png, webp)',
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Por favor sube una imagen válida (jpeg, png o webp).',
                    ])
                ],
            ])
            ->add('guardar', SubmitType::class, ['label' => 'Guardar cambios'])
            ->add('eliminar_imagen', SubmitType::class, [
                'label' => 'Eliminar imagen',
                'attr' => ['formnovalidate' => 'formnovalidate', 'class' => 'btn btn-outline-danger mt-2']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('eliminar_imagen')->isClicked()) {
                if ($producto->getImagen()) {
                    $ruta = __DIR__ . '/../../public/uploads/productoImg/' . $producto->getImagen();
                    if (file_exists($ruta)) {
                        unlink($ruta);
                    }
                    $producto->setImagen(null);
                    $entityManager->flush();
                }
            }

            if ($form->isValid() && $form->get('guardar')->isClicked()) {
                $imagenFile = $form->get('imagen')->getData();

                if ($imagenFile) {
                    $originalFilename = pathinfo($imagenFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $nuevoNombre = $safeFilename . '-' . uniqid() . '.' . $imagenFile->guessExtension();

                    $directorioDestino = __DIR__ . '/../../public/uploads/productoImg';

                    try {
                        $imagenFile->move($directorioDestino, $nuevoNombre);
                    } catch (FileException $e) {
                    }

                    if ($producto->getImagen()) {
                        $anterior = $directorioDestino . '/' . $producto->getImagen();
                        if (file_exists($anterior)) {
                            unlink($anterior);
                        }
                    }

                    $producto->setImagen($nuevoNombre);
                }

                $entityManager->flush();

                return $this->redirectToRoute('productos_por_categoria', [
                    'id' => $producto->getCategoria()->getId()
                ]);
            }
        }

        return $this->render('producto/editar.html.twig', [
            'form' => $form->createView(),
            'producto' => $producto,
        ]);
    }






    #[Route('/producto/eliminar/{id}', name: 'producto_eliminar')]
    public function eliminarProducto(int $id, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $producto = $entityManager->getRepository(Producto::class)->find($id);

        if (!$producto) {
            throw $this->createNotFoundException('Producto no encontrado');
        }

        // Eliminar imagen si existe
        if ($producto->getImagen()) {
            $ruta = __DIR__ . '/../../public/uploads/productoImg/' . $producto->getImagen();
            if (file_exists($ruta)) {
                unlink($ruta);
            }
            }

        // Eliminar producto
        $entityManager->remove($producto);
        $entityManager->flush();


        return $this->redirectToRoute('productos_por_categoria', [
                'id' => $producto->getCategoria()->getId()
        ]);
    }

    #[Route('/producto/nuevo/{categoriaId}', name: 'producto_nuevo')]
    public function nuevoProducto(int $categoriaId, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categoria = $entityManager->getRepository(Categoria::class)->find($categoriaId);
        if (!$categoria) 
        {
        throw $this->createNotFoundException('Categoría no encontrada');
        }

        $producto = new Producto();
        $producto->setCategoria($categoria);


         $form = $this->createFormBuilder($producto)
            ->add('nombre', TextType::class)
            ->add('precio', NumberType::class, ['scale' => 2])
            ->add('descripcion', TextareaType::class,)
            ->add('imagen', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Imagen del producto (jpeg, png, webp)',
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Por favor sube una imagen válida (jpeg, png o webp).',
                    ])
                ],
            ])
            ->add('guardar', SubmitType::class, ['label' => 'Crear Producto'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imagenFile = $form->get('imagen')->getData();

            if ($imagenFile) {
                $originalFilename = pathinfo($imagenFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $nuevoNombre = $safeFilename.'-'.uniqid().'.'.$imagenFile->guessExtension();

                // Ruta fija absoluta en el servidor (ajusta la ruta a tu proyecto)
                $directorioDestino = __DIR__.'/../../public/uploads/productoImg';

                try {
                    $imagenFile->move(
                        $directorioDestino,
                        $nuevoNombre
                    );
                } catch (FileException $e) {
                }

                $producto->setImagen($nuevoNombre);
            }

            $entityManager->persist($producto);
            $entityManager->flush();


            // Ajusta la redirección a la ruta que necesites
            return $this->redirectToRoute('productos_por_categoria', ['id' => $producto->getCategoria()->getId()]);
        }

        return $this->render('producto/nuevo.html.twig', [
            'form' => $form->createView(),
        ]);

        }


    #[Route('/api/productos', name: 'api_productos', methods: ['GET'])]    //Se llama a esta ruta desde el front para obtener los productos por categoria
    public function productosPorCategoria(Request $request, ProductoRepository $repo): JsonResponse
    {
        $categoria = $request->query->get('categoria');

        if ($categoria) {
            $productos = $repo->findRandomByCategoria($categoria, 6);
        } else {
            $productos = $repo->findBy([], ['nombre' => 'ASC'], 200);
        }

        return $this->json($productos, 200, [], ['groups' => 'producto:listado']);
    }





}
