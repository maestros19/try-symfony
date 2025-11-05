<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;

/**
 * Classe Cat - Représente un chat
 * 
 * Démontre:
 * - Héritage de la classe abstraite Animal
 * - Polymorphisme (makeSound, getSpecialNeeds)
 * - Logique métier spécifique aux chats
 */
class Cat extends Animal
{
    private bool $isIndoor;
    private bool $isHypoallergenic;

    public function __construct(
        string $name,
        DateTimeImmutable $birthDate,
        float $weight,
        string $color,
        bool $isIndoor = true,
        bool $isHypoallergenic = false
    ) {
        parent::__construct($name, $birthDate, $weight, $color);
        $this->isIndoor = $isIndoor;
        $this->isHypoallergenic = $isHypoallergenic;
    }

    /**
     * Implémentation polymorphique de makeSound()
     * Les chats miaulent tous de la même façon
     */
    public function makeSound(): string
    {
        return "Miaou!";
    }

    /**
     * Retourne le type d'animal
     */
    public function getType(): string
    {
        return 'Chat';
    }

    /**
     * Polymorphisme - Surcharge des besoins spéciaux
     * Les besoins varient selon que le chat vit à l'intérieur ou à l'extérieur
     */
    public function getSpecialNeeds(): array
    {
        $needs = parent::getSpecialNeeds();
        
        // Besoins communs à tous les chats
        $needs[] = 'Litière à nettoyer quotidiennement';
        $needs[] = 'Griffoir pour préserver les meubles';
        $needs[] = 'Points d\'eau fraîche multiples';
        $needs[] = 'Zones de repos en hauteur';
        
        // Besoins spécifiques aux chats d'extérieur
        if (!$this->isIndoor) {
            $needs[] = 'Accès sécurisé à l\'extérieur (chatière, jardin clos)';
            $needs[] = 'Traitement anti-puces et tiques renforcé (mensuel)';
            $needs[] = 'Vaccination supplémentaire (FeLV, rage)';
            $needs[] = 'Vermifugation plus fréquente';
            $needs[] = 'Collier avec identification';
        } else {
            // Besoins spécifiques aux chats d'intérieur
            $needs[] = 'Stimulation mentale accrue (jouets interactifs)';
            $needs[] = 'Arbre à chat pour grimper';
            $needs[] = 'Sessions de jeu quotidiennes (15-30 min)';
        }

        // Besoins pour races hypoallergéniques
        if ($this->isHypoallergenic) {
            $needs[] = 'Brossage régulier pour minimiser les allergènes (2-3x/semaine)';
            $needs[] = 'Bain occasionnel (mensuel) pour réduire les protéines Fel d1';
            $needs[] = 'Nettoyage fréquent de l\'environnement';
        }

        // Besoins selon l'âge
        if ($this->calculateAge() >= 7) {
            $needs[] = 'Contrôle vétérinaire bi-annuel (chat senior)';
            $needs[] = 'Surveillance du poids et de l\'appétit';
            $needs[] = 'Alimentation adaptée aux seniors';
        }

        return $needs;
    }

    /**
     * Calcule les besoins alimentaires quotidiens (en grammes)
     * Formule: ~40-50 cal/kg de poids corporel
     */
    public function getDailyFoodRequirement(): array
    {
        $caloriesPerKg = $this->isIndoor ? 40 : 50; // Les chats d'extérieur brûlent plus
        $dailyCalories = $this->getWeight() * $caloriesPerKg;
        
        // Croquettes standard: ~350-400 cal/100g
        $dryFoodGrams = ($dailyCalories / 375) * 100;
        
        // Pâtée standard: ~80-100 cal/100g
        $wetFoodGrams = ($dailyCalories / 90) * 100;

        return [
            'dailyCalories' => round($dailyCalories),
            'dryFood' => round($dryFoodGrams),
            'wetFood' => round($wetFoodGrams),
            'unit' => 'grammes',
            'note' => $this->isIndoor ? 'Chat d\'intérieur' : 'Chat d\'extérieur'
        ];
    }

    /**
     * Vérifie si le chat est en surpoids
     * Poids moyen: 3.5-5.5 kg
     */
    public function isOverweight(): bool
    {
        return $this->getWeight() > 6.5;
    }

    /**
     * Vérifie si le chat est senior
     */
    public function isSenior(): bool
    {
        return $this->calculateAge() >= 7;
    }

    /**
     * Retourne le niveau d'activité recommandé
     */
    public function getRecommendedActivityLevel(): string
    {
        if ($this->isSenior()) {
            return 'Modéré - Privilégier des jeux calmes';
        }

        if ($this->isIndoor) {
            return 'Élevé - Compenser le manque d\'espace extérieur';
        }

        return 'Modéré - Activité naturelle à l\'extérieur';
    }

    /**
     * Estime la fréquence de visite vétérinaire
     */
    public function getVetVisitFrequency(): string
    {
        if ($this->isSenior()) {
            return 'Tous les 6 mois';
        }

        if (!$this->isIndoor) {
            return 'Tous les 6 mois (risques accrus)';
        }

        return 'Annuelle';
    }

    /**
     * Retourne des conseils de soins
     */
    public function getCareAdvice(): array
    {
        $advice = [
            'Brossage régulier pour éviter les boules de poils',
            'Contrôle des dents et des gencives',
            'Vérification des griffes',
        ];

        if ($this->isIndoor) {
            $advice[] = 'Attention à la prise de poids (sédentarité)';
            $advice[] = 'Enrichissement de l\'environnement essentiel';
        }

        if (!$this->isIndoor) {
            $advice[] = 'Inspection régulière du pelage (parasites)';
            $advice[] = 'Surveillance des blessures éventuelles';
        }

        if ($this->isHypoallergenic) {
            $advice[] = 'Maintenir une routine de toilettage stricte';
        }

        return $advice;
    }

    // ===== SETTERS AVEC LOGIQUE MÉTIER =====

    /**
     * Change le statut intérieur/extérieur avec validation
     */
    public function setIndoorStatus(bool $isIndoor): void
    {
        if ($this->isIndoor !== $isIndoor) {
            $this->isIndoor = $isIndoor;
            $this->touch();
            
            // Si passage à l'extérieur, note importante
            if (!$isIndoor) {
                // Pourrait déclencher un événement domain
                // Domain Event: CatMovedOutdoors
            }
        }
    }

    // ===== GETTERS =====

    public function isIndoor(): bool
    {
        return $this->isIndoor;
    }

    public function isHypoallergenic(): bool
    {
        return $this->isHypoallergenic;
    }

    /**
     * Retourne le statut d'habitat
     */
    public function getHabitatStatus(): string
    {
        return $this->isIndoor ? 'Intérieur' : 'Intérieur/Extérieur';
    }

    /**
     * Retourne une description détaillée
     */
    public function getDetailedDescription(): string
    {
        $habitatType = $this->isIndoor ? 'd\'intérieur' : 'd\'intérieur/extérieur';
        $allergyStatus = $this->isHypoallergenic ? ', race hypoallergénique' : '';
        $ageStatus = $this->isSenior() ? ', senior' : '';
        
        return sprintf(
            'Chat %s nommé(e) "%s", %.1f kg, %d an(s)%s%s',
            $habitatType,
            $this->getName(),
            $this->getWeight(),
            $this->calculateAge(),
            $allergyStatus,
            $ageStatus
        );
    }
}