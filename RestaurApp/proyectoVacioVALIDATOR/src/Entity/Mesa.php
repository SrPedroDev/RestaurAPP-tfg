<?php

namespace App\Entity;

use App\Repository\MesaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MesaRepository::class)]
class Mesa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    private ?string $identificador = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $capacidad = null;

    /**
     * @var Collection<int, Reserva>
     */
    #[ORM\OneToMany(targetEntity: Reserva::class, mappedBy: 'mesa')]
    private Collection $reservas;

    /**
     * @var Collection<int, MomentoReserva>
     */
    #[ORM\OneToMany(targetEntity: MomentoReserva::class, mappedBy: 'mesa')]
    private Collection $momentoReserva;

    #[ORM\Column]
    private ?bool $operativa = null;

    public function __construct()
    {
        $this->reservas = new ArrayCollection();
        $this->momentoReserva = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentificador(): ?string
    {
        return $this->identificador;
    }

    public function setIdentificador(string $identificador): static
    {
        $this->identificador = $identificador;

        return $this;
    }

    public function getCapacidad(): ?int
    {
        return $this->capacidad;
    }

    public function setCapacidad(int $capacidad): static
    {
        $this->capacidad = $capacidad;

        return $this;
    }

    /**
     * @return Collection<int, Reserva>
     */
    public function getReservas(): Collection
    {
        return $this->reservas;
    }

    public function addReserva(Reserva $reserva): static
    {
        if (!$this->reservas->contains($reserva)) {
            $this->reservas->add($reserva);
            $reserva->setMesa($this);
        }

        return $this;
    }

    public function removeReserva(Reserva $reserva): static
    {
        if ($this->reservas->removeElement($reserva)) {
            // set the owning side to null (unless already changed)
            if ($reserva->getMesa() === $this) {
                $reserva->setMesa(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, MomentoReserva>
     */
    public function getMomentoReserva(): Collection
    {
        return $this->momentoReserva;
    }

    public function addMomentoReserva(MomentoReserva $momentoReserva): static
    {
        if (!$this->momentoReserva->contains($momentoReserva)) {
            $this->momentoReserva->add($momentoReserva);
            $momentoReserva->setMesa($this);
        }

        return $this;
    }

    public function removeMomentoReserva(MomentoReserva $momentoReserva): static
    {
        if ($this->momentoReserva->removeElement($momentoReserva)) {
            // set the owning side to null (unless already changed)
            if ($momentoReserva->getMesa() === $this) {
                $momentoReserva->setMesa(null);
            }
        }

        return $this;
    }

    public function isOperativa(): ?bool
    {
        return $this->operativa;
    }

    public function setOperativa(bool $operativa): static
    {
        $this->operativa = $operativa;

        return $this;
    }
}
