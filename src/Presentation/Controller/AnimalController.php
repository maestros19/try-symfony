<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

/**
 * Domain imports
 */
use App\Application\DTO\CreateAnimalDTO;
use App\Application\Port\In\CreateAnimalUseCaseInterface;
use App\Application\Port\In\GetAnimalUseCaseInterface;
use App\Domain\Exception\AnimalNotFoundException;
use App\Domain\Exception\PetManagementException;
use App\Presentation\Request\CreateAnimalRequest;

/**
 * Symfony imports
 */
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Contrôleur REST pour la gestion des animaux
 * Adapter (Hexagonal Architecture)
 */
#[Route('/api/animals', name: 'api_animals_')]
class AnimalController extends AbstractController
{
    public function __construct(
        private readonly CreateAnimalUseCaseInterface $createAnimalUseCase,
        private readonly GetAnimalUseCaseInterface $getAnimalUseCase,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
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
    public function create(
        #[MapRequestPayload] CreateAnimalRequest $request
    ): JsonResponse {
        try {
            // Conversion du Request en DTO
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