<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\UpdateUserDTO;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users', name: 'api_users_')]
#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function getProfile(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'success' => true,
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
            ]
        ]);
    }

    #[Route('/me', name: 'update', methods: ['PUT', 'PATCH'])]
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                UpdateUserDTO::class,
                'json'
            );

            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'errors' => $this->formatValidationErrors($errors)
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->userService->updateProfile($this->getUser(), $dto);

            return $this->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
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
