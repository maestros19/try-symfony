# 🐾 Système de Gestion d'Animaux de Compagnie

## 📋 Vue d'ensemble

Application de gestion d'animaux de compagnie développée avec Symfony 7 en utilisant une **Architecture Hexagonale** (Ports & Adapters). Ce projet démontre les concepts avancés de la POO, les principes SOLID, et les fonctionnalités avancées de Doctrine ORM.

## 🏗️ Architecture Hexagonale

```
┌─────────────────────────────────────────────────────┐
│               PRESENTATION LAYER                    │
│  ┌──────────────┐  ┌───────────────┐  ┌──────────┐  │
│  │ Controllers  │  │  CLI Commands │  │ Requests │  │
│  └──────────────┘  └───────────────┘  └──────────┘  │
└─────────────────────────────────────────────────────┘
                        ▼
┌─────────────────────────────────────────────────────┐
│              APPLICATION LAYER                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────┐   │
│  │  Use Cases   │  │     DTOs     │  │  Ports   │   │
│  │  (Services)  │  │              │  │  In/Out  │   │
│  └──────────────┘  └──────────────┘  └──────────┘   │
└─────────────────────────────────────────────────────┘
                        ▼
┌─────────────────────────────────────────────────────┐
│                 DOMAIN LAYER                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────┐   │
│  │   Entities   │  │Value Objects │  │ Services │   │
│  │ (Business)   │  │              │  │ (Domain) │   │
│  └──────────────┘  └──────────────┘  └──────────┘   │
│  ┌──────────────┐  ┌──────────────┐                 │
│  │  Exceptions  │  │ Repositories │                 │
│  │              │  │ (Interfaces) │                 │
│  └──────────────┘  └──────────────┘                 │
└─────────────────────────────────────────────────────┘
                        ▼
┌─────────────────────────────────────────────────────┐
│            INFRASTRUCTURE LAYER                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────┐   │
│  │ Repositories │  │   Doctrine   │  │  Config  │   │
│  │  (Concrete)  │  │   Mappings   │  │          │   │
│  └──────────────┘  └──────────────┘  └──────────┘   │
└─────────────────────────────────────────────────────┘
```

## 🎯 Concepts POO Démontrés

### 1. **Héritage**
- Classe abstraite `Animal` avec sous-classes `Dog`, `Cat`, `Bird`
- Méthodes abstraites obligatoires (`makeSound()`, `getType()`)
- Héritage SINGLE_TABLE avec Doctrine (discriminator)

### 2. **Polymorphisme**
- Méthode `makeSound()` redéfinie dans chaque sous-classe
- Méthode `getSpecialNeeds()` surchargée selon le type d'animal
- Traitement uniforme des animaux malgré leurs différences

### 3. **Encapsulation**
- Propriétés privées avec getters publics
- Value Objects immutables (Email, PhoneNumber, Address)
- Validation dans les constructeurs

### 4. **Abstraction**
- Interfaces pour les repositories (Port Out)
- Interfaces pour les use cases (Port In)
- Séparation contrat/implémentation

### 5. **Interfaces**
- `AnimalRepositoryInterface`, `OwnerRepositoryInterface`
- `CreateAnimalUseCaseInterface`, etc.
- Inversion de dépendances via DI

## 🔧 Principes SOLID

### S - Single Responsibility Principle
- Chaque classe a une seule responsabilité
- `CreateAnimalUseCase` : création uniquement
- `AnimalManagementService` : logique métier uniquement

### O - Open/Closed Principle
- `Animal` ouvert à l'extension (nouvelles sous-classes)
- Fermé à la modification (logique commune stable)

### L - Liskov Substitution Principle
- Toute sous-classe d'`Animal` peut remplacer `Animal`
- Polymorphisme respecté

### I - Interface Segregation Principle
- Interfaces spécifiques par use case
- Pas de méthodes inutiles imposées

