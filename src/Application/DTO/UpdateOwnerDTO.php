<?php

declare(strict_types=1);

namespace App\Application\DTO;


/**
 * DTO pour la mise à jour d'un propriétaire
 * Tous les champs sont optionnels
 */
final readonly class UpdateOwnerDTO
{
    public function __construct(
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?string $phoneNumber = null,
        public ?string $street = null,
        public ?string $city = null,
        public ?string $postalCode = null,
        public ?string $country = null,
    ) {
    }

    /**
     * Factory method depuis un tableau
     * 
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['firstName'] ?? $data['first_name'] ?? null,
            lastName: $data['lastName'] ?? $data['last_name'] ?? null,
            email: $data['email'] ?? null,
            phoneNumber: $data['phoneNumber'] ?? $data['phone_number'] ?? null,
            street: $data['street'] ?? null,
            city: $data['city'] ?? null,
            postalCode: $data['postalCode'] ?? $data['postal_code'] ?? null,
            country: $data['country'] ?? null,
        );
    }

    /**
     * Retourne uniquement les champs non null
     */
    public function toArray(): array
    {
        return array_filter([
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'street' => $this->street,
            'city' => $this->city,
            'postalCode' => $this->postalCode,
            'country' => $this->country,
        ], fn($value) => $value !== null);
    }

    /**
     * Vérifie s'il y a au moins un champ à mettre à jour
     */
    public function hasChanges(): bool
    {
        return !empty($this->toArray());
    }
}