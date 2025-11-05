<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Owner;
use App\Domain\Exception\OwnerNotFoundException;
use App\Domain\ValueObject\Email;

/**
 * Interface du repository Owner - Port Out (Hexagonal Architecture)
 * Définit le contrat que l'infrastructure doit implémenter
 * 
 * Démontre:
 * - DIP (Dependency Inversion Principle)
 * - ISP (Interface Segregation Principle)
 * - Séparation Domain/Infrastructure
 */
interface OwnerRepositoryInterface
{
    /**
     * Trouve un propriétaire par son ID
     * 
     * @throws OwnerNotFoundException Si le propriétaire n'existe pas
     */
    public function findById(int $id): Owner;

    /**
     * Trouve un propriétaire par son email (unique)
     * 
     * @throws OwnerNotFoundException Si le propriétaire n'existe pas
     */
    public function findByEmail(Email $email): Owner;

    /**
     * Récupère tous les propriétaires
     * 
     * @return Owner[]
     */
    public function findAll(): array;

    /**
     * Récupère tous les propriétaires actifs
     * 
     * @return Owner[]
     */
    public function findActiveOwners(): array;

    /**
     * Récupère les propriétaires ayant au moins X animaux
     * 
     * @param int $minCount Nombre minimum d'animaux
     * @return Owner[]
     */
    public function findOwnersWithMinAnimals(int $minCount): array;

    /**
     * Recherche avancée avec critères
     * 
     * @param array<string, mixed> $criteria Critères de recherche
     * @param array<string, string>|null $orderBy Ordre de tri
     * @param int|null $limit Limite de résultats
     * @param int|null $offset Offset pour pagination
     * @return Owner[]
     */
    public function findBy(
        array $criteria, 
        ?array $orderBy = null, 
        ?int $limit = null, 
        ?int $offset = null
    ): array;

    /**
     * Recherche par ville
     * 
     * @return Owner[]
     */
    public function findByCity(string $city): array;

    /**
     * Recherche par code postal
     * 
     * @return Owner[]
     */
    public function findByPostalCode(string $postalCode): array;

    /**
     * Persiste un propriétaire (création ou mise à jour)
     * 
     * @param Owner $owner L'entité à persister
     */
    public function save(Owner $owner): void;

    /**
     * Supprime un propriétaire
     * 
     * @param Owner $owner L'entité à supprimer
     */
    public function delete(Owner $owner): void;

    /**
     * Compte le nombre total de propriétaires
     */
    public function count(): int;

    /**
     * Compte les propriétaires actifs
     */
    public function countActive(): int;

    /**
     * Vérifie si un email existe déjà
     */
    public function existsByEmail(Email $email): bool;

    /**
     * Récupère les propriétaires avec leurs animaux (optimisé)
     * Évite le problème N+1 queries
     * 
     * @return Owner[]
     */
    public function findAllWithAnimals(): array;

    /**
     * Récupère un propriétaire avec ses animaux
     * 
     * @throws OwnerNotFoundException
     */
    public function findByIdWithAnimals(int $id): Owner;

    /**
     * Récupère les propriétaires avec statistiques
     * Retourne Owner + nombre d'animaux + poids moyen
     * 
     * @return array<array{owner: Owner, totalAnimals: int, avgWeight: float}>
     */
    public function findWithAnimalStatistics(): array;

    /**
     * Recherche par nom (partiel)
     * 
     * @return Owner[]
     */
    public function searchByName(string $searchTerm): array;

    /**
     * Récupère les propriétaires inscrits récemment
     * 
     * @param int $days Nombre de jours
     * @return Owner[]
     */
    public function findRecentOwners(int $days = 30): array;

    /**
     * Récupère les propriétaires sans animaux
     * 
     * @return Owner[]
     */
    public function findOwnersWithoutAnimals(): array;

    /**
     * Pagination des propriétaires
     * 
     * @return array{items: Owner[], total: int, page: int, perPage: int, totalPages: int}
     */
    public function paginate(int $page = 1, int $perPage = 10): array;
}