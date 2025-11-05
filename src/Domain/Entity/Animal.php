<?php

namespace App\Domain\Entity;

use App\Infrastructure\Persistence\Repository\AnimalRepository;
use Doctrine\ORM\Mapping as ORM;

use App\Domain\Exception\InvalidAnimalDataException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Classe abstraite Animal - Démontre l'héritage et le polymorphisme
 * Utilise le principe OCP (Open/Closed Principle)
 */
#[ORM\Entity(repositoryClass: AnimalRepository::class)]
abstract class Animal
{

    /**
     * ########################################
     * 
     * Attributes
     * 
     * ########################################
     */

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 2255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTime $birthDate = null;

    #[ORM\Column]
    private ?float $weight = null;
    

    #[ORM\Column(length: 30)]
    private ?string $color = null;

    #[ORM\ManyToOne(targetEntity: Owner::class, inversedBy: 'animals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Owner $owner = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    
    /**
     * ########################################
     * 
     * Constructors
     * 
     * ########################################
     */


    public function __construct(
        string $name,
        DateTimeImmutable $birthDate,
        float $weight,
        string $color
    ) {
        $this->validateName($name);
        $this->validateWeight($weight);
        $this->validateBirthDate($birthDate);
        
        $this->name = trim($name);
        $this->birthDate = $birthDate;
        $this->weight = $weight;
        $this->color = trim($color);
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }


    /**
     * ########################################
     * 
     * Getters &  Setters
     * 
     * ########################################
     */


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTime $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * ####################################################################
     * 
     *   MÉTHODES ABSTRAITES (obligatoires dans les sous-classes)
     * 
     * ####################################################################
     */


    /**
     * Retourne le son que fait l'animal
     * Doit être implémenté dans chaque sous-classe
     */
    abstract public function makeSound(): string;
    
    /**
     * Retourne le type d'animal (Chien, Chat, Oiseau, etc.)
     * Doit être implémenté dans chaque sous-classe
     */
    abstract public function getType(): string;


    /**
     * ####################################################################
     * 
     *  MÉTHODES POLYMORPHIQUES (peuvent être surchargées)
     * 
     * ####################################################################
     */
    
    /**
     * Retourne les besoins spéciaux de l'animal
     * Méthode de base, peut être surchargée dans les sous-classes
     */
    public function getSpecialNeeds(): array
    {
        return [
            'Vaccination annuelle',
            'Contrôle vétérinaire régulier',
        ];
    }



    /**
     * ####################################################################
     * 
     *  GESTION DU PROPRIÉTAIRE (Relations bidirectionnelles)
     * 
     * ####################################################################
     */


    /**
     * Retourne le propriétaire actuel
     */
    public function getOwner(): ?Owner
    {
        return $this->owner;
    }

    /**
     * Assigne un nouveau propriétaire à l'animal
     * Gère la synchronisation bidirectionnelle
     */
    public function assignOwner(Owner $owner): void
    {
        // Si l'animal a déjà un propriétaire différent, on le retire d'abord
        if ($this->owner !== null && $this->owner !== $owner) {
            $this->owner->removeAnimal($this);
        }
        
        $this->owner = $owner;
        
        // Synchronisation bidirectionnelle
        if (!$owner->getAnimals()->contains($this)) {
            $owner->addAnimal($this);
        }
        
        $this->touch();
    }

    /**
     * Retire le propriétaire
     */
    public function removeOwner(): void
    {
        if ($this->owner !== null) {
            $oldOwner = $this->owner;
            $this->owner = null;
            $oldOwner->removeAnimal($this);
            $this->touch();
        }
    }

    /**
     * Vérifie si l'animal a un propriétaire
     */
    public function hasOwner(): bool
    {
        return $this->owner !== null;
    }

    // ===== CALCUL DE L'ÂGE =====

    /**
     * Calcule l'âge de l'animal en années
     */
    public function calculateAge(): int
    {
        $now = new DateTimeImmutable();
        $age = $now->diff($this->birthDate);
        return $age->y;
    }

    /**
     * Calcule l'âge en mois
     */
    public function calculateAgeInMonths(): int
    {
        $now = new DateTimeImmutable();
        $diff = $now->diff($this->birthDate);
        return ($diff->y * 12) + $diff->m;
    }

    /**
     * Calcule l'âge en jours
     */
    public function calculateAgeInDays(): int
    {
        $now = new DateTimeImmutable();
        return $now->diff($this->birthDate)->days;
    }

    /**
     * Retourne l'âge formaté (ex: "5 ans 3 mois")
     */
    public function getFormattedAge(): string
    {
        $diff = (new DateTimeImmutable())->diff($this->birthDate);
        
        $parts = [];
        
        if ($diff->y > 0) {
            $parts[] = $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
        }
        
        if ($diff->m > 0) {
            $parts[] = $diff->m . ' mois';
        }
        
        if (empty($parts) && $diff->d > 0) {
            $parts[] = $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
        }
        
        return empty($parts) ? 'Nouveau-né' : implode(' ', $parts);
    }

