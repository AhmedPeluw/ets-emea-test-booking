<?php

declare(strict_types=1);

namespace App\Service;

use App\Document\Booking;
use App\Document\User;
use App\DTO\CreateBookingDTO;
use App\Repository\BookingRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * BookingService - Design Pattern: Service Layer + Transaction Script
 * 
 * Service gérant la logique métier complexe des réservations
 */
class BookingService
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private SessionService $sessionService
    ) {
    }

    /**
     * Crée une nouvelle réservation
     * 
     * @throws BadRequestHttpException si la réservation est impossible
     * @throws NotFoundHttpException si la session n'existe pas
     */
    public function createBooking(User $user, CreateBookingDTO $dto): Booking
    {
        // Récupérer la session
        $session = $this->sessionService->getSession($dto->sessionId);

        // Vérifications métier
        $this->validateBooking($user, $session);

        // Créer la réservation
        $booking = new Booking();
        $booking->setUserId($user->getId());
        $booking->setSessionId($session->getId());
        $booking->setStatus('confirmed');
        $booking->setSession($session);
        $booking->setUser($user);

        // Décrémenter les places disponibles
        $this->sessionService->decrementSeats($session);

        // Sauvegarder
        $this->bookingRepository->save($booking);

        return $booking;
    }

    /**
     * Valide les conditions de réservation
     * 
     * @throws BadRequestHttpException si une condition n'est pas respectée
     */
    private function validateBooking(User $user, $session): void
    {
        // Vérifier si la session est dans le passé
        if ($session->isPast()) {
            throw new BadRequestHttpException('Cette session est déjà passée');
        }

        // Vérifier si la session est active
        if (!$session->isActive()) {
            throw new BadRequestHttpException('Cette session n\'est pas disponible');
        }

        // Vérifier s'il reste des places
        if (!$session->hasAvailableSeats()) {
            throw new BadRequestHttpException('Il n\'y a plus de places disponibles pour cette session');
        }

        // Vérifier si l'utilisateur a déjà réservé cette session
        if ($this->bookingRepository->userHasBookedSession($user->getId(), $session->getId())) {
            throw new BadRequestHttpException('Vous avez déjà réservé cette session');
        }
    }

    /**
     * Annule une réservation
     * 
     * @throws NotFoundHttpException si la réservation n'existe pas
     * @throws BadRequestHttpException si l'annulation est impossible
     */
    public function cancelBooking(User $user, string $bookingId, ?string $reason = null): Booking
    {
        // Trouver la réservation
        $booking = $this->bookingRepository->findByIdAndUser($bookingId, $user->getId());

        if (!$booking) {
            throw new NotFoundHttpException('Réservation non trouvée');
        }

        // Vérifier si la réservation peut être annulée
        if ($booking->isCancelled()) {
            throw new BadRequestHttpException('Cette réservation est déjà annulée');
        }

        // Récupérer la session
        $session = $this->sessionService->getSession($booking->getSessionId());

        // Annuler la réservation
        $booking->cancel($reason);

        // Incrémenter les places disponibles
        $this->sessionService->incrementSeats($session);

        // Sauvegarder
        $this->bookingRepository->save($booking);

        return $booking;
    }

    /**
     * Récupère les réservations d'un utilisateur
     */
    public function getUserBookings(User $user, int $page = 1, int $itemsPerPage = 10): array
    {
        $result = $this->bookingRepository->findByUserWithPagination(
            $user->getId(),
            $page,
            $itemsPerPage
        );

        // Enrichir les réservations avec les sessions
        foreach ($result['items'] as $booking) {
            try {
                $session = $this->sessionService->getSession($booking->getSessionId());
                $booking->setSession($session);
            } catch (NotFoundHttpException $e) {
                // Session supprimée, ne rien faire
            }
        }

        return $result;
    }

    /**
     * Récupère une réservation spécifique de l'utilisateur
     * 
     * @throws NotFoundHttpException si la réservation n'existe pas
     */
    public function getUserBooking(User $user, string $bookingId): Booking
    {
        $booking = $this->bookingRepository->findByIdAndUser($bookingId, $user->getId());

        if (!$booking) {
            throw new NotFoundHttpException('Réservation non trouvée');
        }

        // Enrichir avec la session
        try {
            $session = $this->sessionService->getSession($booking->getSessionId());
            $booking->setSession($session);
        } catch (NotFoundHttpException $e) {
            // Session supprimée
        }

        return $booking;
    }

    /**
     * Récupère les réservations actives d'un utilisateur
     */
    public function getUserActiveBookings(User $user): array
    {
        $bookings = $this->bookingRepository->findActiveByUser($user->getId());

        // Enrichir avec les sessions
        foreach ($bookings as $booking) {
            try {
                $session = $this->sessionService->getSession($booking->getSessionId());
                $booking->setSession($session);
            } catch (NotFoundHttpException $e) {
                // Session supprimée
            }
        }

        return $bookings;
    }

    /**
     * Récupère les réservations d'une session
     */
    public function getSessionBookings(string $sessionId): array
    {
        return $this->bookingRepository->findBySession($sessionId);
    }

    /**
     * Compte les réservations d'une session
     */
    public function countSessionBookings(string $sessionId): int
    {
        return $this->bookingRepository->countBySession($sessionId);
    }

    /**
     * Vérifie si un utilisateur a réservé une session
     */
    public function hasUserBookedSession(User $user, string $sessionId): bool
    {
        return $this->bookingRepository->userHasBookedSession($user->getId(), $sessionId);
    }
}
