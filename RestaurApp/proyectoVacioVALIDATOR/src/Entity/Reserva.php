<?php

namespace App\Entity;

use App\Repository\ReservaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservaRepository::class)]
class Reserva
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 255)]
    private ?string $nombreCliente = null;

    #[ORM\Column]
    private ?string $telefono = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $numeroComensales = null;

    #[ORM\ManyToOne(inversedBy: 'reservas')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Mesa $mesa = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaHora = null;


    #[ORM\OneToOne(mappedBy: 'reserva')]
    private ?MomentoReserva $momentoReserva = null;

    #[ORM\OneToOne(inversedBy: 'reserva', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Atencion $atencion = null;

    public function __construct()
    {
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreCliente(): ?string
    {
        return $this->nombreCliente;
    }

    public function setNombreCliente(string $nombreCliente): static
    {
        $this->nombreCliente = $nombreCliente;

        return $this;
    }

    public function getTelefono(): ?int
    {
        return $this->telefono;
    }

    public function setTelefono(int $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getNumeroComensales(): ?int
    {
        return $this->numeroComensales;
    }

    public function setNumeroComensales(int $numeroComensales): static
    {
        $this->numeroComensales = $numeroComensales;

        return $this;
    }

    public function getMesa(): ?mesa
    {
        return $this->mesa;
    }

    public function setMesa(?mesa $mesa): static
    {
        $this->mesa = $mesa;

        return $this;
    }

    public function getFechaHora(): ?\DateTimeInterface
    {
        return $this->fechaHora;
    }

    public function setFechaHora(\DateTimeInterface $fechaHora): static
    {
        $this->fechaHora = $fechaHora;

        return $this;
    }


    public function getMomentoReserva(): ?MomentoReserva
    {
        return $this->momentoReserva;
    }

    public function setMomentoReserva(?MomentoReserva $momentoReserva): static
    {
        // unset the owning side of the relation if necessary
        if ($momentoReserva === null && $this->momentoReserva !== null) {
            $this->momentoReserva->setReserva(null);
        }

        // set the owning side of the relation if necessary
        if ($momentoReserva !== null && $momentoReserva->getReserva() !== $this) {
            $momentoReserva->setReserva($this);
        }

        $this->momentoReserva = $momentoReserva;

        return $this;
    }

    public function getAtencion(): ?Atencion
    {
        return $this->atencion;
    }

    public function setAtencion(?Atencion $atencion): static
    {
        $this->atencion = $atencion;

        return $this;
    }
    
}
