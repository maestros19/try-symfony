<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Dog;
use App\Domain\Entity\Owner;
use App\Domain\Exception\InvalidAnimalDataException;
use App\Domain\ValueObject\Address;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PhoneNumber;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Dog
 * Démontre les tests de la logique métier
 */
class DogTest extends TestCase
{
    private Dog $dog;

    protected function setUp(): void
    {
        $this->dog = new Dog(
            name: 'Rex',
            birthDate: new DateTimeImmutable('2019-01-01'),
            weight: 25.5,
            color: 'Marron',
            breed: 'Berger Allemand',
            isDangerous: false
        );
    }

    public function testDogCreation(): void
    {
        self::assertInstanceOf(Dog::class, $this->dog);
        self::assertEquals('Rex', $this->dog->getName());
        self::assertEquals(25.5, $this->dog->getWeight());
        self::assertEquals('Marron', $this->dog->getColor());
        self::assertEquals('Berger Allemand', $this->dog->getBreed());
        self::assertFalse($this->dog->isDangerous());
    }

    public function testMakeSoundLargeDog(): void
    {
        // Chien > 20kg fait "WOOF WOOF!"
        self::assertEquals('WOOF WOOF!', $this->dog->makeSound());
    }

    public function testMakeSoundSmallDog(): void
    {
        $smallDog = new Dog(
            name: 'Mimi',
            birthDate: new DateTimeImmutable('2020-01-01'),
            weight: 5.0,
            color: 'Blanc',
            breed: 'Chihuahua',
            isDangerous: false
        );

        // Chien <= 20kg fait "Woof woof!"
        self::assertEquals('Woof woof!', $smallDog->makeSound());
    }

    public function testGetType(): void
    {
        self::assertEquals('Chien', $this->dog->getType());
    }

    public function testCalculateAge(): void
    {
        $birthDate = new DateTimeImmutable('-3 years');
        $dog = new Dog(
            name: 'Max',
            birthDate: $birthDate,
            weight: 10.0,
            color: 'Noir',
            breed: 'Labrador',
            isDangerous: false
        );

        self::assertEquals(3, $dog->calculateAge());
    }

    public function testSpecialNeedsForNormalDog(): void
    {
        $needs = $this->dog->getSpecialNeeds();

        self::assertIsArray($needs);
        self::assertContains('Vaccination annuelle', $needs);
        self::assertContains('Contrôle vétérinaire', $needs);
        self::assertContains('Promenade quotidienne minimum 30 minutes', $needs);
        self::assertNotContains('Port de la muselière obligatoire en public', $needs);
    }

    public function testSpecialNeedsForDangerousDog(): void
    {
        $dangerousDog = new Dog(
            name: 'Brutus',
            birthDate: new DateTimeImmutable('2018-01-01'),
            weight: 40.0,
            color: 'Noir',
            breed: 'Rottweiler',
            isDangerous: true
        );

        $needs = $dangerousDog->getSpecialNeeds();

        self::assertContains('Port de la muselière obligatoire en public', $needs);
        self::assertContains('Assurance responsabilité civile spécifique', $needs);
    }

    public function testAssignOwner(): void
    {
        $owner = new Owner(
            firstName: 'Jean',
            lastName: 'Dupont',
            email: new Email('jean@example.com'),
            phoneNumber: new PhoneNumber('0612345678'),
            address: new Address('123 Rue', 'Paris', '75001')
        );

        $this->dog->assignOwner($owner);

        self::assertSame($owner, $this->dog->getOwner());
        self::assertTrue($owner->getAnimals()->contains($this->dog));
    }

    public function testRemoveOwner(): void
    {
        $owner = new Owner(
            firstName: 'Jean',
            lastName: 'Dupont',
            email: new Email('jean@example.com'),
            phoneNumber: new PhoneNumber('0612345678'),
            address: new Address('123 Rue', 'Paris', '75001')
        );

        $this->dog->assignOwner($owner);
        $this->dog->removeOwner();

        self::assertNull($this->dog->getOwner());
        self::assertFalse($owner->getAnimals()->contains($this->dog));
    }

    public function testUpdateWeight(): void
    {
        $this->dog->updateWeight(30.0);
        self::assertEquals(30.0, $this->dog->getWeight());
    }

    public function testInvalidNameThrowsException(): void
    {
        $this->expectException(InvalidAnimalDataException::class);
        $this->expectExceptionMessage('Le nom de l\'animal ne peut pas être vide');

        new Dog(
            name: '',
            birthDate: new DateTimeImmutable(),
            weight: 10.0,
            color: 'Noir',
            breed: 'Test',
            isDangerous: false
        );
    }

    public function testInvalidWeightThrowsException(): void
    {
        $this->expectException(InvalidAnimalDataException::class);

        new Dog(
            name: 'Test',
            birthDate: new DateTimeImmutable(),
            weight: -5.0,
            color: 'Noir',
            breed: 'Test',
            isDangerous: false
        );
    }

    public function testGetFullDescription(): void
    {
        $description = $this->dog->getFullDescription();
        
        self::assertStringContainsString('Chien', $description);
        self::assertStringContainsString('Rex', $description);
        self::assertStringContainsString('25.5', $description);
        self::assertStringContainsString('Marron', $description);
    }

    public function testRegistrationNumber(): void
    {
        self::assertNull($this->dog->getRegistrationNumber());

        $this->dog->setRegistrationNumber('FR123456789');
        self::assertEquals('FR123456789', $this->dog->getRegistrationNumber());
    }
}