    /**
     * Vérifie si l'animal est un bébé (< 1 an)
     */
    public function isBaby(): bool
    {
        return $this->calculateAge() < 1;
    }

    /**
     * Vérifie si l'animal est senior (>= 7 ans)
     */
    public function isSenior(): bool
    {
        return $this->calculateAge() >= 7;
    }

    /**
     * Retourne la phase de vie
     */
    public function getLifeStage(): string
    {
        $age = $this->calculateAge();
        
        return match (true) {
            $age < 1 => 'Bébé',
            $age < 3 => 'Junior',
            $age < 7 => 'Adulte',
            $age < 10 => 'Senior',
            default => 'Gériatrique',
        };
    }


    // ===== GESTION DU POIDS =====

    /**
     * Met à jour le poids de l'animal
     */
    public function updateWeight(float $weight): void
    {
        $this->validateWeight($weight);
        $oldWeight = $this->weight;
        $this->weight = $weight;
        $this->touch();
        
        // Pourrait déclencher un événement si changement significatif
        if (abs($oldWeight - $weight) > ($oldWeight * 0.1)) {
            // Event: SignificantWeightChange
        }
    }

    /**
     * Vérifie si le poids a changé significativement (> 10%)
     */
    public function hasSignificantWeightChange(float $previousWeight): bool
    {
        $change = abs($this->weight - $previousWeight);
        $percentageChange = ($change / $previousWeight) * 100;
        
        return $percentageChange > 10;
    }

    // ===== MÉTHODES D'INFORMATION =====

    /**
     * Retourne une description complète de l'animal
     */
    public function getFullDescription(): string
    {
        return sprintf(
            '%s nommé(e) "%s", %d an(s), %.2f kg, couleur: %s',
            $this->getType(),
            $this->name,
            $this->calculateAge(),
            $this->weight,
            $this->color
        );
    }

    /**
     * Retourne un résumé court
     */
    public function getSummary(): string
    {
        return sprintf(
            '%s "%s" (%d an%s)',
            $this->getType(),
            $this->name,
            $this->calculateAge(),
            $this->calculateAge() > 1 ? 's' : ''
        );
    }

    /**
     * Retourne les statistiques de l'animal
     */
    public function getStatistics(): array
    {
        return [
            'age' => $this->calculateAge(),
            'ageInMonths' => $this->calculateAgeInMonths(),
            'ageInDays' => $this->calculateAgeInDays(),
            'lifeStage' => $this->getLifeStage(),
            'weight' => $this->weight,
            'hasOwner' => $this->hasOwner(),
        ];
    }

    // ===== VALIDATION =====

    /**
     * Validation du nom
     */
    private function validateName(string $name): void
    {
        $trimmed = trim($name);
        
        if (empty($trimmed)) {
            throw InvalidAnimalDataException::emptyName();
        }

        if (strlen($trimmed) < 2) {
            throw new InvalidAnimalDataException(
                'Le nom doit contenir au moins 2 caractères'
            );
        }

        if (strlen($trimmed) > 100) {
            throw InvalidAnimalDataException::nameTooLong($name);
        }
    }

    /**
     * Validation du poids
     */
    private function validateWeight(float $weight): void
    {
        if ($weight <= 0) {
            throw InvalidAnimalDataException::invalidWeight($weight);
        }

        if ($weight > 500) {
            throw new InvalidAnimalDataException(
                sprintf('Le poids semble trop élevé: %.2f kg', $weight)
            );
        }
    }

    /**
     * Validation de la date de naissance
     */
    private function validateBirthDate(DateTimeImmutable $birthDate): void
    {
        $now = new DateTimeImmutable();
        
        if ($birthDate > $now) {
            throw new InvalidAnimalDataException(
                'La date de naissance ne peut pas être dans le futur'
            );
        }

        // Limite à 50 ans (animal le plus vieux connu)
        $fiftyYearsAgo = $now->modify('-50 years');
        if ($birthDate < $fiftyYearsAgo) {
            throw new InvalidAnimalDataException(
                'La date de naissance semble trop ancienne'
            );
        }
    }

    /**
     * Met à jour la date de modification
     */
    protected function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
    
    // ===== MÉTHODES UTILITAIRES =====

    /**
     * Compare deux animaux
     */
    public function equals(Animal $other): bool
    {
        if ($this->id !== null && $other->id !== null) {
            return $this->id === $other->id;
        }
        
        return $this === $other;
    }

    /**
     * Conversion en string
     */
    public function __toString(): string
    {
        return $this->getSummary();
    }
}
