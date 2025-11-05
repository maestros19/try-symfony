<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\InvalidOwnerDataException;
use App\Domain\ValueObject\Address;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PhoneNumber;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Entité Owner - Représente un propriétaire d'animaux
 * 
 * Démontre:
 * - Utilisation de Value Objects (Email, PhoneNumber, Address)
 * - Relations bidirectionnelles avec Animal
 * - Encapsulation et protection des données
 * - Logique métier riche
 */
#[ORM\Entity(repositoryClass: AnimalRepository::class)]
class Owner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $firstName;

    #[ORM\Column(length: 100)]
    private string $lastName;

    #[ORM\Column(length: 255, unique: true)]
    private string $email;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column]
    private \DateTimeImmutable $registrationDate;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Animal::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $animals;


    public function __construct(
        string $firstName,
        string $lastName,
        Email $email,
        PhoneNumber $phoneNumber,
        Address $address
    ) {
        $this->validateName($firstName, 'firstName');
        $this->validateName($lastName, 'lastName');
        
        $this->firstName = ucfirst(trim($firstName));
        $this->lastName = strtoupper(trim($lastName));
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->address = $address;
        $this->animals = new ArrayCollection();
        $this->registrationDate = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Ajoute un animal à la collection
     * Gère la relation bidirectionnelle
     */
    public function addAnimal(Animal $animal): void
    {
        if (!$this->animals->contains($animal)) {
            $this->animals->add($animal);
            
            // Synchronisation bidirectionnelle
            if ($animal->getOwner() !== $this) {
                $animal->assignOwner($this);
            }
            
            $this->touch();
        }
    }

    /**
     * Retire un animal de la collection
     */
    public function removeAnimal(Animal $animal): void
    {
        if ($this->animals->contains($animal)) {
            $this->animals->removeElement($animal);
            $this->touch();
        }
    }

    /**
     * Met à jour les informations de contact
     */
    public function updateContactInfo(Email $email, PhoneNumber $phoneNumber): void
    {
        $hasChanged = false;

        if (!$this->email->equals($email)) {
            $this->email = $email;
            $hasChanged = true;
        }

        if (!$this->phoneNumber->equals($phoneNumber)) {
            $this->phoneNumber = $phoneNumber;
            $hasChanged = true;
        }

        if ($hasChanged) {
            $this->touch();
        }
    }

    /**
     * Met à jour l'adresse
     */
    public function updateAddress(Address $address): void
    {
        if (!$this->address->equals($address)) {
            $this->address = $address;
            $this->touch();
        }
    }

    /**
     * Met à jour le nom
     */
    public function updateName(string $firstName, string $lastName): void
    {
        $this->validateName($firstName, 'firstName');
        $this->validateName($lastName, 'lastName');

        $this->firstName = ucfirst(trim($firstName));
        $this->lastName = strtoupper(trim($lastName));
        $this->touch();
    }

    /**
     * Désactive le compte
     */
    public function deactivate(): void
    {
        if ($this->isActive) {
            $this->isActive = false;
            $this->touch();
            
            // Pourrait déclencher un Domain Event
            // Event: OwnerDeactivated
        }
    }

    /**
     * Réactive le compte
     */
    public function activate(): void
    {
        if (!$this->isActive) {
            $this->isActive = true;
            $this->touch();
            
            // Event: OwnerActivated
        }
    }

    /**
     * Récupère les animaux par type
     * 
     * @return Animal[]
     */
    public function getAnimalsByType(string $type): array
    {
        return $this->animals->filter(
            fn(Animal $animal) => $animal->getType() === $type
        )->toArray();
    }

    /**
     * Compte les animaux par type
     * 
     * @return array<string, int>
     */
    public function countAnimalsByType(): array
    {
        $counts = [];
        
        foreach ($this->animals as $animal) {
            $type = $animal->getType();
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * Retourne le nombre total d'animaux
     */
    public function getTotalAnimals(): int
    {
        return $this->animals->count();
    }

    /**
     * Vérifie si le propriétaire possède des animaux
     */
    public function hasAnimals(): bool
    {
        return !$this->animals->isEmpty();
    }

    /**
     * Vérifie si le propriétaire a atteint la limite légale d'animaux
     * (exemple: 5 chiens maximum en France)
     */
    public function hasReachedDogLimit(): bool
    {
        $dogs = $this->getAnimalsByType('Chien');
        return count($dogs) >= 5;
    }

    /**
     * Vérifie si le propriétaire possède des chiens dangereux
     */
    public function ownsDangerousDogs(): bool
    {
        $dogs = $this->getAnimalsByType('Chien');
        
        foreach ($dogs as $dog) {
            if ($dog instanceof Dog && $dog->isDangerous()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calcule l'âge moyen des animaux
     */
    public function getAverageAnimalAge(): float
    {
        if ($this->animals->isEmpty()) {
            return 0.0;
        }

        $totalAge = 0;
        foreach ($this->animals as $animal) {
            $totalAge += $animal->calculateAge();
        }

        return round($totalAge / $this->animals->count(), 1);
    }

    /**
     * Retourne les animaux seniors (>= 7 ans)
     * 
     * @return Animal[]
     */
    public function getSeniorAnimals(): array
    {
        return $this->animals->filter(
            fn(Animal $animal) => $animal->calculateAge() >= 7
        )->toArray();
    }

    /**
     * Estime le coût annuel total pour tous les animaux
     */
    public function getEstimatedTotalAnnualCost(): array
    {
        $totalCost = 0;
        $breakdown = [];

        foreach ($this->animals as $animal) {
            if ($animal instanceof Dog) {
                $cost = $animal->getEstimatedAnnualCost();
                $totalCost += $cost['total'];
                $breakdown[$animal->getName()] = $cost['total'];
            } else {
                // Estimation générique pour autres animaux
                $baseCost = 500;
                $totalCost += $baseCost;
                $breakdown[$animal->getName()] = $baseCost;
            }
        }

        return [
            'total' => round($totalCost),
            'breakdown' => $breakdown,
            'currency' => 'EUR',
            'note' => 'Estimation basée sur les coûts moyens',
        ];
    }

    /**
     * Retourne le nom complet
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Retourne les initiales
     */
    public function getInitials(): string
    {
        return strtoupper(
            substr($this->firstName, 0, 1) . substr($this->lastName, 0, 1)
        );
    }

    /**
     * Calcule la durée d'inscription en jours
     */
    public function getMembershipDurationInDays(): int
    {
        $now = new DateTimeImmutable();
        return $now->diff($this->registrationDate)->days;
    }

    /**
     * Vérifie si c'est un nouveau membre (moins de 30 jours)
     */
    public function isNewMember(): bool
    {
        return $this->getMembershipDurationInDays() < 30;
    }

    /**
     * Retourne un résumé du profil
     */
    public function getProfileSummary(): array
    {
        return [
            'fullName' => $this->getFullName(),
            'email' => $this->email->getValue(),
            'city' => $this->address->getCity(),
            'totalAnimals' => $this->getTotalAnimals(),
            'animalsByType' => $this->countAnimalsByType(),
            'memberSince' => $this->registrationDate->format('Y-m-d'),
            'isActive' => $this->isActive,
        ];
    }

    /**
     * Validation du nom
     */
    private function validateName(string $name, string $field): void
    {
        $trimmed = trim($name);
        
        if (empty($trimmed)) {
            throw InvalidOwnerDataException::emptyField($field);
        }

        if (strlen($trimmed) < 2) {
            throw new InvalidOwnerDataException(
                sprintf('Le champ "%s" doit contenir au moins 2 caractères', $field)
            );
        }

        if (strlen($trimmed) > 100) {
            throw InvalidOwnerDataException::fieldTooLong($field, $name);
        }

        // Validation: uniquement lettres, espaces, tirets, apostrophes
        if (!preg_match('/^[\p{L}\s\-\']+$/u', $trimmed)) {
            throw new InvalidOwnerDataException(
                sprintf('Le champ "%s" contient des caractères invalides', $field)
            );
        }
    }

    /**
     * Met à jour la date de modification
     */
    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    // ===== GETTERS =====

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPhoneNumber(): PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function getAnimals(): Collection
    {
        return $this->animals;
    }

    public function getRegistrationDate(): DateTimeImmutable
    {
        return $this->registrationDate;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}