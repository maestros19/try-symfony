<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\InvalidAnimalDataException;
use DateTimeImmutable;

/**
 * Classe Dog - Représente un chien
 * 
 * Démontre:
 * - Héritage de la classe abstraite Animal
 * - Polymorphisme (makeSound varie selon la taille)
 * - Logique métier riche et encapsulation
 */
class Dog extends Animal
{
    private string $breed;
    private bool $isDangerous;
    private ?string $registrationNumber = null;

    // Races considérées comme dangereuses en France (catégories 1 et 2)
    private const DANGEROUS_BREEDS = [
        'Pitbull',
        'American Staffordshire Terrier',
        'Rottweiler',
        'Tosa',
        'Mastiff',
    ];

    public function __construct(
        string $name,
        DateTimeImmutable $birthDate,
        float $weight,
        string $color,
        string $breed,
        bool $isDangerous = false,
        Owner $owner,
    ) {
        parent::__construct($name, $birthDate, $weight, $color, $owner);
        
        $this->validateBreed($breed);
        $this->breed = $breed;
        $this->isDangerous = $isDangerous || $this->isBreedDangerous($breed);
    }

    /**
     * Implémentation polymorphique de makeSound()
     * Le son varie selon la taille du chien
     */
    public function makeSound(): string
    {
        return $this->weight > 20 ? "WOOF WOOF!" : "Woof woof!";
    }

    /**
     * Retourne le type d'animal
     */
    public function getType(): string
    {
        return 'Chien';
    }

    /**
     * Polymorphisme - Surcharge des besoins spéciaux
     * Les besoins varient fortement selon la race et la catégorisation
     */
    public function getSpecialNeeds(): array
    {
        $needs = parent::getSpecialNeeds();
        
        // Besoins communs à tous les chiens
        $needs[] = 'Promenade quotidienne minimum 30 minutes';
        $needs[] = 'Éducation et socialisation';
        $needs[] = 'Alimentation adaptée à la race et à l\'âge';
        
        // Besoins selon la taille
        if ($this->isLargeBreed()) {
            $needs[] = 'Surveillance de la dysplasie de la hanche';
            $needs[] = 'Alimentation spéciale grande race';
            $needs[] = 'Espace de vie suffisant';
        }
        
        // Besoins pour chiens dangereux (obligations légales)
        if ($this->isDangerous) {
            $needs[] = 'Port de la muselière obligatoire en public';
            $needs[] = 'Tenue en laisse obligatoire (maximum 1,50m)';
            $needs[] = 'Assurance responsabilité civile spécifique';
            $needs[] = 'Permis de détention obligatoire';
            $needs[] = 'Évaluation comportementale';
            $needs[] = 'Interdiction dans certains lieux publics';
        }
        
        // Besoins selon l'âge
        if ($this->calculateAge() >= 7) {
            $needs[] = 'Contrôle vétérinaire bi-annuel (chien senior)';
            $needs[] = 'Surveillance de l\'arthrose';
            $needs[] = 'Adaptation de l\'exercice physique';
        }

        // Besoins selon la race (exemples)
        $needs = array_merge($needs, $this->getBreedSpecificNeeds());

        return $needs;
    }

    /**
     * Besoins spécifiques selon la race
     */
    private function getBreedSpecificNeeds(): array
    {
        return match (strtolower($this->breed)) {
            'berger allemand' => [
                'Exercice intense quotidien',
                'Stimulation mentale importante',
            ],
            'husky', 'malamute' => [
                'Exercice très intense (course)',
                'Climat frais préférable',
            ],
            'bulldog', 'carlin' => [
                'Surveillance respiratoire',
                'Éviter la surchauffe',
            ],
            'border collie' => [
                'Stimulation mentale intensive',
                'Travail ou sport canin recommandé',
            ],
            default => []
        };
    }

    /**
     * Définit le numéro d'identification (tatouage ou puce)
     */
    public function setRegistrationNumber(string $number): void
    {
        $this->validateRegistrationNumber($number);
        $this->registrationNumber = $number;
        $this->touch();
    }

