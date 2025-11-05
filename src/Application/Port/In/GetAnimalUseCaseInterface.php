<?php

declare(strict_types=1);

namespace App\Application\Port\In;

use App\Application\DTO\AnimalResponseDTO;

/**
 * Port In pour récupérer un animal
 */
interface GetAnimalUseCaseInterface
{
    public function execute(int $id): AnimalResponseDTO;

    /**
     * @return AnimalResponseDTO[]
     */
    public function executeAll(): array;
}