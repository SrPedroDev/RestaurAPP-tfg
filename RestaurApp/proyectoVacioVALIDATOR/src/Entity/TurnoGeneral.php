<?php

namespace App\Entity;

use App\Repository\TurnoGeneralRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TurnoGeneralRepository::class)]
class TurnoGeneral
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'turnoGeneral')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DiaSemana $diaSemana = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $horaInicio = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $horaFin = null;

    #[ORM\Column(length: 255)]
    private ?string $tipo = null;

    #[ORM\Column]
    private ?int $reservasPorMesa = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiaSemana(): ?DiaSemana
    {
        return $this->diaSemana;
    }

    public function setDiaSemana(?DiaSemana $diaSemana): static
    {
        $this->diaSemana = $diaSemana;

        return $this;
    }

    public function getHoraInicio(): ?\DateTimeInterface
    {
        return $this->horaInicio;
    }

    public function setHoraInicio(\DateTimeInterface $horaInicio): static
    {
        $this->horaInicio = $horaInicio;

        return $this;
    }

    public function getHoraFin(): ?\DateTimeInterface
    {
        return $this->horaFin;
    }

    public function setHoraFin(\DateTimeInterface $horaFin): static
    {
        $this->horaFin = $horaFin;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getReservasPorMesa(): ?int
    {
        return $this->reservasPorMesa;
    }

    public function setReservasPorMesa(int $reservasPorMesa): static
    {
        $this->reservasPorMesa = $reservasPorMesa;

        return $this;
    }
}
