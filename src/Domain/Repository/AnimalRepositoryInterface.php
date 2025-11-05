<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Animal;
use App\Domain\Entity\Owner;
use App\Domain\Exception\AnimalNotFoundException;

/**
 * Interface du repository Animal - Port (Hexagonal Architecture)
 * Démontre le principe DIP (Dependency Inversion Principle)
 */
interface AnimalRepositoryInterface
{
    /**
     * @throws AnimalNotFoundException
     */
    public function findById(int $id): Animal;

    /**
     * @return Animal[]
     */
    public function findAll(): array;

    /**
     * @return Animal[]
     */
    public function findByOwner(Owner $owner): array;

    /**
     * @return Animal[]
     */
    public function findByType(string $type): array;

    /**
     * @return Animal[]
     */
    public function findByAgeRange(int $minAge, int $maxAge): array;

    /**
     * @param array<string, mixed> $criteria
     * @return Animal[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function save(Animal $animal): void;

    public function delete(Animal $animal): void;

    public function countAll(): int;
}