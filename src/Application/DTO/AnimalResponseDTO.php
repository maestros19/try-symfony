<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\Animal;
use App\Domain\Entity\Dog;
use App\Domain\Entity\Cat;
use App\Domain\Entity\Bird;
use DateTimeImmutable;
use JsonSerializable;

/**
 * DTO pour la réponse d'un animal
 * Transforme une entité complexe en structure simple pour l'API
 */
final readonly class AnimalResponseDTO implements JsonSerializable
{
    public function __construct(
        public int $id,
        public string $type,
        public string $name,
        public DateTimeImmutable $birthDate,
        public int $age,
        public float $weight,
        public string $color,
        public ?int $ownerId,
        public ?string $ownerName,
        public array $specialNeeds,
        public string $sound,
        public string $fullDescription,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
        // Propriétés spécifiques selon le type
        public ?array $specificData = null,
    ) {
    }

    /**
     * Factory method pour créer le DTO depuis une entité
     * Démontre le pattern Factory
     */
    public static function fromEntity(Animal $animal): self
    {
        $owner = $animal->getOwner();
        
        // Récupération des données spécifiques selon le type
        $specificData = self::extractSpecificData($animal);

        return new self(
            id: $animal->getId(),
            type: $animal->getType(),
            name: $animal->getName(),
            birthDate: $animal->getBirthDate(),
            age: $animal->calculateAge(),
            weight: $animal->getWeight(),
            color: $animal->getColor(),
            ownerId: $owner?->getId(),
            ownerName: $owner?->getFullName(),
            specialNeeds: $animal->getSpecialNeeds(),
            sound: $animal->makeSound(),
            fullDescription: $animal->getFullDescription(),
            createdAt: $animal->getCreatedAt(),
            updatedAt: $animal->getUpdatedAt(),
            specificData: $specificData,
        );
    }

    /**
     * Extraction des données spécifiques selon le type d'animal
     * Démontre le polymorphisme
     */
    private static function extractSpecificData(Animal $animal): array
    {
        return match (true) {
            $animal instanceof Dog => [
                'breed' => $animal->getBreed(),
                'isDangerous' => $animal->isDangerous(),
                'registrationNumber' => $animal->getRegistrationNumber(),
            ],
            $animal instanceof Cat => [
                'isIndoor' => $animal->isIndoor(),
                'isHypoallergenic' => $animal->isHypoallergenic(),
            ],
            $animal instanceof Bird => [
                'species' => $animal->getSpecies(),
                'wingSpan' => $animal->getWingSpan(),
                'canTalk' => $animal->canTalk(),
            ],
            default => [],
        };
    }

    /**
     * Conversion en tableau pour JSON
     * Implémentation de JsonSerializable
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'birthDate' => $this->birthDate->format('c'), // ISO 8601
            'age' => $this->age,
            'weight' => $this->weight,
            'color' => $this->color,
            'owner' => $this->ownerId ? [
                'id' => $this->ownerId,
                'name' => $this->ownerName,
            ] : null,
            'specialNeeds' => $this->specialNeeds,
            'sound' => $this->sound,
            'fullDescription' => $this->fullDescription,
            'specificData' => $this->specificData,
            'createdAt' => $this->createdAt->format('c'),
            'updatedAt' => $this->updatedAt->format('c'),
        ];
    }

    /**
     * Conversion en tableau simple (pour exports, etc.)
     */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    /**
     * Méthode helper pour obtenir le nom complet avec type
     */
    public function getDisplayName(): string
    {
        return sprintf('%s "%s"', $this->type, $this->name);
    }

    /**
     * Vérifie si l'animal a un propriétaire
     */
    public function hasOwner(): bool
    {
        return $this->ownerId !== null;
    }

    /**
     * Vérifie si l'animal est senior (>= 7 ans)
     */
    public function isSenior(): bool
    {
        return $this->age >= 7;
    }

    /**
     * Retourne une version simplifiée (pour les listes)
     */
    public function toSummary(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'age' => $this->age,
            'owner' => $this->ownerName,
        ];
    }
}