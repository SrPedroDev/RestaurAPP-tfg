<?php

namespace App\Entity;

use App\Repository\DiaSemanaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiaSemanaRepository::class)]
class DiaSemana
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    /**
     * @var Collection<int, Turno>
     */
    #[ORM\OneToMany(targetEntity: Turno::class, mappedBy: 'diaSemana')]
    private Collection $turnos;

    /**
     * @var Collection<int, TurnoGeneral>
     */
    #[ORM\OneToMany(targetEntity: TurnoGeneral::class, mappedBy: 'diaSemana')]
    private Collection $turnoGeneral;

    #[ORM\Column]
    private ?int $numero = null;

    public function __construct()
    {
        $this->turnos = new ArrayCollection();
        $this->turnoGeneral = new ArrayCollection();
    }

    public function getId(): ?  int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * @return Collection<int, Turno>
     */
    public function getTurnos(): Collection
    {
        return $this->turnos;
    }

    public function addTurno(Turno $turno): static
    {
        if (!$this->turnos->contains($turno)) {
            $this->turnos->add($turno);
            $turno->setDiaSemana($this);
        }

        return $this;
    }

    public function removeTurno(Turno $turno): static
    {
        if ($this->turnos->removeElement($turno)) {
            // set the owning side to null (unless already changed)
            if ($turno->getDiaSemana() === $this) {
                $turno->setDiaSemana(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TurnoGeneral>
     */
    public function getTurnoGeneral(): Collection
    {
        return $this->turnoGeneral;
    }

    public function addTurnoGeneral(TurnoGeneral $turnoGeneral): static
    {
        if (!$this->turnoGeneral->contains($turnoGeneral)) {
            $this->turnoGeneral->add($turnoGeneral);
            $turnoGeneral->setDiaSemana($this);
        }

        return $this;
    }

    public function removeTurnoGeneral(TurnoGeneral $turnoGeneral): static
    {
        if ($this->turnoGeneral->removeElement($turnoGeneral)) {
            // set the owning side to null (unless already changed)
            if ($turnoGeneral->getDiaSemana() === $this) {
                $turnoGeneral->setDiaSemana(null);
            }
        }

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }
}
