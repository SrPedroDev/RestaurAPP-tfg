<?php

namespace App\Entity;

use App\Repository\ProductoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductoRepository::class)]
class Producto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['producto:listado'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['producto:listado'])]
    #[Assert\NotBlank(message: 'El nombre no puede estar vacío.')]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    #[Groups(['producto:listado'])]
    #[Assert\NotBlank(message: 'La descripción no puede estar vacía.')]
    private ?string $descripcion = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['producto:listado'])]
    #[Assert\NotBlank(message: 'El precio no puede estar vacío.')]
    private ?string $precio = null;

    #[ORM\ManyToOne(inversedBy: 'productos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categoria $categoria = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['producto:listado'])]
    private ?string $imagen = null;

    #[Groups(['producto:listado'])]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(['producto:listado'])]
    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    #[Groups(['producto:listado'])]
    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    #[Groups(['producto:listado'])]
    public function getPrecio(): ?string
    {
        return $this->precio;
    }

    public function setPrecio(string $precio): static
    {
        $this->precio = $precio;

        return $this;
    }

    public function getCategoria(): ?Categoria
    {
        return $this->categoria;
    }

    public function setCategoria(?Categoria $categoria): static
    {
        $this->categoria = $categoria;

        return $this;
    }

    #[Groups(['producto:listado'])]
    public function getCategoriaNombre(): ?string
    {
        return $this->categoria?->getNombre();
    }

    #[Groups(['producto:listado'])]
    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    public function setImagen(?string $imagen): static
    {
        $this->imagen = $imagen;

        return $this;
    }
}
