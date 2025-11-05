<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\AnimalResponseDTO;
use App\Application\Port\In\GetAnimalUseCaseInterface;
use App\Domain\Repository\AnimalRepositoryInterface;

/**
 * Use Case pour récupérer un ou plusieurs animaux
 * 
 * - Implémente le Port In GetAnimalUseCaseInterface
 * - Applique le principe Query
 */
final readonly class GetAnimalUseCase implements GetAnimalUseCaseInterface
{
    public function __construct(
        private AnimalRepositoryInterface $animalRepository,
    ) {
    }

    public function execute(int $id): AnimalResponseDTO
    {
        $animal = $this->animalRepository->findById($id);
        return AnimalResponseDTO::fromEntity($animal);
    }

    /**
     * @return AnimalResponseDTO[]
     */
    public function executeAll(): array
    {
        $animals = $this->animalRepository->findAll();

        return array_map(
            fn($animal) => AnimalResponseDTO::fromEntity($animal),
            $animals
        );
    }
}
