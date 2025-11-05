<?php

declare(strict_types=1);

namespace App\Application\DTO;

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
        public string $phoneNumber,
        public string $phoneNumberFormatted,
        public AddressDTO $address,
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
        return new self(
            id: $owner->getId(),
            firstName: $owner->getFirstName(),
            lastName: $owner->getLastName(),
            fullName: $owner->getFullName(),
            email: $owner->getEmail()->getValue(),
            phoneNumber: $owner->getPhoneNumber()->getValue(),
            phoneNumberFormatted: $owner->getPhoneNumber()->getFormatted(),
            address: AddressDTO::fromValueObject($owner->getAddress()),
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
        $animals = [];
        foreach ($owner->getAnimals() as $animal) {
            $animals[] = AnimalSummaryDTO::fromEntity($animal);
        }

        return new self(
            id: $owner->getId(),
            firstName: $owner->getFirstName(),
            lastName: $owner->getLastName(),
            fullName: $owner->getFullName(),
            email: $owner->getEmail()->getValue(),
            phoneNumber: $owner->getPhoneNumber()->getValue(),
            phoneNumberFormatted: $owner->getPhoneNumber()->getFormatted(),
            address: AddressDTO::fromValueObject($owner->getAddress()),
            totalAnimals: $owner->getTotalAnimals(),
            isActive: $owner->isActive(),
            registrationDate: $owner->getRegistrationDate(),
            updatedAt: $owner->getUpdatedAt(),
            animals: $animals,
            statistics: OwnerStatisticsDTO::fromOwner($owner),
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
            'address' => $this->address->toArray(),
            'totalAnimals' => $this->totalAnimals,
            'isActive' => $this->isActive,
            'registrationDate' => $this->registrationDate->format('c'),
            'updatedAt' => $this->updatedAt->format('c'),
        ];

        if ($this->animals !== null) {
            $data['animals'] = array_map(
                fn($animal) => $animal->toArray(),
                $this->animals
            );
        }

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
            'totalAnimals' => $this->totalAnimals,
            'isActive' => $this->isActive,
        ];
    }
}

/**
 * DTO pour l'adresse
 */
final readonly class AddressDTO
{
    public function __construct(
        public string $street,
        public string $city,
        public string $postalCode,
        public string $country,
        public string $fullAddress,
    ) {
    }

    public static function fromValueObject(\App\Domain\ValueObject\Address $address): self
    {
        return new self(
            street: $address->getStreet(),
            city: $address->getCity(),
            postalCode: $address->getPostalCode(),
            country: $address->getCountry(),
            fullAddress: $address->getFullAddress(),
        );
    }

    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'city' => $this->city,
            'postalCode' => $this->postalCode,
            'country' => $this->country,
            'fullAddress' => $this->fullAddress,
        ];
    }
}

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