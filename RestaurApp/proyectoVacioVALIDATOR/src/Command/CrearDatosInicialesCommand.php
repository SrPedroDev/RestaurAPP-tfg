<?php

namespace App\Command;

use App\Entity\Menu;
use App\Entity\DiaSemana;
use App\Entity\Categoria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:crear-datos-iniciales',description: 'Inserta menús, días de la semana y categorías iniciales.',)]
class CrearDatosInicialesCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // --- MENÚS ---
        $menus = [
            ['titulo' => 'Gestión Carta', 'enlace' => 'listar_categorias', 'orden' => 3],
            ['titulo' => 'Gestion Turnos', 'enlace' => 'inicio_turno', 'orden' => 2],
            ['titulo' => 'Gestion Sala', 'enlace' => 'gestion_sala_menu', 'orden' => 1],
            ['titulo' => 'Gestión Mesas', 'enlace' => 'gestion_mesas_index', 'orden' => 4],
            ['titulo' => 'Registros de reservas', 'enlace' => 'historico_reservas_calendario', 'orden' => 5],
        ];


        foreach ($menus as $data) {
            $exists = $this->em->getRepository(Menu::class)->findOneBy(['enlace' => $data['enlace']]);
            if (!$exists) {
                $menu = new Menu();
                $menu->setTitulo($data['titulo']);
                $menu->setEnlace($data['enlace']);
                $menu->setOrden($data['orden']);
                $this->em->persist($menu);
            }
        }

        // --- DÍAS DE LA SEMANA ---
        $dias = [
            ['nombre' => 'Lunes', 'numero' => 1],
            ['nombre' => 'Martes', 'numero' => 2],
            ['nombre' => 'Miercoles', 'numero' => 3],
            ['nombre' => 'Jueves', 'numero' => 4],
            ['nombre' => 'Viernes', 'numero' => 5],
            ['nombre' => 'Sabado', 'numero' => 6],
            ['nombre' => 'Domingo', 'numero' => 7],
        ];

        foreach ($dias as $data) {
            $exists = $this->em->getRepository(DiaSemana::class)->findOneBy(['numero' => $data['numero']]);
            if (!$exists) {
                $dia = new DiaSemana();
                $dia->setNombre($data['nombre']);
                $dia->setNumero($data['numero']);
                $this->em->persist($dia);
            }
        }

        // --- CATEGORÍAS ---
        $categorias = [
            ['nombre' => 'Primeros', 'icono' => 'primero.png'],
            ['nombre' => 'Segundos', 'icono' => 'segundo.png'],
            ['nombre' => 'Entrantes', 'icono' => 'entrante.png'],
            ['nombre' => 'Postres', 'icono' => 'postres.png'],
            ['nombre' => 'Bebidas', 'icono' => 'bebidas.png'],
        ];

        foreach ($categorias as $data) {
            $exists = $this->em->getRepository(Categoria::class)->findOneBy(['nombre' => $data['nombre']]);
            if (!$exists) {
                $categoria = new Categoria();
                $categoria->setNombre($data['nombre']);
                $categoria->setIcono($data['icono']);
                $this->em->persist($categoria);
            }
        }

        $this->em->flush();

        $output->writeln('<info>Datos iniciales creados correctamente.</info>');

        return Command::SUCCESS;
    }
}
