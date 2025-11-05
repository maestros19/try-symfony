<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\CreateOwnerDTO;
use App\Application\DTO\OwnerResponseDTO;
use App\Application\Port\In\CreateOwnerUseCaseInterface;
use App\Domain\Entity\Owner;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PhoneNumber;
use App\Domain\ValueObject\Address;

/**
 * Use Case pour créer un propriétaire
 * Démontre le pattern Command et la séparation Domain/Application
 */
final readonly class CreateOwnerUseCase implements CreateOwnerUseCaseInterface
{
    public function __construct(
        private OwnerRepositoryInterface $ownerRepository,
    ) {
    }

    public function execute(CreateOwnerDTO $dto): OwnerResponseDTO
    {
        // Vérifie si l'email existe déjà
        if ($this->ownerRepository->existsByEmail(new Email($dto->email))) {
            throw new \InvalidArgumentException("L'adresse email est déjà utilisée : {$dto->email}");
        }

        // Crée les Value Objects
        $email = new Email($dto->email);
        $phone = new PhoneNumber($dto->phoneNumber);
        $address = new Address(
            $dto->street,
            $dto->city,
            $dto->postalCode,
            $dto->country
        );

        // Instancie le propriétaire (entité du domaine)
        $owner = new Owner(
            firstName: $dto->firstName,
            lastName: $dto->lastName,
            email: $email,
            phoneNumber: $phone,
            address: $address
        );

        $this->ownerRepository->save($owner);

        return OwnerResponseDTO::fromEntity($owner);
    }
}
