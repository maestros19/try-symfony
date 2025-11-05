<?php

declare(strict_types=1);

namespace App\Application\DTO;

/**
 * DTO pour la création d'un propriétaire
 * Input DTO - Représente les données entrantes
 */
final readonly class CreateOwnerDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $phoneNumber,
        public string $street,
        public string $city,
        public string $postalCode,
        public string $country = 'France',
    ) {
    }

    /**
     * Factory method depuis un tableau (utile pour les formulaires)
     * 
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['firstName'] ?? $data['first_name'] ?? '',
            lastName: $data['lastName'] ?? $data['last_name'] ?? '',
            email: $data['email'] ?? '',
            phoneNumber: $data['phoneNumber'] ?? $data['phone_number'] ?? '',
            street: $data['street'] ?? '',
            city: $data['city'] ?? '',
            postalCode: $data['postalCode'] ?? $data['postal_code'] ?? '',
            country: $data['country'] ?? 'France',
        );
    }

    /**
     * Conversion en tableau
     */
    public function toArray(): array
    {
        return [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'street' => $this->street,
            'city' => $this->city,
            'postalCode' => $this->postalCode,
            'country' => $this->country,
        ];
    }

    /**
     * Validation basique (la vraie validation est dans les Value Objects)
     */
    public function validate(): array
    {
        $errors = [];

        if (empty(trim($this->firstName))) {
            $errors['firstName'] = 'Le prénom est obligatoire';
        }

        if (empty(trim($this->lastName))) {
            $errors['lastName'] = 'Le nom est obligatoire';
        }

        if (empty(trim($this->email))) {
            $errors['email'] = 'L\'email est obligatoire';
        }

        if (empty(trim($this->phoneNumber))) {
            $errors['phoneNumber'] = 'Le téléphone est obligatoire';
        }

        if (empty(trim($this->street))) {
            $errors['street'] = 'La rue est obligatoire';
        }

        if (empty(trim($this->city))) {
            $errors['city'] = 'La ville est obligatoire';
        }

        if (empty(trim($this->postalCode))) {
            $errors['postalCode'] = 'Le code postal est obligatoire';
        }

        return $errors;
    }

    /**
     * Vérifie si le DTO est valide
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }

    /**
     * Retourne le nom complet
     */
    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }
}

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