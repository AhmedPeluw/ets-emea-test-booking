<?php

declare(strict_types=1);

namespace App\Service;

use App\Document\User;
use App\DTO\LoginDTO;
use App\DTO\RegisterDTO;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * AuthService - Design Pattern: Service Layer
 * 
 * Service gérant toute la logique métier d'authentification
 */
class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager
    ) {
    }

    /**
     * Inscrit un nouvel utilisateur
     * 
     * @throws BadRequestHttpException si l'email existe déjà
     */
    public function register(RegisterDTO $dto): User
    {
        // Vérifier si l'email existe déjà
        if ($this->userRepository->emailExists($dto->email)) {
            throw new BadRequestHttpException('Cet email est déjà utilisé');
        }

        // Créer l'utilisateur
        $user = new User();
        $user->setName($dto->name);
        $user->setEmail($dto->email);
        
        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
        $user->setPassword($hashedPassword);

        // Sauvegarder
        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Authentifie un utilisateur et génère un token JWT
     * 
     * @throws UnauthorizedHttpException si les identifiants sont invalides
     */
    public function login(LoginDTO $dto): array
    {
        // Trouver l'utilisateur
        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user) {
            throw new UnauthorizedHttpException('', 'Identifiants invalides');
        }

        // Vérifier le mot de passe
        if (!$this->passwordHasher->isPasswordValid($user, $dto->password)) {
            throw new UnauthorizedHttpException('', 'Identifiants invalides');
        }

        // Générer le token JWT
        $token = $this->jwtManager->create($user);

        return [
            'token' => $token,
            'user' => $user
        ];
    }

    /**
     * Valide et décode un token JWT
     */
    public function validateToken(string $token): ?User
    {
        try {
            $payload = $this->jwtManager->parse($token);
            $email = $payload['email'] ?? $payload['username'] ?? null;

            if (!$email) {
                return null;
            }

            return $this->userRepository->findByEmail($email);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Génère un nouveau token pour un utilisateur
     */
    public function refreshToken(User $user): string
    {
        return $this->jwtManager->create($user);
    }
}
