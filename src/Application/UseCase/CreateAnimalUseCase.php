<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\AnimalResponseDTO;
use App\Application\DTO\CreateAnimalDTO;
use App\Application\Port\In\CreateAnimalUseCaseInterface;
use App\Domain\Entity\Bird;
use App\Domain\Entity\Cat;
use App\Domain\Entity\Dog;
use App\Domain\Repository\AnimalRepositoryInterface;
use App\Domain\Repository\OwnerRepositoryInterface;
use InvalidArgumentException;

/**
 * Use Case pour créer un animal
 * Démontre le principe SRP et le pattern Command
 */
final readonly class CreateAnimalUseCase implements CreateAnimalUseCaseInterface
{
    public function __construct(
        private AnimalRepositoryInterface $animalRepository,
        private OwnerRepositoryInterface $ownerRepository,
    ) {
    }

    public function execute(CreateAnimalDTO $dto): AnimalResponseDTO
    {
        // Factory pattern pour créer le bon type d'animal
        $animal = match ($dto->type) {
            'Dog', 'Chien' => new Dog(
                name: $dto->name,
                birthDate: $dto->birthDate,
                weight: $dto->weight,
                color: $dto->color,
                breed: $dto->breed ?? 'Inconnu',
                isDangerous: $dto->isDangerous ?? false,
            ),
            'Cat', 'Chat' => new Cat(
                name: $dto->name,
                birthDate: $dto->birthDate,
                weight: $dto->weight,
                color: $dto->color,
                isIndoor: $dto->isIndoor ?? true,
                isHypoallergenic: $dto->isHypoallergenic ?? false,
            ),
            'Bird', 'Oiseau' => new Bird(
                name: $dto->name,
                birthDate: $dto->birthDate,
                weight: $dto->weight,
                color: $dto->color,
                species: $dto->species ?? 'Inconnu',
                wingSpan: $dto->wingSpan ?? 0.0,
                canTalk: $dto->canTalk ?? false,
            ),
            default => throw new InvalidArgumentException(
                sprintf('Type d\'animal non supporté: %s', $dto->type)
            ),
        };

        // Assignation du propriétaire si fourni
        if ($dto->ownerId !== null) {
            $owner = $this->ownerRepository->findById($dto->ownerId);
            $animal->assignOwner($owner);
        }

        $this->animalRepository->save($animal);

        return AnimalResponseDTO::fromEntity($animal);
    }
}