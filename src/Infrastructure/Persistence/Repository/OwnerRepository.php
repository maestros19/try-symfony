<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Entity\Owner;
use App\Domain\Exception\OwnerNotFoundException;
use App\Domain\Repository\OwnerRepositoryInterface;
use App\Domain\ValueObject\Email;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Implémentation Doctrine du OwnerRepositoryInterface (Port Out)
 */
class OwnerRepository extends ServiceEntityRepository implements OwnerRepositoryInterface
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Owner::class);
        $this->em = $em;
    }

    public function findById(int $id): Owner
    {
        $owner = $this->find($id);
        if (!$owner) {
            throw new OwnerNotFoundException("Aucun propriétaire trouvé pour l'ID {$id}");
        }
        return $owner;
    }

    public function findByEmail(Email $email): Owner
    {
        $owner = $this->findOneBy(['email' => (string) $email]);
        if (!$owner) {
            throw new OwnerNotFoundException("Aucun propriétaire trouvé pour l'email {$email}");
        }
        return $owner;
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function findActiveOwners(): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    public function findOwnersWithMinAnimals(int $minCount): array
    {
        return $this->createQueryBuilder('o')
            ->join('o.animals', 'a')
            ->groupBy('o.id')
            ->having('COUNT(a.id) >= :minCount')
            ->setParameter('minCount', $minCount)
            ->getQuery()
            ->getResult();
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findByCity(string $city): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.address.city = :city')
            ->setParameter('city', $city)
            ->getQuery()
            ->getResult();
    }

    public function findByPostalCode(string $postalCode): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.address.postalCode = :postalCode')
            ->setParameter('postalCode', $postalCode)
            ->getQuery()
            ->getResult();
    }

    public function save(Owner $owner): void
    {
        $this->em->persist($owner);
        $this->em->flush();
    }

    public function delete(Owner $owner): void
    {
        $this->em->remove($owner);
        $this->em->flush();
    }

    public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }

    public function countActive(): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function existsByEmail(Email $email): bool
    {
        return (bool) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.email = :email')
            ->setParameter('email', (string) $email)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllWithAnimals(): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.animals', 'a')
            ->addSelect('a')
            ->getQuery()
            ->getResult();
    }

    public function findByIdWithAnimals(int $id): Owner
    {
        $owner = $this->createQueryBuilder('o')
            ->leftJoin('o.animals', 'a')
            ->addSelect('a')
            ->andWhere('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$owner) {
            throw new OwnerNotFoundException("Aucun propriétaire trouvé avec ses animaux pour l'ID {$id}");
        }

        return $owner;
    }

    public function findWithAnimalStatistics(): array
    {
        $result = $this->createQueryBuilder('o')
            ->leftJoin('o.animals', 'a')
            ->select('o AS owner')
            ->addSelect('COUNT(a.id) AS totalAnimals')
            ->addSelect('AVG(a.weight) AS avgWeight')
            ->groupBy('o.id')
            ->getQuery()
            ->getResult();

        return array_map(static function ($row) {
            return [
                'owner' => $row['owner'],
                'totalAnimals' => (int) $row['totalAnimals'],
                'avgWeight' => (float) $row['avgWeight'],
            ];
        }, $result);
    }

    public function searchByName(string $searchTerm): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('LOWER(o.firstName) LIKE :term OR LOWER(o.lastName) LIKE :term')
            ->setParameter('term', '%' . strtolower($searchTerm) . '%')
            ->getQuery()
            ->getResult();
    }

    public function findRecentOwners(int $days = 30): array
    {
        $dateThreshold = new \DateTimeImmutable("-{$days} days");

        return $this->createQueryBuilder('o')
            ->andWhere('o.registrationDate >= :date')
            ->setParameter('date', $dateThreshold)
            ->getQuery()
            ->getResult();
    }

    public function findOwnersWithoutAnimals(): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.animals', 'a')
            ->andWhere('a.id IS NULL')
            ->getQuery()
            ->getResult();
    }

    public function paginate(int $page = 1, int $perPage = 10): array
    {
        $query = $this->createQueryBuilder('o')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery();

        $items = $query->getResult();
        $total = $this->count();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
        ];
    }
}