    /**
     * Vérifie si la race est considérée comme dangereuse
     */
    private function isBreedDangerous(string $breed): bool
    {
        foreach (self::DANGEROUS_BREEDS as $dangerousBreed) {
            if (stripos($breed, $dangerousBreed) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Vérifie si c'est une grande race (> 25kg adulte)
     */
    public function isLargeBreed(): bool
    {
        // Estimation: si le chien pèse > 25kg ou certaines races
        if ($this->getWeight() > 25) {
            return true;
        }

        $largeBreeds = [
            'Berger Allemand', 'Labrador', 'Golden Retriever',
            'Rottweiler', 'Doberman', 'Boxer', 'Dogue',
        ];

        foreach ($largeBreeds as $largeBreed) {
            if (stripos($this->breed, $largeBreed) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calcule les besoins alimentaires quotidiens
     */
    public function getDailyFoodRequirement(): array
    {
        // Formule simple: ~2-3% du poids corporel par jour
        $percentage = $this->calculateAge() >= 7 ? 0.02 : 0.025;
        $foodInKg = $this->getWeight() * $percentage;
        
        return [
            'dailyAmount' => round($foodInKg * 1000), // en grammes
            'unit' => 'grammes',
            'meals' => $this->getWeight() > 20 ? 2 : 1,
            'note' => 'Adapter selon l\'activité physique',
        ];
    }

    /**
     * Retourne la durée minimale d'exercice quotidien
     */
    public function getMinimumExerciseDuration(): int
    {
        if ($this->calculateAge() >= 7) {
            return 30; // Seniors: 30 minutes
        }

        return match (true) {
            $this->isLargeBreed() => 60,
            $this->getWeight() > 10 => 45,
            default => 30,
        };
    }

    /**
     * Vérifie si le chien nécessite une déclaration en mairie
     */
    public function requiresMunicipalDeclaration(): bool
    {
        return $this->isDangerous;
    }

    /**
     * Estime le coût annuel d'entretien
     */
    public function getEstimatedAnnualCost(): array
    {
        $veterinary = 200;
        $food = $this->getWeight() * 50; // ~50€/kg/an
        $insurance = $this->isDangerous ? 500 : 150;
        $grooming = $this->isLargeBreed() ? 300 : 150;
        $miscellaneous = 200;

        $total = $veterinary + $food + $insurance + $grooming + $miscellaneous;

        return [
            'veterinary' => $veterinary,
            'food' => round($food),
            'insurance' => $insurance,
            'grooming' => $grooming,
            'miscellaneous' => $miscellaneous,
            'total' => round($total),
            'currency' => 'EUR',
        ];
    }

    /**
     * Validation de la race
     */
    private function validateBreed(string $breed): void
    {
        if (empty(trim($breed))) {
            throw InvalidAnimalDataException::emptyField('breed');
        }

        if (strlen($breed) > 100) {
            throw InvalidAnimalDataException::fieldTooLong('breed', $breed);
        }
    }

    /**
     * Validation du numéro d'identification
     */
    private function validateRegistrationNumber(string $number): void
    {
        // Format français: 3 lettres + 12 chiffres ou 15 chiffres
        if (!preg_match('/^[A-Z]{3}\d{12}$|^\d{15}$/', $number)) {
            throw new InvalidAnimalDataException(
                'Format de numéro d\'identification invalide'
            );
        }
    }

    // ===== GETTERS =====

    public function getBreed(): string
    {
        return $this->breed;
    }

    public function isDangerous(): bool
    {
        return $this->isDangerous;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    /**
     * Retourne la catégorie (si dangereux)
     */
    public function getDangerCategory(): ?int
    {
        if (!$this->isDangerous) {
            return null;
        }

        // Catégorie 1: races d'attaque
        $category1 = ['Pitbull', 'Tosa', 'Mastiff'];
        foreach ($category1 as $breed) {
            if (stripos($this->breed, $breed) !== false) {
                return 1;
            }
        }

        // Catégorie 2: races de garde et de défense
        return 2;
    }

    /**
     * Retourne une description détaillée
     */
    public function getDetailedDescription(): string
    {
        $sizeType = $this->isLargeBreed() ? 'grande race' : 'petite/moyenne race';
        $dangerStatus = $this->isDangerous ? ', catégorisé dangereux' : '';
        $regNumber = $this->registrationNumber ? ', identifié' : ', non identifié';
        
        return sprintf(
            'Chien %s de race %s, %.1f kg, %d an(s)%s%s',
            $sizeType,
            $this->breed,
            $this->getWeight(),
            $this->calculateAge(),
            $dangerStatus,
            $regNumber
        );
    }

    /**
     * Vérifie si le chien est correctement identifié
     */
    public function isProperlyIdentified(): bool
    {
        return $this->registrationNumber !== null;
    }

    /**
     * Vérifie si le chien est senior
     */
    public function isSenior(): bool
    {
        return $this->calculateAge() >= 7;
    }
}