### D - Dependency Inversion Principle
- Dépendance sur les abstractions (interfaces)
- Injection de dépendances via constructeur
- Configuration dans `services.yaml`

## 🗄️ Fonctionnalités Doctrine Avancées

### 1. **Héritage avec Discriminator**
```xml
<entity inheritance-type="SINGLE_TABLE">
    <discriminator-column name="type" type="string"/>
    <discriminator-map>
        <discriminator-mapping value="dog" class="Dog"/>
        <discriminator-mapping value="cat" class="Cat"/>
    </discriminator-map>
</entity>
```

### 2. **Value Objects Embedables**
```xml
<embedded name="email" class="Email">
    <field name="value" type="string" column="email"/>
</embedded>
```

### 3. **Relations Bidirectionnelles**
- `Owner` ↔ `Animal` (OneToMany/ManyToOne)
- `Animal` ↔ `MedicalRecord` (OneToMany/ManyToOne)
- Cascade operations (persist, remove)

### 4. **Index et Contraintes**
- Index simples et composites
- Contraintes d'unicité
- Index sur les colonnes fréquemment requêtées

### 5. **Query Builder Avancé**
```php
$this->createQueryBuilder('a')
    ->leftJoin('a.owner', 'o')
    ->addSelect('o')
    ->where('a.birthDate BETWEEN :minDate AND :maxDate')
    ->orderBy('a.name', 'ASC')
    ->getQuery()
    ->getResult();
```

### 6. **DQL avec INSTANCE OF**
```php
->where('a INSTANCE OF :type')
```

## 📦 Structure des Fichiers

```
src/
├── Application
│   ├── DTO
│   │   ├── AnimalResponseDTO.php
│   │   ├── CreateAnimalDTO.php
│   │   ├── CreateOwnerDTO.php
│   │   └── OwnerResponseDTO.php
│   ├── Port
│   │   ├── In
│   │   │   ├── CreateAnimalUseCaseInterface.php
│   │   │   ├── CreateOwnerUseCaseInterface.php
│   │   │   ├── GetAnimalUseCaseInterface.php
│   │   │   └── GetOwnerUseCaseInterface.php
│   │   └── Out
│   └── UseCase
│       ├── CreateAnimalUseCase.php
│       └── GetOwnerUseCase.php
├── Domain
│   ├── Entity
│   │   ├── Animal.php
│   │   ├── Bird.php
│   │   ├── Cat.php
│   │   ├── Dog.php
│   │   └── Owner.php
│   ├── Exception
│   │   ├── AnimalNotFoundException.php
│   │   ├── InvalidAddressException.php
│   │   ├── InvalidAnimalDataException.php
│   │   ├── InvalidEmailException.php
│   │   ├── InvalidMedicalRecordException.php
│   │   ├── InvalidOwnerDataException.php
│   │   ├── InvalidPhoneNumberException.php
│   │   ├── OwnerNotFoundException.php
│   │   └── PetManagementException.php
│   ├── Repository
│   │   ├── AnimalRepositoryInterface.php
│   │   └── OwnerRepositoryInterface.php
│   ├── Service
│   │   └── AnimalManagementService.php
│   └── ValueObject
│       ├── Address.php
│       ├── Email.php
│       └── PhoneNumber.php
├── Infrastructure
│   ├── Adapter
│   │   ├── CLI
│   │   ├── Config
│   │   │   └── service.yml
│   │   └── Http
│   └── Persistence
│       ├── Doctrine
│       │   └── Animal.orm.xml
│       └── Repository
│           └── AnimalRepository.php
├── Kernel.php
└── Presentation
    ├── CLI
    │   └── CreateSampleDataCommand.php
    ├── Controller
    │   └── AnimalController.php
    └── Request
        └── CreateAnimalRequest.php


        Mettre a jour le symfony lock

        composer recipes:install doctrine/doctrine-bundle --force

        php bin/console make:migration
        php bin/console doctrine:database:create
        php bin/console doctrine:database:diff
        php bin/console doctrine:migrations:migrate
