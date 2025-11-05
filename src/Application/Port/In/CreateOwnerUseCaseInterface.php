<?php

declare(strict_types=1);

namespace App\Application\Port\In;

use App\Application\DTO\CreateOwnerDTO;
use App\Application\DTO\OwnerResponseDTO;

/**
 * Port In pour créer un propriétaire
 */
interface CreateOwnerUseCaseInterface
{
    public function execute(CreateOwnerDTO $dto): OwnerResponseDTO;
}