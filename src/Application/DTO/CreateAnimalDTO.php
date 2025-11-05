<?php

declare(strict_types=1);

namespace App\Application\DTO;

use DateTimeImmutable;

/**
 * DTO pour la création d'un animal
 * Démontre le principe ISP (Interface Segregation Principle)
 */
final readonly class CreateAnimalDTO
{
    public function __construct(
        public string $type,
        public string $name,
        public DateTimeImmutable $birthDate,
        public float $weight,
        public string $color,
        public ?int $ownerId = null,
        // Champs spécifiques Dog
        public ?string $breed = null,
        public ?bool $isDangerous = null,
        // Champs spécifiques Cat
        public ?bool $isIndoor = null,
        public ?bool $isHypoallergenic = null,
        // Champs spécifiques Bird
        public ?string $species = null,
        public ?float $wingSpan = null,
        public ?bool $canTalk = null,
    ) {
    }
}