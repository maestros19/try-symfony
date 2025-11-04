<?php

declare(strict_types=1);

namespace App\Domain\Exception;


final class InvalidEmailException extends PetManagementException
{
    public static function invalidFormat(string $email): self
    {
        return new self(sprintf(
            'L\'email "%s" n\'est pas valide',
            $email
        ));
    }
}