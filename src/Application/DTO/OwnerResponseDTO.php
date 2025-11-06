<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObject\Address;
use App\Domain\Entity\Owner;

use DateTimeImmutable;
use JsonSerializable;

/**
 * DTO pour la réponse d'un propriétaire
 * Transforme l'entité Owner avec ses Value Objects en structure simple
 */
final readonly class OwnerResponseDTO implements JsonSerializable
{
    /**
     * @param array<AnimalSummaryDTO>|null $animals
     */
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $fullName,
        public string $email,
        public ?string $phoneNumber,
        public ?string $phoneNumberFormatted,
        public ?AddressDTO $address,
        public int $totalAnimals,
        public bool $isActive,
        public DateTimeImmutable $registrationDate,
        public DateTimeImmutable $updatedAt,
        public ?array $animals = null,
        public ?OwnerStatisticsDTO $statistics = null,
    ) {
    }

    /**
     * Factory method simple - Sans les animaux
     */
    public static function fromEntity(Owner $owner): self
    {
        $phoneNumber = $owner->getPhoneNumber();
        $address = $owner->getAddress();

        return new self(
            id: $owner->getId(),
            firstName: $owner->getFirstName(),
            lastName: $owner->getLastName(),
            fullName: $owner->getFullName(),
            email: $owner->getEmail(),
            phoneNumber: $phoneNumber ? $phoneNumber : null,
            phoneNumberFormatted: $phoneNumber ? $phoneNumber : null,
            address: $address ? AddressDTO::fromValueObject(Address::fromFullAddress($address)) : null,
            totalAnimals: $owner->getTotalAnimals(),
            isActive: $owner->isActive(),
            registrationDate: $owner->getRegistrationDate(),
            updatedAt: $owner->getUpdatedAt(),
        );
    }

    /**
     * Factory method avec les animaux
     */
    public static function fromEntityWithAnimals(Owner $owner): self
    {
        $phoneNumber = $owner->getPhoneNumber();
        $address = $owner->getAddress();
        $animals = [];
        
        foreach ($owner->getAnimals() as $animal) {
            $animals[] = AnimalSummaryDTO::fromEntity($animal);
        }

        return new self(
            id: $owner->getId(),
            firstName: $owner->getFirstName(),
            lastName: $owner->getLastName(),
            fullName: $owner->getFullName(),
            email: $owner->getEmail(),
            phoneNumber: $phoneNumber ? $phoneNumber : null,
            phoneNumberFormatted: $phoneNumber ? $phoneNumber : null,
            address: $address ? AddressDTO::fromValueObject(Address::fromFullAddress($address)) : null,
            totalAnimals: $owner->getTotalAnimals(),
            isActive: $owner->isActive(),
            registrationDate: $owner->getRegistrationDate(),
            updatedAt: $owner->getUpdatedAt(),
            animals: $animals,
            statistics: OwnerStatisticsDTO::fromOwner($owner),
        );
    }

    /**
     * Factory method pour liste (light version)
     */
    public static function fromEntityForList(Owner $owner): self
    {
        $phoneNumber = $owner->getPhoneNumber();
        $address = $owner->getAddress();

        return new self(
            id: $owner->getId(),
            firstName: $owner->getFirstName(),
            lastName: $owner->getLastName(),
            fullName: $owner->getFullName(),
            email: $owner->getEmail(),
            phoneNumber: $phoneNumber ? $phoneNumber : null,
            phoneNumberFormatted: $phoneNumber ? $phoneNumber : null,
            address: $address ? AddressDTO::fromValueObject(Address::fromFullAddress($address)) : null,
            totalAnimals: $owner->getTotalAnimals(),
            isActive: $owner->isActive(),
            registrationDate: $owner->getRegistrationDate(),
            updatedAt: $owner->getUpdatedAt(),
            animals: null,
            statistics: null,
        );
    }

    /**
     * Implémentation de JsonSerializable
     */
    public function jsonSerialize(): array
    {
        $data = [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'fullName' => $this->fullName,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumberFormatted,
            'totalAnimals' => $this->totalAnimals,
            'isActive' => $this->isActive,
            'registrationDate' => $this->registrationDate->format('c'),
            'updatedAt' => $this->updatedAt->format('c'),
        ];

        // Ajout de l'adresse si disponible
        if ($this->address !== null) {
            $data['address'] = $this->address->toArray();
        }

        // Ajout des animaux si disponibles
        if ($this->animals !== null) {
            $data['animals'] = array_map(
                fn($animal) => $animal->toArray(),
                $this->animals
            );
        }

        // Ajout des statistiques si disponibles
        if ($this->statistics !== null) {
            $data['statistics'] = $this->statistics->toArray();
        }

        return $data;
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    /**
     * Retourne le statut du propriétaire
     */
    public function getStatus(): string
    {
        return $this->isActive ? 'Actif' : 'Inactif';
    }

    /**
     * Durée d'inscription en jours
     */
    public function getMembershipDurationInDays(): int
    {
        $now = new DateTimeImmutable();
        return $now->diff($this->registrationDate)->days;
    }

    /**
     * Version simplifiée pour les listes
     */
    public function toSummary(): array
    {
        return [
            'id' => $this->id,
            'fullName' => $this->fullName,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumberFormatted,
            'totalAnimals' => $this->totalAnimals,
            'isActive' => $this->isActive,
        ];
    }
}