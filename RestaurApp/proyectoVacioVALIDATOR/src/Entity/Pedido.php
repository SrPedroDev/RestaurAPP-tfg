<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class Pedido
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $fechaHora;

    #[ORM\OneToMany(mappedBy: 'pedido', targetEntity: PedidoItem::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\OneToOne(mappedBy: 'pedido', cascade: ['persist', 'remove'])]
    private ?Atencion $atencion = null;

    public function __construct()
    {
        $this->fechaHora = new \DateTimeImmutable();
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaHora(): \DateTimeInterface
    {
        return $this->fechaHora;
    }

    public function setFechaHora(\DateTimeInterface $fechaHora): static
    {
        $this->fechaHora = $fechaHora;
        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(PedidoItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setPedido($this);
        }
        return $this;
    }

    public function removeItem(PedidoItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getPedido() === $this) {
                $item->setPedido(null);
            }
        }
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

    public function calcularTotal(): float
    {
        return array_reduce($this->items->toArray(), fn($acc, $item) => $acc + $item->getSubtotal(), 0.0);
    }
}
