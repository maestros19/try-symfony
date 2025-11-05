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
            choices: ['dog', 'cat', 'bird'], // ✅ Uniquement les valeurs attendues par Doctrine
            message: 'Type invalide. Types acceptés: dog, cat, bird'
        )]
        public string $type,

        #[Assert\NotBlank(message: 'Le nom est obligatoire')]
        #[Assert\Length(
            min: 2,
            max: 2255, // ✅ Correspond à l'entité
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
        #[Assert\Length(max: 30)] // ✅ Correspond à l'entité
        public string $color,

        #[Assert\NotNull(message: 'Le propriétaire est obligatoire')]
        #[Assert\Positive(message: 'L\'ID du propriétaire doit être positif')]
        public int $ownerId, // ✅ Non nullable car requis par l'entité

        // Dog specific
        #[Assert\When(
            expression: 'this.type === "dog"',
            constraints: [
                new Assert\NotBlank(message: 'La race est obligatoire pour un chien'),
                new Assert\Length(max: 100)
            ]
        )]
        public ?string $breed = null,

        public ?bool $isDangerous = null,

        // Cat specific
        public ?bool $isIndoor = null,

        public ?bool $isHypoallergenic = null,

        // Bird specific
        #[Assert\When(
            expression: 'this.type === "bird"',
            constraints: [
                new Assert\NotBlank(message: 'L\'espèce est obligatoire pour un oiseau'),
                new Assert\Length(max: 100)
            ]
        )]
        public ?string $species = null,

        #[Assert\When(
            expression: 'this.type === "bird"',
            constraints: [
                new Assert\NotNull(message: 'L\'envergure est obligatoire pour un oiseau'),
                new Assert\Positive(message: 'L\'envergure doit être positive')
            ]
        )]
        public ?float $wingSpan = null,

        public ?bool $canTalk = null,
    ) {
    }
}