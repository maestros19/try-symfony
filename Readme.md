1- lister les animauxx de compagnies.
2- proprietaires des animaux.

.
├── Application
│   ├── DTO
│   ├── Port
│   │   ├── In
│   │   └── Out
│   └── UseCase
├── Domain
│   ├── Entity
│   ├── Exception
│   ├── Repository
│   └── Service
├── Infrastructure
│   ├── Adapter
│   │   ├── CLI
│   │   ├── Config
│   │   └── Http
│   └── Persistence
│       ├── Doctrine
│       └── Repository
├── Kernel.php
└── Presentation
    ├── CLI
    ├── Controller
    │   └── RecipeController.php
    └── Request


voila la structurre de mon projet, l'ideeee est de faire un egstion des des animaux de compagnie leure proprietaire, on doit voir les concept de l'oriente object (heritage, polymophisme, interface, protectin des donnees), tu dois aussi utiliser les principes de solid dans ce code pour qu'il soit reutilisable le plus possible, sans repetition et tout les bonne pratique... on doit egalement voir comment j'utilise les concepts avances de Doctrines, bref fais tout comme un developpeur senior Symfony partant de cette architecture de base.

on est sur du symfony 7 et j'utilise une architecture hexagonale


# 🐾 Système de Gestion d'Animaux de Compagnie

## 📋 Vue d'ensemble

Application de gestion d'animaux de compagnie développée avec Symfony 7 en utilisant une **Architecture Hexagonale** (Ports & Adapters). Ce projet démontre les concepts avancés de la POO, les principes SOLID, et les fonctionnalités avancées de Doctrine ORM.

## 🏗️ Architecture Hexagonale

```
┌─────────────────────────────────────────────────────┐
│               PRESENTATION LAYER                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────┐  │
│  │ Controllers  │  │  CLI Commands │  │ Requests │  │
│  └──────────────┘  └──────────────┘  └──────────┘  │
└─────────────────────────────────────────────────────┘
                        ▼
┌─────────────────────────────────────────────────────┐
│              APPLICATION LAYER                       │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────┐  │
│  │  Use Cases   │  │     DTOs     │  │  Ports   │  │
│  │  (Services)  │  │              │  │  In/Out  │  │
│  └──────────────┘  └──────────────┘  └──────────┘  │
└─────────────────────────────────────────────────────┘
                        ▼
┌─────────────────────────────────────────────────────┐
│                 DOMAIN LAYER                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────┐  │
│  │   Entities   │  │Value Objects │  │ Services │  │
│  │ (Business)   │  │              │  │ (Domain) │  │
│  └──────────────┘  └──────────────┘  └──────────┘  │
│  ┌──────────────┐  ┌──────────────┐                │
│  │  Exceptions  │  │ Repositories │                │
│  │              │  │ (Interfaces) │                │
│  └──────────────┘  └──────────────┘                │
└─────────────────────────────────────────────────────┘
                        ▼
┌─────────────────────────────────────────────────────┐
│            INFRASTRUCTURE LAYER                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────┐  │
│  │ Repositories │  │   Doctrine   │  │  Config  │  │
│  │  (Concrete)  │  │   Mappings   │  │          │  │
│  └──────────────┘  └──────────────┘  └──────────┘  │
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
├── Application/
│   ├── DTO/
│   │   ├── CreateAnimalDTO.php
│   │   ├── CreateOwnerDTO.php
│   │   ├── AnimalResponseDTO.php
│   │   └── OwnerResponseDTO.php
│   ├── Port/
│   │   ├── In/
│   │   │   ├── CreateAnimalUseCaseInterface.php
│   │   │   └── GetAnimalUseCaseInterface.php
│   │   └── Out/ (Repositories interfaces dans Domain)
│   └── UseCase/
│       ├── CreateAnimalUseCase.php
│       ├── CreateOwnerUseCase.php
│       ├── GetAnimalUseCase.php
│       └── GetOwnerUseCase.php
├── Domain/
│   ├── Entity/
│   │   ├── Animal.php (abstract)
│   │   ├── Dog.php
│   │   ├── Cat.php
│   │   ├── Bird.php
│   │   ├── Owner.php
│   │   └── MedicalRecord.php
│   ├── ValueObject/
│   │   ├── Email.php
│   │   ├── PhoneNumber.php
│   │   └── Address.php
│   ├── Exception/
│   │   ├── PetManagementException.php (base)
│   │   ├── InvalidAnimalDataException.php
│   │   └── ...
│   ├── Repository/ (interfaces)
│   │   ├── AnimalRepositoryInterface.php
│   │   └── OwnerRepositoryInterface.php
│   └── Service/
│       └── AnimalManagementService.php
├── Infrastructure/
│   ├── Adapter/
│   │   └── Config/
│   │       └── services.yaml
│   └── Persistence/
│       ├── Doctrine/
│       │   ├── Animal.orm.xml
│       │   ├──