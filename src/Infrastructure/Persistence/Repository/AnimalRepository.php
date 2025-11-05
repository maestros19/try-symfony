<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Entity\Animal;
use App\Domain\Entity\Owner;
use App\Domain\Exception\AnimalNotFoundException;
use App\Domain\Repository\AnimalRepositoryInterface;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository Doctrine pour Animal
 * Adapter (Hexagonal Architecture) - Port Out
 * Démontre les requêtes DQL avancées et les Query Builder
 */
class AnimalRepository extends ServiceEntityRepository implements AnimalRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Animal::class);
    }

    public function findById(int $id): Animal
    {
        $animal = $this->find($id);

        if ($animal === null) {
            throw AnimalNotFoundException::withId($id);
        }

        return $animal;
    }

    public function findAll(): array
    {
        return $this->findBy([], ['name' => 'ASC']);
    }

    public function findByOwner(Owner $owner): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByType(string $type): array
    {
        // Utilisation du DQL avec discriminator
        return $this->createQueryBuilder('a')
            ->where('a INSTANCE OF :type')
            ->setParameter('type', $this->getClassForType($type))
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByAgeRange(int $minAge, int $maxAge): array
    {
        $now = new DateTimeImmutable();
        $maxBirthDate = $now->modify("-{$minAge} years");
        $minBirthDate = $now->modify("-{$maxAge} years");

        return $this->createQueryBuilder('a')
            ->where('a.birthDate BETWEEN :minDate AND :maxDate')
            ->setParameter('minDate', $minBirthDate)
            ->setParameter('maxDate', $maxBirthDate)
            ->orderBy('a.birthDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBy(
        array $criteria, 
        ?array $orderBy = null, 
        ?int $limit = null, 
        ?int $offset = null
    ): array {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    public function save(Animal $animal): void
    {
        $this->getEntityManager()->persist($animal);
        $this->getEntityManager()->flush();
    }

    public function delete(Animal $animal): void
    {
        $this->getEntityManager()->remove($animal);
        $this->getEntityManager()->flush();
    }

    public function count(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Requête personnalisée avec jointure et agrégation
     * 
     * @return array<string, int>
     */
    public function countByType(): array
    {
        $results = $this->createQueryBuilder('a')
            ->select('a.type, COUNT(a.id) as total')
            ->groupBy('a.type')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $result) {
            $counts[$result['type']] = (int) $result['total'];
        }

        return $counts;
    }

    /**
     * Requête avancée avec sous-requête
     * Trouve les animaux sans dossier médical
     * 
     * @return Animal[]
     */
    public function findWithoutMedicalRecords(): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.medicalRecords', 'mr')
            ->where('mr.id IS NULL')
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Requête avec fetch join pour optimisation N+1
     * 
     * @return Animal[]
     */
    public function findAllWithOwner(): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.owner', 'o')
            ->addSelect('o')
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function getClassForType(string $type): string
    {
        return match (strtolower($type)) {
            'dog', 'chien' => \App\Domain\Entity\Dog::class,
            'cat', 'chat' => \App\Domain\Entity\Cat::class,
            'bird', 'oiseau' => \App\Domain\Entity\Bird::class,
            default => Animal::class,
        };
    }
}