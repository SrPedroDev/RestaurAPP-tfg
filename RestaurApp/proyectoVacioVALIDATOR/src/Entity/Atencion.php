<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Atencion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $inicio;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $fin = null;

    #[ORM\OneToOne(mappedBy: 'atencion', cascade: ['persist', 'remove'])]
    private ?Reserva $reserva = null;

    #[ORM\OneToOne(inversedBy: 'atencion', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pedido $pedido = null;

    public function __construct()
    {
        $this->inicio = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInicio(): \DateTimeInterface
    {
        return $this->inicio;
    }

    public function setInicio(\DateTimeInterface $inicio): static
    {
        $this->inicio = $inicio;
        return $this;
    }

    public function getFin(): ?\DateTimeInterface
    {
        return $this->fin;
    }

    public function setFin(?\DateTimeInterface $fin): static
    {
        $this->fin = $fin;
        return $this;
    }

    public function getReserva(): ?Reserva
    {
        return $this->reserva;
    }

    public function setReserva(?Reserva $reserva): static
    {
        $this->reserva = $reserva;
        return $this;
    }

    public function getPedido(): ?Pedido
    {
        return $this->pedido;
    }

    public function setPedido(?Pedido $pedido): static
    {
        $this->pedido = $pedido;
        return $this;
    }

    public function calcularTotal(): float
    {
        return $this->pedido?->calcularTotal() ?? 0.0;
    }
}
