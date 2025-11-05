<?php

declare(strict_types=1);

namespace App\Application\Port\In;

use App\Application\DTO\AnimalResponseDTO;
use App\Application\DTO\CreateAnimalDTO;

/**
 * Port In pour créer un animal
 */
interface CreateAnimalUseCaseInterface
{
    public function execute(CreateAnimalDTO $dto): AnimalResponseDTO;
}