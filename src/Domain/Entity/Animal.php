<?php

namespace App\Domain\Entity;

use App\Repository\AnimalRepository;
use Doctrine\ORM\Mapping as ORM;

use App\Domain\Exception\InvalidAnimalDataException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Classe abstraite Animal - Démontre l'héritage et le polymorphisme
 * Utilise le principe OCP (Open/Closed Principle)
 */
#[ORM\Entity(repositoryClass: AnimalRepository::class)]
abstract class Animal
{

    /**
     * ########################################
     * Attributes
     * ########################################
     */

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 2255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTime $birthDate = null;

    #[ORM\Column]
    private ?float $weight = null;

    #[ORM\Column(length: 30)]
    private ?string $color = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;


    /**
     * ########################################
     * Getters &  Setters
     * ########################################
     */


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTime $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * ########################################
     * Metthods
     * ########################################
     */
}
