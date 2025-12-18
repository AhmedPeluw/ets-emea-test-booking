<?php
declare(strict_types=1);
namespace App\Controller;

use App\DTO\CreateSessionDTO;
use App\Service\SessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/sessions', name: 'api_sessions_')]
class SessionController extends AbstractController
{
    public function __construct(
        private SessionService $sessionService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $itemsPerPage = min(50, max(1, (int) $request->query->get('itemsPerPage', 10)));
        $language = $request->query->get('language');
        $level = $request->query->get('level');

        $result = $this->sessionService->listAvailableSessions(
            $page,
            $itemsPerPage,
            $language,
            $level
        );

        return $this->json([
            'success' => true,
            'data' => array_map(fn($session) => $this->formatSession($session), $result['items']),
            'pagination' => [
                'total' => $result['total'],
                'pages' => $result['pages'],
                'currentPage' => $result['currentPage'],
                'itemsPerPage' => $result['itemsPerPage']
            ]
        ]);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        try {
            $session = $this->sessionService->getSession($id);
            return $this->json([
                'success' => true,
                'data' => $this->formatSession($session)
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): JsonResponse
    {
        try {
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                CreateSessionDTO::class,
                'json'
            );

            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'errors' => $this->formatValidationErrors($errors)
                ], Response::HTTP_BAD_REQUEST);
            }

            $session = $this->sessionService->createSession($dto);

            return $this->json([
                'success' => true,
                'message' => 'Session créée avec succès',
                'data' => $this->formatSession($session)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(string $id, Request $request): JsonResponse
    {
        try {
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                CreateSessionDTO::class,
                'json'
            );

            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'errors' => $this->formatValidationErrors($errors)
                ], Response::HTTP_BAD_REQUEST);
            }

            $session = $this->sessionService->updateSession($id, $dto);

            return $this->json([
                'success' => true,
                'message' => 'Session mise à jour avec succès',
                'data' => $this->formatSession($session)
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(string $id): JsonResponse
    {
        try {
            $this->sessionService->deleteSession($id);
            return $this->json([
                'success' => true,
                'message' => 'Session supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function formatSession($session): array
    {
        return [
            'id' => $session->getId(),
            'language' => $session->getLanguage(),
            'date' => $session->getDate()->format('Y-m-d'),
            'time' => $session->getTime(),
            'location' => $session->getLocation(),
            'totalSeats' => $session->getTotalSeats(),
            'availableSeats' => $session->getAvailableSeats(),
            'description' => $session->getDescription(),
            'level' => $session->getLevel(),
            'durationMinutes' => $session->getDurationMinutes(),
            'price' => $session->getPrice(),
            'isActive' => $session->isActive()
        ];
    }

    private function formatValidationErrors($errors): array
    {
        $formatted = [];
        foreach ($errors as $error) {
            $formatted[$error->getPropertyPath()] = $error->getMessage();
        }
        return $formatted;
    }
}
