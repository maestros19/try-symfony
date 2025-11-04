<?php

declare(strict_types=1);

namespace App\Domain\Exception;


final class InvalidAddressException extends PetManagementException
{
    public static function emptyStreet(): self
    {
        return new self('La rue ne peut pas être vide');
    }

    public static function emptyCity(): self
    {
        return new self('La ville ne peut pas être vide');
    }

    public static function emptyPostalCode(): self
    {
        return new self('Le code postal ne peut pas être vide');
    }

    public static function invalidPostalCode(string $code): self
    {
        return new self(sprintf(
            'Le code postal "%s" n\'est pas valide (format attendu: 5 chiffres)',
            $code
        ));
    }

    public static function emptyCountry(): self
    {
        return new self('Le pays ne peut pas être vide');
    }
}