<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\AnimalResponseDTO;
use App\Application\DTO\CreateAnimalDTO;
use App\Application\Port\In\CreateAnimalUseCaseInterface;
use App\Domain\Entity\Bird;
use App\Domain\Entity\Cat;
use App\Domain\Entity\Dog;
use App\Domain\Entity\Owner;
use App\Domain\Exception\OwnerNotFoundException;
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
        // Récupérer le propriétaire (obligatoire)
        $owner = $this->ownerRepository->findById($dto->ownerId);
        if (!$owner) {
            throw new OwnerNotFoundException(''.$dto->ownerId);
        }

        // Factory pattern pour créer le bon type d'animal
        $animal = match ($dto->type) {
            'dog' => new Dog(
                name: $dto->name,
                birthDate: $dto->birthDate,
                weight: $dto->weight,
                color: $dto->color,
                breed: $dto->breed ?? 'Inconnu',
                isDangerous: $dto->isDangerous ?? false,
                owner: $owner // ✅ Ajout du Owner obligatoire
            ),
            'cat' => new Cat(
                name: $dto->name,
                birthDate: $dto->birthDate,
                weight: $dto->weight,
                color: $dto->color,
                isIndoor: $dto->isIndoor ?? true,
                isHypoallergenic: $dto->isHypoallergenic ?? false,
                owner: $owner // ✅ Ajout du Owner obligatoire
            ),
            'bird' => new Bird(
                name: $dto->name,
                birthDate: $dto->birthDate,
                weight: $dto->weight,
                color: $dto->color,
                species: $dto->species ?? 'Inconnu',
                wingSpan: $dto->wingSpan ?? 0.0,
                canTalk: $dto->canTalk ?? false,
                owner: $owner // ✅ Ajout du Owner obligatoire
            ),
            default => throw new InvalidArgumentException(
                sprintf('Type d\'animal non supporté: %s. Types supportés: dog, cat, bird', $dto->type)
            ),
        };

        $this->animalRepository->save($animal);

        return AnimalResponseDTO::fromEntity($animal);
    }
}