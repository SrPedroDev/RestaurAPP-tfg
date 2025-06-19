<?php

namespace App\Entity;

use App\Repository\TurnoRepository;
use App\Entity\DiaSemana;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TurnoRepository::class)]
class Turno
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $horaInicio = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $horaFin = null;

    #[ORM\ManyToOne(inversedBy: 'turnos')]
    private ?DiaSemana $diaSemana = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\Column(length: 255)]
    private ?string $tipo = null;

    /**
     * @var Collection<int, MomentoReserva>
     */
    #[ORM\OneToMany(targetEntity: MomentoReserva::class, mappedBy: 'turno')]
    private Collection $momentoReservas;

    #[ORM\Column]
    private ?int $reservasPorMesa = null;

    public function __construct()
    {
        $this->momentoReservas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDiaSemana(): ?DiaSemana
    {
        return $this->diaSemana;
    }

    public function setDiaSemana(?DiaSemana $diaSemana): static
    {
        $this->diaSemana = $diaSemana;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): static
    {
        $this->fecha = $fecha;

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

    /**
     * @return Collection<int, MomentoReserva>
     */
    public function getMomentoReservas(): Collection
    {
        return $this->momentoReservas;
    }

    public function addMomentoReserva(MomentoReserva $momentoReserva): static
    {
        if (!$this->momentoReservas->contains($momentoReserva)) {
            $this->momentoReservas->add($momentoReserva);
            $momentoReserva->setTurno($this);
        }

        return $this;
    }

    public function removeMomentoReserva(MomentoReserva $momentoReserva): static
    {
        if ($this->momentoReservas->removeElement($momentoReserva)) {
            // set the owning side to null (unless already changed)
            if ($momentoReserva->getTurno() === $this) {
                $momentoReserva->setTurno(null);
            }
        }

        return $this;
    }

    public function getreservasPorMesa(): ?int
    {
        return $this->reservasPorMesa;
    }

    public function setreservasPorMesa(int $reservasPorMesa): static
    {
        $this->reservasPorMesa = $reservasPorMesa;

        return $this;
    }
}
