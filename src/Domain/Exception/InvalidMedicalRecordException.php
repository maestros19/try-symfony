<?php

declare(strict_types=1);

namespace App\Domain\Exception;


final class InvalidMedicalRecordException extends PetManagementException
{
    public static function emptyVeterinarianName(): self
    {
        return new self('Le nom du vétérinaire ne peut pas être vide');
    }

    public static function emptyDiagnosis(): self
    {
        return new self('Le diagnostic ne peut pas être vide');
    }

    public static function negativeCost(float $cost): self
    {
        return new self(sprintf(
            'Le coût ne peut pas être négatif (reçu: %.2f)',
            $cost
        ));
    }
}