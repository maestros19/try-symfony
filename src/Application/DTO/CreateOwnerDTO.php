<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PhoneNumber;
use App\Domain\ValueObject\Address;
use App\Domain\Exception\InvalidOwnerDataException;

/**
 * DTO pour la création d'un propriétaire
 * Input DTO - Représente les données entrantes avec les mêmes types que l'entité
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
     * Factory method depuis un tableau
     * 
     * @param array<string, mixed> $data
     * @throws InvalidOwnerDataException
     */
    public static function fromArray(array $data): self
    {
        try {
            // Validation des champs obligatoires
            self::validateRequiredFields($data);

            return new self(
                firstName: trim($data['firstName'] ?? $data['first_name'] ?? ''),
                lastName: trim($data['lastName'] ?? $data['last_name'] ?? ''),
                email: $data['email'],
                phoneNumber: $data['phoneNumber'] ?? $data['phone_number'] ?? '',
                street: $street ?? '',
                city: $city ?? '',
                postalCode: $postalCode ?? '',
                country: $country ?? 'France'
            );
        } catch (\InvalidArgumentException $e) {
            throw new InvalidOwnerDataException($e->getMessage(), previous: $e);
        }
    }

    /**
     * Validation des champs obligatoires
     */
    private static function validateRequiredFields(array $data): void
    {
        $requiredFields = ['firstName', 'lastName', 'email', 'phoneNumber', 'street', 'city', 'postalCode'];
        
        foreach ($requiredFields as $field) {
            $value = $data[$field] ?? $data[self::camelToSnake($field)] ?? null;
            
            if (empty(trim((string) $value))) {
                throw new InvalidOwnerDataException("Le champ $field est obligatoire");
            }
        }
    }

    /**
     * Conversion camelCase vers snake_case
     */
    private static function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * Conversion en tableau pour potentielle sérialisation
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
     * Retourne le nom complet
     */
    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }
}