<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\DTO\CreateOwnerDTO;
use App\Application\DTO\OwnerResponseDTO;
use App\Domain\Exception\InvalidOwnerDataException;
use App\Domain\Exception\OwnerNotFoundException;
use App\Application\UseCase\CreateOwnerUseCase;
use App\Application\UseCase\GetOwnerUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Route('/api/owners')]
#[OA\Tag(name: 'Propriétaires')]
class OwnerController extends AbstractController
{
    public function __construct(
        private CreateOwnerUseCase $createOwnerUseCase,
        // private ListOwnersUseCase $listOwnersUseCase,
        private GetOwnerUseCase $getOwnerUseCase,
        // private DeleteOwnerUseCase $deleteOwnerUseCase,
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Créer un nouveau propriétaire',
        description: 'Crée un nouveau propriétaire avec ses informations personnelles et son adresse',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                examples: [
                    'Créer propriétaire' => new OA\Examples(
                        example: 'Créer propriétaire',
                        summary: 'Créer propriétaire',
                        value: '{
                            "firstName": "Jean",
                            "lastName": "DUPONT",
                            "email": "jean.dupont@email.com",
                            "phoneNumber": "+33123456789",
                            "street": "123 Avenue des Champs-Élysées",
                            "city": "Paris",
                            "postalCode": "75008",
                            "country": "France"
                        }'
                    ),
                    'Créer propriétaire sans pays' => new OA\Examples(
                        example: 'Créer propriétaire sans pays',
                        summary: 'Créer propriétaire sans pays',
                        value: '{
                            "firstName": "Marie",
                            "lastName": "MARTIN",
                            "email": "marie.martin@email.com",
                            "phoneNumber": "+33987654321",
                            "street": "456 Rue de la République",
                            "city": "Lyon",
                            "postalCode": "69001"
                        }'
                    )
                ],
                properties: [
                    new OA\Property(property: 'firstName', type: 'string', required: ['true'], example: 'Jean'),
                    new OA\Property(property: 'lastName', type: 'string', required: ['true'], example: 'DUPONT'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', required: ['true'], example: 'jean.dupont@email.com'),
                    new OA\Property(property: 'phoneNumber', type: 'string', required: ['true'], example: '+33123456789'),
                    new OA\Property(property: 'street', type: 'string', required: ['true'], example: '123 Avenue des Champs-Élysées'),
                    new OA\Property(property: 'city', type: 'string', required: ['true'], example: 'Paris'),
                    new OA\Property(property: 'postalCode', type: 'string', required: ['true'], example: '75008'),
                    new OA\Property(property: 'country', type: 'string', required: ['false'], example: 'France')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Propriétaire créé avec succès',
                content: new OA\JsonContent(
                    examples: [
                        'Création réussie' => new OA\Examples(
                            example: 'Création réussie',
                            summary: 'Création réussie',
                            value: '{
                                "success": true,
                                "data": {
                                    "id": 1,
                                    "firstName": "Jean",
                                    "lastName": "DUPONT",
                                    "fullName": "Jean DUPONT",
                                    "email": "jean.dupont@email.com",
                                    "phoneNumber": "+33 1 23 45 67 89",
                                    "address": {
                                        "street": "123 Avenue des Champs-Élysées",
                                        "city": "Paris",
                                        "postalCode": "75008",
                                        "country": "France",
                                        "fullAddress": "123 Avenue des Champs-Élysées, 75008 Paris, France"
                                    },
                                    "totalAnimals": 0,
                                    "isActive": true,
                                    "registrationDate": "2024-01-15T10:30:00+00:00",
                                    "updatedAt": "2024-01-15T10:30:00+00:00"
                                },
                                "message": "Propriétaire créé avec succès"
                            }'
                        )
                    ],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                        new OA\Property(property: 'message', type: 'string', example: 'Propriétaire créé avec succès')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Données invalides',
                content: new OA\JsonContent(
                    examples: [
                        'Email invalide' => new OA\Examples(
                            example: 'Email invalide',
                            summary: 'Email invalide',
                            value: '{
                                "success": false,
                                "error": "L\'email \"email-invalide\" n\'est pas valide"
                            }'
                        ),
                        'Téléphone invalide' => new OA\Examples(
                            example: 'Téléphone invalide',
                            summary: 'Téléphone invalide',
                            value: '{
                                "success": false,
                                "error": "Le numéro de téléphone \"123\" n\'est pas valide"
                            }'
                        ),
                        'Champs manquants' => new OA\Examples(
                            example: 'Champs manquants',
                            summary: 'Champs manquants',
                            value: '{
                                "success": false,
                                "error": "Le champ firstName est obligatoire"
                            }'
                        )
                    ],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: 'Données invalides')
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Email déjà utilisé',
                content: new OA\JsonContent(
                    examples: [
                        'Email existant' => new OA\Examples(
                            example: 'Email existant',
                            summary: 'Email existant',
                            value: '{
                                "success": false,
                                "error": "Un propriétaire avec cet email existe déjà"
                            }'
                        )
                    ],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: 'Email déjà utilisé')
                    ]
                )
            )
        ]
    )]
    public function create(
        #[MapRequestPayload] CreateOwnerDTO $request
    ): JsonResponse {
        try {
            $dto = CreateOwnerDTO::fromArray($request->toArray());
            $responseDTO = $this->createOwnerUseCase->execute($dto);


            return $this->json([
                'success' => true,
                'data' => $responseDTO->toArray(),
                'message' => 'Propriétaire créé avec succès',
            ], Response::HTTP_CREATED);

        } catch (InvalidOwnerDataException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {

            dd($e->getMessage());
            return $this->json([
                'success' => false,
                'error' => 'Une erreur est survenue lors de la création du propriétaire',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // #[Route('', name: 'list', methods: ['GET'])]
    // #[OA\Get(
    //     summary: 'Lister tous les propriétaires',
    //     description: 'Retourne la liste de tous les propriétaires avec leurs informations de base',
    //     parameters: [
    //         new OA\Parameter(
    //             name: 'page',
    //             description: 'Numéro de page',
    //             in: 'query',
    //             required: false,
    //             schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    //         ),
    //         new OA\Parameter(
    //             name: 'limit',
    //             description: 'Nombre d\'éléments par page',
    //             in: 'query',
    //             required: false,
    //             schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, example: 10)
    //         ),
    //         new OA\Parameter(
    //             name: 'active',
    //             description: 'Filtrer par statut actif/inactif',
    //             in: 'query',
    //             required: false,
    //             schema: new OA\Schema(type: 'boolean', example: true)
    //         )
    //     ],
    //     responses: [
    //         new OA\Response(
    //             response: 200,
    //             description: 'Liste des propriétaires récupérée avec succès',
    //             content: new OA\JsonContent(
    //                 examples: [
    //                     'Liste réussie' => new OA\Examples(
    //                         example: 'Liste réussie',
    //                         summary: 'Liste réussie',
    //                         value: '{
    //                             "success": true,
    //                             "data": [
    //                                 {
    //                                     "id": 1,
    //                                     "firstName": "Jean",
    //                                     "lastName": "DUPONT",
    //                                     "fullName": "Jean DUPONT",
    //                                     "email": "jean.dupont@email.com",
    //                                     "phoneNumber": "+33 1 23 45 67 89",
    //                                     "totalAnimals": 2,
    //                                     "isActive": true,
    //                                     "registrationDate": "2024-01-15T10:30:00+00:00",
    //                                     "updatedAt": "2024-01-15T10:30:00+00:00"
    //                                 },
    //                                 {
    //                                     "id": 2,
    //                                     "firstName": "Marie",
    //                                     "lastName": "MARTIN",
    //                                     "fullName": "Marie MARTIN",
    //                                     "email": "marie.martin@email.com",
    //                                     "phoneNumber": "+33 4 56 78 90 12",
    //                                     "totalAnimals": 1,
    //                                     "isActive": true,
    //                                     "registrationDate": "2024-01-16T14:20:00+00:00",
    //                                     "updatedAt": "2024-01-16T14:20:00+00:00"
    //                                 }
    //                             ],
    //                             "pagination": {
    //                                 "page": 1,
    //                                 "limit": 10,
    //                                 "total": 2,
    //                                 "pages": 1
    //                             },
    //                             "message": "Propriétaires récupérés avec succès"
    //                         }'
    //                     )
    //                 ],
    //                 properties: [
    //                     new OA\Property(property: 'success', type: 'boolean', example: true),
    //                     new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
    //                     new OA\Property(property: 'pagination', type: 'object'),
    //                     new OA\Property(property: 'message', type: 'string', example: 'Propriétaires récupérés avec succès')
    //                 ]
    //             )
    //         )
    //     ]
    // )]
    // public function list(): JsonResponse
    // {
    //     try {
    //         $owners = $this->listOwnersUseCase->execute();
    //         $ownersDTO = array_map(
    //             fn($owner) => OwnerResponseDTO::fromEntityForList($owner)->toArray(),
    //             $owners
    //         );

    //         return $this->json([
    //             'success' => true,
    //             'data' => $ownersDTO,
    //             'message' => 'Propriétaires récupérés avec succès',
    //         ]);

    //     } catch (\Exception $e) {
    //         return $this->json([
    //             'success' => false,
    //             'error' => 'Une erreur est survenue lors de la récupération des propriétaires',
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        summary: 'Récupérer un propriétaire spécifique',
        description: 'Retourne les détails complets d\'un propriétaire, y compris ses animaux et statistiques',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID du propriétaire',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Propriétaire récupéré avec succès',
                content: new OA\JsonContent(
                    examples: [
                        'Détails réussis' => new OA\Examples(
                            example: 'Détails réussis',
                            summary: 'Détails réussis',
                            value: '{
                                "success": true,
                                "data": {
                                    "id": 1,
                                    "firstName": "Jean",
                                    "lastName": "DUPONT",
                                    "fullName": "Jean DUPONT",
                                    "email": "jean.dupont@email.com",
                                    "phoneNumber": "+33 1 23 45 67 89",
                                    "address": {
                                        "street": "123 Avenue des Champs-Élysées",
                                        "city": "Paris",
                                        "postalCode": "75008",
                                        "country": "France",
                                        "fullAddress": "123 Avenue des Champs-Élysées, 75008 Paris, France"
                                    },
                                    "totalAnimals": 2,
                                    "isActive": true,
                                    "registrationDate": "2024-01-15T10:30:00+00:00",
                                    "updatedAt": "2024-01-15T10:30:00+00:00",
                                    "animals": [
                                        {
                                            "id": 1,
                                            "type": "dog",
                                            "name": "Médor",
                                            "age": 4,
                                            "color": "Noir"
                                        },
                                        {
                                            "id": 2,
                                            "type": "cat",
                                            "name": "Félix",
                                            "age": 3,
                                            "color": "Blanc"
                                        }
                                    ],
                                    "statistics": {
                                        "totalAnimals": 2,
                                        "animalsByType": {
                                            "dog": 1,
                                            "cat": 1
                                        },
                                        "averageAge": 3.5,
                                        "seniorAnimals": 0
                                    }
                                },
                                "message": "Propriétaire récupéré avec succès"
                            }'
                        )
                    ],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                        new OA\Property(property: 'message', type: 'string', example: 'Propriétaire récupéré avec succès')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Propriétaire non trouvé',
                content: new OA\JsonContent(
                    examples: [
                        'Non trouvé' => new OA\Examples(
                            example: 'Non trouvé',
                            summary: 'Non trouvé',
                            value: '{
                                "success": false,
                                "error": "Propriétaire avec ID 999 non trouvé"
                            }'
                        )
                    ],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: 'Propriétaire non trouvé')
                    ]
                )
            )
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $owner = $this->getOwnerUseCase->execute($id);
            $responseDTO = $owner;

            return $this->json([
                'success' => true,
                'data' => $responseDTO->toArray(),
                'message' => 'Propriétaire récupéré avec succès',
            ]);

        } catch (OwnerNotFoundException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Une erreur est survenue lors de la récupération du propriétaire',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    // #[OA\Delete(
    //     summary: 'Supprimer un propriétaire',
    //     description: 'Supprime un propriétaire et tous ses animaux associés',
    //     parameters: [
    //         new OA\Parameter(
    //             name: 'id',
    //             description: 'ID du propriétaire',
    //             in: 'path',
    //             required: true,
    //             schema: new OA\Schema(type: 'integer', example: 1)
    //         )
    //     ],
    //     responses: [
    //         new OA\Response(
    //             response: 200,
    //             description: 'Propriétaire supprimé avec succès',
    //             content: new OA\JsonContent(
    //                 examples: [
    //                     'Suppression réussie' => new OA\Examples(
    //                         example: 'Suppression réussie',
    //                         summary: 'Suppression réussie',
    //                         value: '{
    //                             "success": true,
    //                             "message": "Propriétaire et ses 2 animaux supprimés avec succès"
    //                         }'
    //                     )
    //                 ],
    //                 properties: [
    //                     new OA\Property(property: 'success', type: 'boolean', example: true),
    //                     new OA\Property(property: 'message', type: 'string', example: 'Propriétaire supprimé avec succès')
    //                 ]
    //             )
    //         ),
    //         new OA\Response(
    //             response: 404,
    //             description: 'Propriétaire non trouvé',
    //             content: new OA\JsonContent(
    //                 examples: [
    //                     'Non trouvé' => new OA\Examples(
    //                         example: 'Non trouvé',
    //                         summary: 'Non trouvé',
    //                         value: '{
    //                             "success": false,
    //                             "error": "Propriétaire avec ID 999 non trouvé"
    //                         }'
    //                     )
    //                 ],
    //                 properties: [
    //                     new OA\Property(property: 'success', type: 'boolean', example: false),
    //                     new OA\Property(property: 'error', type: 'string', example: 'Propriétaire non trouvé')
    //                 ]
    //             )
    //         ),
    //         new OA\Response(
    //             response: 409,
    //             description: 'Suppression impossible',
    //             content: new OA\JsonContent(
    //                 examples: [
    //                     'Suppression impossible' => new OA\Examples(
    //                         example: 'Suppression impossible',
    //                         summary: 'Suppression impossible',
    //                         value: '{
    //                             "success": false,
    //                             "error": "Impossible de supprimer le propriétaire car il a des animaux en soins actifs"
    //                         }'
    //                     )
    //                 ],
    //                 properties: [
    //                     new OA\Property(property: 'success', type: 'boolean', example: false),
    //                     new OA\Property(property: 'error', type: 'string', example: 'Suppression impossible')
    //                 ]
    //             )
    //         )
    //     ]
    // )]
    // public function delete(int $id): JsonResponse
    // {
    //     try {
    //         $this->deleteOwnerUseCase->execute($id);

    //         return $this->json([
    //             'success' => true,
    //             'message' => 'Propriétaire supprimé avec succès',
    //         ]);

    //     } catch (OwnerNotFoundException $e) {
    //         return $this->json([
    //             'success' => false,
    //             'error' => $e->getMessage(),
    //         ], Response::HTTP_NOT_FOUND);
    //     } catch (\DomainException $e) {
    //         return $this->json([
    //             'success' => false,
    //             'error' => $e->getMessage(),
    //         ], Response::HTTP_CONFLICT);
    //     } catch (\Exception $e) {
    //         return $this->json([
    //             'success' => false,
    //             'error' => 'Une erreur est survenue lors de la suppression du propriétaire',
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }
}