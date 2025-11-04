<?php

declare(strict_types=1);

namespace App\Domain\Exception;


final class AnimalNotFoundException extends PetManagementException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Animal avec l\'ID %d non trouvé', $id));
    }
}