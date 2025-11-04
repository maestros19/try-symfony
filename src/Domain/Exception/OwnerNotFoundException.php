<?php

declare(strict_types=1);

namespace App\Domain\Exception;


final class OwnerNotFoundException extends PetManagementException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Propriétaire avec l\'ID %d non trouvé', $id));
    }

    public static function withEmail(string $email): self
    {
        return new self(sprintf('Propriétaire avec l\'email "%s" non trouvé', $email));
    }
}