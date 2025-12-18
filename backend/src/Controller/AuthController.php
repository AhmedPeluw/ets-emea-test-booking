<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\LoginDTO;
use App\DTO\RegisterDTO;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * AuthController - Design Pattern: MVC Controller
 * 
 * Gère les endpoints d'authentification
 */
#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private AuthService $authService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * Inscription d'un nouvel utilisateur
     */
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            // Désérialiser le DTO
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                RegisterDTO::class,
                'json'
            );

            // Valider
            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'errors' => $this->formatValidationErrors($errors)
                ], Response::HTTP_BAD_REQUEST);
            }

            // Inscrire l'utilisateur
            $user = $this->authService->register($dto);

            return $this->json([
                'success' => true,
                'message' => 'Inscription réussie',
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail()
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Connexion d'un utilisateur
     */
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            // Désérialiser le DTO
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                LoginDTO::class,
                'json'
            );

            // Valider
            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'errors' => $this->formatValidationErrors($errors)
                ], Response::HTTP_BAD_REQUEST);
            }

            // Authentifier
            $result = $this->authService->login($dto);

            return $this->json([
                'success' => true,
                'token' => $result['token'],
                'user' => [
                    'id' => $result['user']->getId(),
                    'name' => $result['user']->getName(),
                    'email' => $result['user']->getEmail()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Formate les erreurs de validation
     */
    private function formatValidationErrors($errors): array
    {
        $formatted = [];
        foreach ($errors as $error) {
            $formatted[$error->getPropertyPath()] = $error->getMessage();
        }
        return $formatted;
    }
}
