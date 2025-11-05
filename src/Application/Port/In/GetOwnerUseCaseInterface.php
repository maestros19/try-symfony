<?php

declare(strict_types=1);

namespace App\Application\Port\In;

use App\Application\DTO\OwnerResponseDTO;

/**
 * Port In pour récupérer un propriétaire
 */
interface GetOwnerUseCaseInterface
{
    public function execute(int $id): OwnerResponseDTO;

    /**
     * @return OwnerResponseDTO[]
     */
    public function executeAll(): array;
}