<?php

declare(strict_types=1);

namespace App\Domain\Exception;


final class InvalidPhoneNumberException extends PetManagementException
{
    public static function invalidFormat(string $phoneNumber): self
    {
        return new self(sprintf(
            'Le numéro de téléphone "%s" n\'est pas valide',
            $phoneNumber
        ));
    }
}