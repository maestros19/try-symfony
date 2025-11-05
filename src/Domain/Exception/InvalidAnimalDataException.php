<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidAnimalDataException extends PetManagementException
{
    public static function emptyName(): self
    {
        return new self('Le nom de l\'animal ne peut pas être vide');
    }

    public static function nameTooLong(string $name): self
    {
        return new self(sprintf(
            'Le nom "%s" est trop long (maximum 100 caractères)',
            $name
        ));
    }

    public static function invalidWeight(float $weight): self
    {
        return new self(sprintf(
            'Le poids doit être supérieur à 0 (reçu: %.2f)',
            $weight
        ));
    }

    public static function emptyField(string $fieldName): self
    {
        return new self(sprintf(
            'Le champ "%s" ne peut pas être vide',
            $fieldName
        ));
    }

    public static function fieldTooLong(string $fieldName, string $value): self
    {
        return new self(sprintf(
            'Le champ "%s" est trop long (maximum 100 caractères). Valeur reçue: "%s"',
            $fieldName,
            $value
        ));
    }

    public static function invalidBirthDate(): self
    {
        return new self('La date de naissance ne peut pas être dans le futur');
    }

    public static function invalidWingSpan(float $wingSpan): self
    {
        return new self(sprintf(
            'L\'envergure des ailes doit être positive (reçu: %.2f)',
            $wingSpan
        ));
    }

    public static function invalidBreed(string $breed): self
    {
        return new self(sprintf(
            'La race "%s" n\'est pas valide',
            $breed
        ));
    }
}