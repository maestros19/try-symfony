<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\Owner;
use DateTimeImmutable;
use JsonSerializable;

/**
 * DTO pour statistiques du propriétaire
 */
final readonly class OwnerStatisticsDTO
{
    /**
     * @param array<string, int> $animalsByType
     */
    public function __construct(
        public int $totalAnimals,
        public array $animalsByType,
        public float $averageAge,
        public int $seniorAnimals,
    ) {
    }

    public static function fromOwner(Owner $owner): self
    {
        $animals = $owner->getAnimals()->toArray();
        $animalsByType = [];
        $totalAge = 0;
        $seniorCount = 0;

        foreach ($animals as $animal) {
            $type = $animal->getType();
            $animalsByType[$type] = ($animalsByType[$type] ?? 0) + 1;
            
            $age = $animal->calculateAge();
            $totalAge += $age;
            
            if ($age >= 7) {
                $seniorCount++;
            }
        }

        $averageAge = count($animals) > 0 ? $totalAge / count($animals) : 0;

        return new self(
            totalAnimals: count($animals),
            animalsByType: $animalsByType,
            averageAge: round($averageAge, 1),
            seniorAnimals: $seniorCount,
        );
    }

    public function toArray(): array
    {
        return [
            'totalAnimals' => $this->totalAnimals,
            'animalsByType' => $this->animalsByType,
            'averageAge' => $this->averageAge,
            'seniorAnimals' => $this->seniorAnimals,
        ];
    }
}