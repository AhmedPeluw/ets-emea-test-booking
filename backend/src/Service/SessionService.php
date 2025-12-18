<?php

declare(strict_types=1);

namespace App\Service;

use App\Document\Session;
use App\DTO\CreateSessionDTO;
use App\Repository\SessionRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * SessionService - Design Pattern: Service Layer
 * 
 * Service gérant la logique métier des sessions de tests
 */
class SessionService
{
    public function __construct(
        private SessionRepository $sessionRepository
    ) {
    }

    /**
     * Crée une nouvelle session
     */
    public function createSession(CreateSessionDTO $dto): Session
    {
        $session = new Session();
        $this->populateSession($session, $dto);
        
        $this->sessionRepository->save($session);

        return $session;
    }

    /**
     * Met à jour une session existante
     * 
     * @throws NotFoundHttpException si la session n'existe pas
     */
    public function updateSession(string $sessionId, CreateSessionDTO $dto): Session
    {
        $session = $this->sessionRepository->findById($sessionId);

        if (!$session) {
            throw new NotFoundHttpException('Session non trouvée');
        }

        $this->populateSession($session, $dto);
        $this->sessionRepository->save($session);

        return $session;
    }

    /**
     * Remplit une session avec les données du DTO
     */
    private function populateSession(Session $session, CreateSessionDTO $dto): void
    {
        $session->setLanguage($dto->language);
        $session->setDate(new \DateTime($dto->date));
        $session->setTime($dto->time);
        $session->setLocation($dto->location);
        $session->setTotalSeats($dto->totalSeats);
        
        if ($dto->description !== null) {
            $session->setDescription($dto->description);
        }
        
        if ($dto->level !== null) {
            $session->setLevel($dto->level);
        }
        
        if ($dto->durationMinutes !== null) {
            $session->setDurationMinutes($dto->durationMinutes);
        }
        
        if ($dto->price !== null) {
            $session->setPrice($dto->price);
        }
    }

    /**
     * Récupère une session par son ID
     * 
     * @throws NotFoundHttpException si la session n'existe pas
     */
    public function getSession(string $sessionId): Session
    {
        $session = $this->sessionRepository->findById($sessionId);

        if (!$session) {
            throw new NotFoundHttpException('Session non trouvée');
        }

        return $session;
    }

    /**
     * Liste les sessions disponibles avec pagination
     */
    public function listAvailableSessions(
        int $page = 1,
        int $itemsPerPage = 10,
        ?string $language = null,
        ?string $level = null
    ): array {
        return $this->sessionRepository->findAvailableWithPagination(
            $page,
            $itemsPerPage,
            $language,
            $level
        );
    }

    /**
     * Liste toutes les sessions avec pagination
     */
    public function listAllSessions(int $page = 1, int $itemsPerPage = 10): array
    {
        return $this->sessionRepository->findAllWithPagination($page, $itemsPerPage);
    }

    /**
     * Récupère les sessions à venir
     */
    public function getUpcomingSessions(int $limit = 10): array
    {
        return $this->sessionRepository->findUpcoming($limit);
    }

    /**
     * Supprime une session
     * 
     * @throws NotFoundHttpException si la session n'existe pas
     */
    public function deleteSession(string $sessionId): void
    {
        $session = $this->sessionRepository->findById($sessionId);

        if (!$session) {
            throw new NotFoundHttpException('Session non trouvée');
        }

        $this->sessionRepository->remove($session);
    }

    /**
     * Vérifie si une session a des places disponibles
     */
    public function hasAvailableSeats(Session $session): bool
    {
        return $session->hasAvailableSeats();
    }

    /**
     * Décrémente les places disponibles d'une session
     */
    public function decrementSeats(Session $session): void
    {
        $session->decrementAvailableSeats();
        $this->sessionRepository->save($session);
    }

    /**
     * Incrémente les places disponibles d'une session
     */
    public function incrementSeats(Session $session): void
    {
        $session->incrementAvailableSeats();
        $this->sessionRepository->save($session);
    }

    /**
     * Compte les sessions disponibles
     */
    public function countAvailableSessions(): int
    {
        return $this->sessionRepository->countAvailable();
    }
}
