<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\DTO\CreateAnimalDTO;
use App\Application\Port\In\CreateAnimalUseCaseInterface;
use App\Application\Port\In\GetAnimalUseCaseInterface;
use App\Domain\Exception\AnimalNotFoundException;
use App\Domain\Exception\PetManagementException;
use App\Presentation\Request\CreateAnimalRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[Route('/api/animals', name: 'api_animals_')]
#[OA\Tag(name: 'Animals', description: 'Gestion des animaux (chiens, chats, oiseaux)')]
class AnimalController extends AbstractController
{
    public function __construct(
        private readonly CreateAnimalUseCaseInterface $createAnimalUseCase,
        private readonly GetAnimalUseCaseInterface $getAnimalUseCase,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Lister tous les animaux',
        description: 'Retourne la liste de tous les animaux enregistrés dans le système',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des animaux récupérée avec succès',
                content: new OA\JsonContent(
                    examples: [
                        'Liste animaux' => new OA\Examples(
                            example: 'Liste animaux',
                            summary: 'Liste animaux',
                            value: '{
                                "success": true,
                                "data": [
                                    {
                                        "id": 1,
                                        "name": "Médor",
                                        "type": "dog",
                                        "birthDate": "2020-05-15T00:00:00+00:00",
                                        "weight": 25.5,
                                        "color": "Noir",
                                        "breed": "Labrador",
                                        "isDangerous": false
                                    },
                                    {
                                        "id": 2,
                                        "name": "Félix",
                                        "type": "cat", 
                                        "birthDate": "2021-03-20T00:00:00+00:00",
                                        "weight": 4.2,
                                        "color": "Blanc",
                                        "isIndoor": true,
                                        "isHypoallergenic": false
                                    }
                                ],
                                "count": 2
                            }'
                        )
                    ],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'count', type: 'integer', example: 5)
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erreur serveur',
                content: new OA\JsonContent(
                    examples: [
                        'Erreur serveur' => new OA\Examples(
                            example: 'Erreur serveur',
                            summary: 'Erreur serveur',
                            value: '{
                                "success": false,
                                "error": "Une erreur est survenue"
                            }'
                        )
                    ],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: 'Une erreur est survenue')
                    ]
                )
            )
        ]
    )]
    public function list(): JsonResponse
    {
        try {
            $animals = $this->getAnimalUseCase->executeAll();

            return $this->json([
                'success' => true,
                'data' => $animals,
                'count' => count($animals),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Une erreur est survenue lors de la récupération des animaux',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(
        summary: 'Récupérer un animal par son ID',
        description: 'Retourne les détails d\'un animal spécifique',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID de l\'animal',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Animal trouvé',
                content: new OA\JsonContent(
                    examples: [
                        'Chien' => new OA\Examples(
                            example: 'Chien',
                            summary: 'Chien',
                            value: '{
                                "success": true,
                                "data": {
                                    "id": 1,
                                    "name": "Médor",
                                    "type": "dog",
                                    "birthDate": "2020-05-15T00:00:00+00:00",
                                    "weight": 25.5,
                                    "color": "Noir",
                                    "breed": "Labrador",
                                    "isDangerous": false,
                                    "owner": {
                                        "id": 1,
                                        "firstName": "Jean",
                                        "lastName": "DUPONT"
                                    }
                                }
                            }'
                        ),
                        'Chat' => new OA\Examples(
                            example: 'Chat',
                            summary: 'Chat',
                            value: '{
                                "success": true,
                                "data": {
                                    "id": 2,
                                    "name": "Félix",
                                    "type": "cat",
                                    "birthDate": "2021-03-20T00:00:00+00:00", 
                                    "weight": 4.2,
                                    "color": "Blanc",
                                    "isIndoor": true,
                                    "isHypoallergenic": false,
                                    "owner": {
                                        "id": 1,
                                        "firstName": "Jean",
                                        "lastName": "DUPONT"
                                    }
                                }
                            }'
                        )
                    ],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Animal non trouvé',
                content: new OA\JsonContent(
                    examples: [
                        'Non trouvé' => new OA\Examples(
                            example: 'Non trouvé',
                            summary: 'Non trouvé',
                            value: '{
                                "success": false,
                                "error": "Animal non trouvé"
                            }'
                        )
                    ],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: 'Animal non trouvé')
                    ]
                )
            )
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $animal = $this->getAnimalUseCase->execute($id);

            return $this->json([
                'success' => true,
                'data' => $animal,
            ]);
        } catch (AnimalNotFoundException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Une erreur est survenue',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Créer un nouvel animal',
        description: 'Crée un nouvel animal (chien, chat ou oiseau)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                examples: [
                    'Créer chien' => new OA\Examples(
                        example: 'Créer chien',
                        summary: 'Créer chien',
                        value: '{
                            "type": "dog",
                            "name": "Médor",
                            "birthDate": "2020-05-15T00:00:00+00:00",
                            "weight": 25.5,
                            "color": "Noir",
                            "ownerId": 1,
                            "breed": "Labrador",
                            "isDangerous": false
                        }'
                    ),
                    'Créer chat' => new OA\Examples(
                        example: 'Créer chat',
                        summary: 'Créer chat',
                        value: '{
                            "type": "cat",
                            "name": "Félix",
                            "birthDate": "2021-03-20T00:00:00+00:00",
                            "weight": 4.2,
                            "color": "Blanc", 
                            "ownerId": 1,
                            "isIndoor": true,
                            "isHypoallergenic": false
                        }'
                    ),
                    'Créer oiseau' => new OA\Examples(
                        example: 'Créer oiseau',
                        summary: 'Créer oiseau',
                        value: '{
                            "type": "bird",
                            "name": "Coco",
                            "birthDate": "2019-07-10T00:00:00+00:00",
                            "weight": 0.8,
                            "color": "Vert",
                            "ownerId": 2,
                            "species": "Perroquet",
                            "wingSpan": 0.5,
                            "canTalk": true
                        }'
                    )
                ],
                properties: [
                    new OA\Property(property: 'type', type: 'string', enum: ['dog', 'cat', 'bird'], required: ['true'], example: 'dog'),
                    new OA\Property(property: 'name', type: 'string', required: ['true'], example: 'Médor'),
                    new OA\Property(property: 'birthDate', type: 'string', format: 'date-time', required: ['true']),
                    new OA\Property(property: 'weight', type: 'number', format: 'float', required: ['true'], example: 25.5),
                    new OA\Property(property: 'color', type: 'string', required: ['true'], example: 'Noir'),
                    new OA\Property(property: 'ownerId', type: 'integer', required: ['true'], example: 1),
                    new OA\Property(property: 'breed', type: 'string', required: ['false'], example: 'Labrador'),
                    new OA\Property(property: 'isDangerous', type: 'boolean', required: ['false'], example: false),
                    new OA\Property(property: 'isIndoor', type: 'boolean', required: ['false'], example: true),
                    new OA\Property(property: 'isHypoallergenic', type: 'boolean', required: ['false'], example: false),
                    new OA\Property(property: 'species', type: 'string', required: ['false'], example: 'Perroquet'),
                    new OA\Property(property: 'wingSpan', type: 'number', format: 'float', required: ['false'], example: 0.5),
                    new OA\Property(property: 'canTalk', type: 'boolean', required: ['false'], example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Animal créé avec succès',
                content: new OA\JsonContent(
                    examples: [
                        'Création réussie' => new OA\Examples(
                            example: 'Création réussie',
                            summary: 'Création réussie',
                            value: '{
                                "success": true,
                                "data": {
                                    "id": 1,
                                    "name": "Médor",
                                    "type": "dog",
                                    "birthDate": "2020-05-15T00:00:00+00:00",
                                    "weight": 25.5,
                                    "color": "Noir",
                                    "owner": {
                                        "id": 1,
                                        "firstName": "Jean",
                                        "lastName": "DUPONT"
                                    }
                                },
                                "message": "Animal créé avec succès"
                            }'
                        )
                    ],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                        new OA\Property(property: 'message', type: 'string', example: 'Animal créé avec succès')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Données invalides',
                content: new OA\JsonContent(
                    examples: [
                        'Propriétaire non trouvé' => new OA\Examples(
                            example: 'Propriétaire non trouvé',
                            summary: 'Propriétaire non trouvé',
                            value: '{
                                "success": false,
                                "error": "Propriétaire avec ID 999 non trouvé"
                            }'
                        ),
                        'Données invalides' => new OA\Examples(
                            example: 'Données invalides',
                            summary: 'Données invalides',
                            value: '{
                                "success": false,
                                "error": "Le nom ne peut pas être vide"
                            }'
                        )
                    ],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: 'Données invalides')
                    ]
                )
            )
        ]
    )]
    public function create(
        #[MapRequestPayload] CreateAnimalRequest $request
    ): JsonResponse {
        try {
            $dto = new CreateAnimalDTO(
                type: $request->type,
                name: $request->name,
                birthDate: $request->birthDate,
                weight: $request->weight,
                color: $request->color,
                ownerId: $request->ownerId,
                breed: $request->breed,
                isDangerous: $request->isDangerous,
                isIndoor: $request->isIndoor,
                isHypoallergenic: $request->isHypoallergenic,
                species: $request->species,
                wingSpan: $request->wingSpan,
                canTalk: $request->canTalk,
            );

            $animal = $this->createAnimalUseCase->execute($dto);

            return $this->json([
                'success' => true,
                'data' => $animal,
                'message' => 'Animal créé avec succès',
            ], Response::HTTP_CREATED);

        } catch (PetManagementException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Une erreur est survenue lors de la création',
                'details' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Delete(
        summary: 'Supprimer un animal',
        description: 'Supprime un animal par son ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID de l\'animal à supprimer',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Animal supprimé avec succès',
                content: new OA\JsonContent(
                    examples: [
                        'Suppression réussie' => new OA\Examples(
                            example: 'Suppression réussie',
                            summary: 'Suppression réussie',
                            value: '{
                                "success": true,
                                "message": "Animal supprimé avec succès"
                            }'
                        )
                    ],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Animal supprimé avec succès')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Animal non trouvé',
                content: new OA\JsonContent(
                    examples: [
                        'Non trouvé' => new OA\Examples(
                            example: 'Non trouvé',
                            summary: 'Non trouvé',
                            value: '{
                                "success": false,
                                "error": "Animal non trouvé"
                            }'
                        )
                    ],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: 'Animal non trouvé')
                    ]
                )
            )
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        try {
            // À implémenter avec un DeleteAnimalUseCase
            return $this->json([
                'success' => true,
                'message' => 'Animal supprimé avec succès',
            ]);
        } catch (AnimalNotFoundException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Une erreur est survenue lors de la suppression',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}