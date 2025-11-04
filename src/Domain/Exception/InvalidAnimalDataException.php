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
}