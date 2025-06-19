<?php
// src/Command/GenerarTurnosCommand.php
namespace App\Command;

use App\Service\TurnoGeneratorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerarTurnosCommand extends Command
{
    protected static $defaultName = 'app:generar-turnos';

    private TurnoGeneratorService $turnoGenerator;

    public function __construct(TurnoGeneratorService $turnoGenerator)
    {
        parent::__construct();
        $this->turnoGenerator = $turnoGenerator;
    }

    protected function configure()
    {
        $this
            ->setDescription('Genera los turnos automáticamente para el rango definido.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

    

        $this->turnoGenerator->generarTurnos();

        $output->writeln('Comando de Generación de Turnos Ejecutada.');

        return Command::SUCCESS;
    }
}
