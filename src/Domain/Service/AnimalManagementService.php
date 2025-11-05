<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Animal;
use App\Domain\Entity\Owner;
use App\Domain\Repository\AnimalRepositoryInterface;
use App\Domain\Repository\OwnerRepositoryInterface;

/**
 * Service du domaine pour la gestion des animaux
 * Démontre le principe SRP (Single Responsibility Principle)
 */
final readonly class AnimalManagementService
{
    public function __construct(
        private AnimalRepositoryInterface $animalRepository,
        private OwnerRepositoryInterface $ownerRepository
    ) {
    }

    public function transferOwnership(Animal $animal, Owner $newOwner): void
    {
        $animal->assignOwner($newOwner);
        
        $this->animalRepository->save($animal);
        $this->ownerRepository->save($newOwner);
    }

    public function releaseAnimal(Animal $animal): void
    {
        $owner = $animal->getOwner();
        
        if ($owner !== null) {
            $animal->removeOwner();
            $this->animalRepository->save($animal);
            $this->ownerRepository->save($owner);
        }
    }

    /**
     * @return array{string: int}
     */
    public function getAnimalStatisticsByType(): array
    {
        $animals = $this->animalRepository->findAll();
        $statistics = [];

        foreach ($animals as $animal) {
            $type = $animal->getType();
            $statistics[$type] = ($statistics[$type] ?? 0) + 1;
        }

        return $statistics;
    }

    /**
     * @return Animal[]
     */
    public function findAnimalsNeedingAttention(): array
    {
        $animals = $this->animalRepository->findAll();
        $needingAttention = [];

        foreach ($animals as $animal) {
            // Animaux de plus de 10 ans
            if ($animal->calculateAge() >= 10) {
                $needingAttention[] = $animal;
            }
        }

        return $needingAttention;
    }

    public function calculateAverageAge(): float
    {
        $animals = $this->animalRepository->findAll();
        
        if (empty($animals)) {
            return 0.0;
        }

        $totalAge = array_reduce(
            $animals,
            fn(int $carry, Animal $animal) => $carry + $animal->calculateAge(),
            0
        );

        return $totalAge / count($animals);
    }
}