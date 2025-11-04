<?php

declare(strict_types=1);

namespace App\Domain\Exception;


final class InvalidOwnerDataException extends PetManagementException
{
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
            'Le champ "%s" est trop long (valeur: "%s", maximum: 100 caractères)',
            $fieldName,
            $value
        ));
    }
}