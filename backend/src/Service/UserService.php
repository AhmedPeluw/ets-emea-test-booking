<?php

declare(strict_types=1);

namespace App\Service;

use App\Document\User;
use App\DTO\UpdateUserDTO;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * UserService - Design Pattern: Service Layer
 * 
 * Service gérant la logique métier des utilisateurs
 */
class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * Met à jour le profil d'un utilisateur
     * 
     * @throws BadRequestHttpException si les données sont invalides
     */
    public function updateProfile(User $user, UpdateUserDTO $dto): User
    {
        // Mise à jour du nom
        if ($dto->name !== null) {
            $user->setName($dto->name);
        }

        // Mise à jour de l'email
        if ($dto->email !== null && $dto->email !== $user->getEmail()) {
            // Vérifier si le nouvel email existe déjà
            if ($this->userRepository->emailExists($dto->email, $user->getId())) {
                throw new BadRequestHttpException('Cet email est déjà utilisé');
            }
            $user->setEmail($dto->email);
        }

        // Mise à jour du mot de passe
        if ($dto->password !== null) {
            // Vérifier le mot de passe actuel si fourni
            if ($dto->currentPassword !== null) {
                if (!$this->passwordHasher->isPasswordValid($user, $dto->currentPassword)) {
                    throw new BadRequestHttpException('Le mot de passe actuel est incorrect');
                }
            }

            $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
            $user->setPassword($hashedPassword);
        }

        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Récupère un utilisateur par son ID
     */
    public function getUser(string $userId): ?User
    {
        return $this->userRepository->find($userId);
    }

    /**
     * Récupère un utilisateur par son email
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Supprime un utilisateur
     */
    public function deleteUser(User $user): void
    {
        $this->userRepository->remove($user);
    }

    /**
     * Liste tous les utilisateurs
     */
    public function listUsers(): array
    {
        return $this->userRepository->findAll();
    }
}
