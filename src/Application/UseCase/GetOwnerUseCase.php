<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\OwnerResponseDTO;
use App\Application\Port\In\GetOwnerUseCaseInterface;
use App\Domain\Repository\OwnerRepositoryInterface;

/**
 * Use Case pour récupérer un propriétaire
 */
final readonly class GetOwnerUseCase implements GetOwnerUseCaseInterface
{
    public function __construct(
        private OwnerRepositoryInterface $ownerRepository,
    ) {
    }

    public function execute(int $id): OwnerResponseDTO
    {
        $owner = $this->ownerRepository->findById($id);
        return OwnerResponseDTO::fromEntity($owner);
    }

    /**
     * @return OwnerResponseDTO[]
     */
    public function executeAll(): array
    {
        $owners = $this->ownerRepository->findAll();
        
        return array_map(
            fn($owner) => OwnerResponseDTO::fromEntity($owner),
            $owners
        );
    }
}