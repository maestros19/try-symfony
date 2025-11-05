<?php

declare(strict_types=1);

namespace App\Presentation\Request;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Request object pour la création d'un animal
 * Démontre la validation avec Symfony Validator
 */
final readonly class CreateAnimalRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le type est obligatoire')]
        #[Assert\Choice(
            choices: ['Dog', 'Cat', 'Bird', 'Chien', 'Chat', 'Oiseau'],
            message: 'Type invalide. Types acceptés: Dog, Cat, Bird'
        )]
        public string $type,

        #[Assert\NotBlank(message: 'Le nom est obligatoire')]
        #[Assert\Length(
            min: 2,
            max: 100,
            minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
            maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
        )]
        public string $name,

        #[Assert\NotNull(message: 'La date de naissance est obligatoire')]
        #[Assert\LessThanOrEqual(
            'today',
            message: 'La date de naissance ne peut pas être dans le futur'
        )]
        public DateTimeImmutable $birthDate,

        #[Assert\NotNull(message: 'Le poids est obligatoire')]
        #[Assert\Positive(message: 'Le poids doit être positif')]
        #[Assert\Range(
            min: 0.01,
            max: 500,
            notInRangeMessage: 'Le poids doit être entre {{ min }} et {{ max }} kg'
        )]
        public float $weight,

        #[Assert\NotBlank(message: 'La couleur est obligatoire')]
        #[Assert\Length(max: 50)]
        public string $color,

        #[Assert\Positive]
        public ?int $ownerId = null,

        // Dog specific
        #[Assert\Length(max: 100)]
        public ?string $breed = null,

        public ?bool $isDangerous = null,

        // Cat specific
        public ?bool $isIndoor = null,

        public ?bool $isHypoallergenic = null,

        // Bird specific
        #[Assert\Length(max: 100)]
        public ?string $species = null,

        #[Assert\Positive]
        public ?float $wingSpan = null,

        public ?bool $canTalk = null,
    ) {
    }
}