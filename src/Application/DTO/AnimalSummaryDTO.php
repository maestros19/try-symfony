<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\Owner;
use DateTimeImmutable;
use JsonSerializable;

/**
 * DTO pour résumé d'animal
 */
final readonly class AnimalSummaryDTO
{
    public function __construct(
        public int $id,
        public string $type,
        public string $name,
        public int $age,
        public string $color,
    ) {
    }

    public static function fromEntity(\App\Domain\Entity\Animal $animal): self
    {
        return new self(
            id: $animal->getId(),
            type: $animal->getType(),
            name: $animal->getName(),
            age: $animal->calculateAge(),
            color: $animal->getColor(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'age' => $this->age,
            'color' => $this->color,
        ];
    }
}
