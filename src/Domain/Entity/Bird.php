<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\InvalidAnimalDataException;
use DateTimeImmutable;

/**
 * Classe Bird - Représente un oiseau
 * 
 * Démontre:
 * - Héritage de la classe abstraite Animal
 * - Polymorphisme (makeSound, getSpecialNeeds)
 * - Encapsulation des données spécifiques
 */
class Bird extends Animal
{
    private string $species;
    private bool $canTalk;
    private float $wingSpan;

    public function __construct(
        string $name,
        DateTimeImmutable $birthDate,
        float $weight,
        string $color,
        string $species,
        float $wingSpan,
        bool $canTalk = false,
        Owner $owner,
    ) {
        parent::__construct($name, $birthDate, $weight, $color, $owner);
        
        $this->validateSpecies($species);
        $this->validateWingSpan($wingSpan);
        
        $this->species = $species;
        $this->wingSpan = $wingSpan;
        $this->canTalk = $canTalk;
    }

    /**
     * Implémentation polymorphique de makeSound()
     * Le son varie selon l'espèce et la capacité à parler
     */
    public function makeSound(): string
    {
        // Si l'oiseau peut parler, il dit quelque chose
        if ($this->canTalk) {
            return sprintf("Hello! %s veut un cracker!", $this->getName());
        }
        
        // Sinon, son selon l'espèce
        return match (strtolower($this->species)) {
            'perroquet', 'parrot' => 'Squawk!',
            'canari', 'canary' => 'Cui cui!',
            'perruche', 'parakeet' => 'Chirp chirp!',
            'cacatoès', 'cockatoo' => 'Screech!',
            'ara', 'macaw' => 'Awk awk!',
            'colombe', 'dove' => 'Coo coo!',
            default => 'Tweet tweet!'
        };
    }

    /**
     * Retourne le type d'animal
     */
    public function getType(): string
    {
        return 'Oiseau';
    }

    /**
     * Polymorphisme - Surcharge des besoins spéciaux
     * Les oiseaux ont des besoins très spécifiques
     */
    public function getSpecialNeeds(): array
    {
        $needs = parent::getSpecialNeeds();
        
        // Besoins de base pour tous les oiseaux
        $minCageSize = $this->wingSpan * 3;
        $needs[] = sprintf('Cage spacieuse (minimum %.0f cm)', $minCageSize);
        $needs[] = 'Exercice quotidien hors cage (minimum 2h)';
        $needs[] = 'Alimentation variée avec graines et fruits frais';
        $needs[] = 'Température stable (18-25°C)';
        $needs[] = 'Éviter les courants d\'air';
        
        // Besoins supplémentaires pour oiseaux parleurs
        if ($this->canTalk) {
            $needs[] = 'Stimulation mentale et sociale quotidienne';
            $needs[] = 'Interaction humaine régulière (minimum 1h/jour)';
            $needs[] = 'Jouets interactifs pour éviter l\'ennui';
            $needs[] = 'Apprentissage et répétition de mots';
        }
        
        // Besoins selon l'espèce
        $needs = array_merge($needs, $this->getSpeciesSpecificNeeds());

        return $needs;
    }

    /**
     * Besoins spécifiques selon l'espèce
     */
    private function getSpeciesSpecificNeeds(): array
    {
        return match (strtolower($this->species)) {
            'perroquet', 'ara' => [
                'Attention particulière au bec (risque de blessures)',
                'Surveillance du plumage',
                'Bain ou douche régulière',
            ],
            'canari' => [
                'Lumière naturelle importante',
                'Chant stimulé par d\'autres canaris',
            ],
            'perruche' => [
                'Compagnie recommandée (vie en groupe)',
                'Accessoires pour grimper',
            ],
            default => []
        };
    }

    /**
     * Calcule la taille de cage recommandée
     */
    public function getRecommendedCageSize(): array
    {
        $multiplier = $this->canTalk ? 4 : 3;
        $size = $this->wingSpan * $multiplier;

        return [
            'width' => $size,
            'height' => $size * 1.5,
            'depth' => $size * 0.8,
            'unit' => 'cm'
        ];
    }

    /**
     * Vérifie si l'oiseau est de grande taille
     */
    public function isLargeBird(): bool
    {
        return $this->wingSpan > 50.0; // Plus de 50cm d'envergure
    }

    /**
     * Estime l'espérance de vie selon l'espèce
     */
    public function getEstimatedLifespan(): int
    {
        return match (strtolower($this->species)) {
            'perroquet' => 60,
            'ara' => 50,
            'cacatoès' => 40,
            'canari' => 10,
            'perruche' => 15,
            'colombe' => 12,
            default => 10
        };
    }

    /**
     * Validation de l'espèce
     */
    private function validateSpecies(string $species): void
    {
        if (empty(trim($species))) {
            throw InvalidAnimalDataException::emptyField('species');
        }

        if (strlen($species) > 100) {
            throw InvalidAnimalDataException::fieldTooLong('species', $species);
        }
    }

    /**
     * Validation de l'envergure
     */
    private function validateWingSpan(float $wingSpan): void
    {
        if ($wingSpan <= 0) {
            throw new InvalidAnimalDataException(
                sprintf('L\'envergure doit être positive (reçu: %.2f)', $wingSpan)
            );
        }

        if ($wingSpan > 300) { // Maximum 3 mètres (condor)
            throw new InvalidAnimalDataException(
                sprintf('L\'envergure semble trop grande (reçu: %.2f cm)', $wingSpan)
            );
        }
    }

    // ===== GETTERS =====

    public function getSpecies(): string
    {
        return $this->species;
    }

    public function canTalk(): bool
    {
        return $this->canTalk;
    }

    public function getWingSpan(): float
    {
        return $this->wingSpan;
    }

    /**
     * Retourne une description détaillée
     */
    public function getDetailedDescription(): string
    {
        $talkingStatus = $this->canTalk ? 'parleur' : 'non parleur';
        $size = $this->isLargeBird() ? 'grand' : 'petit';

        return sprintf(
            '%s de type %s, %s (%s), envergure de %.1f cm, %d an(s)',
            $this->getType(),
            $this->species,
            $talkingStatus,
            $size,
            $this->wingSpan,
            $this->calculateAge()
        );
    }
}