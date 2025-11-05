<?php

declare(strict_types=1);

namespace App\Presentation\CLI;

use App\Application\DTO\CreateAnimalDTO;
use App\Application\DTO\CreateOwnerDTO;
use App\Application\Port\In\CreateAnimalUseCaseInterface;
use App\Application\Port\In\CreateOwnerUseCaseInterface;

use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande CLI pour créer des données de démonstration
 * Démontre l'utilisation de l'architecture hexagonale depuis la CLI
 */
#[AsCommand(
    name: 'app:create-sample-data',
    description: 'Crée des données de démonstration pour le système de gestion des animaux',
)]
class CreateSampleDataCommand extends Command
{
    public function __construct(
        private readonly CreateOwnerUseCaseInterface $createOwnerUseCase,
        private readonly CreateAnimalUseCaseInterface $createAnimalUseCase,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Création de données de démonstration');

        try {
            // Création des propriétaires
            $io->section('Création des propriétaires');
            
            $owner1 = $this->createOwnerUseCase->execute(new CreateOwnerDTO(
                firstName: 'Jean',
                lastName: 'Dupont',
                email: 'jean.dupont@example.com',
                phoneNumber: '0612345678',
                street: '123 Rue de la République',
                city: 'Paris',
                postalCode: '75001',
                country: 'France'
            ));
            $io->success(sprintf('Propriétaire créé: %s', $owner1->fullName));

            $owner2 = $this->createOwnerUseCase->execute(new CreateOwnerDTO(
                firstName: 'Marie',
                lastName: 'Martin',
                email: 'marie.martin@example.com',
                phoneNumber: '0623456789',
                street: '456 Avenue des Champs',
                city: 'Lyon',
                postalCode: '69001',
                country: 'France'
            ));
            $io->success(sprintf('Propriétaire créé: %s', $owner2->fullName));

            $owner3 = $this->createOwnerUseCase->execute(new CreateOwnerDTO(
                firstName: 'Pierre',
                lastName: 'Dubois',
                email: 'pierre.dubois@example.com',
                phoneNumber: '0634567890',
                street: '789 Boulevard du Centre',
                city: 'Marseille',
                postalCode: '13001',
                country: 'France'
            ));
            $io->success(sprintf('Propriétaire créé: %s', $owner3->fullName));

            // Création des animaux
            $io->section('Création des animaux');

            // Chiens
            $dog1 = $this->createAnimalUseCase->execute(new CreateAnimalDTO(
                type: 'Dog',
                name: 'Rex',
                birthDate: new DateTimeImmutable('2019-03-15'),
                weight: 25.5,
                color: 'Marron',
                ownerId: $owner1->id,
                breed: 'Berger Allemand',
                isDangerous: false
            ));
            $io->success(sprintf('Chien créé: %s (%s)', $dog1->name, $dog1->sound));

            $dog2 = $this->createAnimalUseCase->execute(new CreateAnimalDTO(
                type: 'Dog',
                name: 'Max',
                birthDate: new DateTimeImmutable('2020-07-22'),
                weight: 8.2,
                color: 'Blanc',
                ownerId: $owner2->id,
                breed: 'Jack Russell',
                isDangerous: false
            ));
            $io->success(sprintf('Chien créé: %s (%s)', $dog2->name, $dog2->sound));

            // Chats
            $cat1 = $this->createAnimalUseCase->execute(new CreateAnimalDTO(
                type: 'Cat',
                name: 'Minou',
                birthDate: new DateTimeImmutable('2018-11-05'),
                weight: 4.5,
                color: 'Gris',
                ownerId: $owner1->id,
                isIndoor: true,
                isHypoallergenic: false
            ));
            $io->success(sprintf('Chat créé: %s (%s)', $cat1->name, $cat1->sound));

            $cat2 = $this->createAnimalUseCase->execute(new CreateAnimalDTO(
                type: 'Cat',
                name: 'Félix',
                birthDate: new DateTimeImmutable('2021-02-14'),
                weight: 3.8,
                color: 'Noir et blanc',
                ownerId: $owner2->id,
                isIndoor: false,
                isHypoallergenic: false
            ));
            $io->success(sprintf('Chat créé: %s (%s)', $cat2->name, $cat2->sound));

            // Oiseaux
            $bird1 = $this->createAnimalUseCase->execute(new CreateAnimalDTO(
                type: 'Bird',
                name: 'Coco',
                birthDate: new DateTimeImmutable('2017-05-20'),
                weight: 0.4,
                color: 'Vert et jaune',
                ownerId: $owner3->id,
                species: 'Perroquet',
                wingSpan: 35.0,
                canTalk: true
            ));
            $io->success(sprintf('Oiseau créé: %s (%s)', $bird1->name, $bird1->sound));

            $bird2 = $this->createAnimalUseCase->execute(new CreateAnimalDTO(
                type: 'Bird',
                name: 'Piou-Piou',
                birthDate: new DateTimeImmutable('2022-01-10'),
                weight: 0.02,
                color: 'Jaune',
                ownerId: $owner3->id,
                species: 'Canari',
                wingSpan: 15.0,
                canTalk: false
            ));
            $io->success(sprintf('Oiseau créé: %s (%s)', $bird2->name, $bird2->sound));

            $io->newLine();
            $io->success('Toutes les données de démonstration ont été créées avec succès!');
            
            $io->table(
                ['Statistiques', 'Nombre'],
                [
                    ['Propriétaires', 3],
                    ['Chiens', 2],
                    ['Chats', 2],
                    ['Oiseaux', 2],
                    ['Total Animaux', 6],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Erreur lors de la création des données: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}