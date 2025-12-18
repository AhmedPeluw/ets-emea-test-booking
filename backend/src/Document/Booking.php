<?php

declare(strict_types=1);

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Booking Document - Représente une réservation
 * 
 * Design Pattern: Aggregate Root avec gestion de la relation
 */
#[MongoDB\Document(collection: 'bookings')]
#[MongoDB\Index(keys: ['userId' => 'asc', 'sessionId' => 'asc'], options: ['unique' => true])]
#[MongoDB\Index(keys: ['userId' => 'asc', 'createdAt' => 'desc'])]
#[MongoDB\Index(keys: ['sessionId' => 'asc'])]
class Booking
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: 'L\'ID de session est obligatoire')]
    private string $sessionId;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: 'L\'ID utilisateur est obligatoire')]
    private string $userId;

    #[MongoDB\Field(type: 'string')]
    #[Assert\Choice(
        choices: ['pending', 'confirmed', 'cancelled', 'completed'],
        message: 'Le statut {{ value }} n\'est pas valide'
    )]
    private string $status = 'confirmed';

    #[MongoDB\Field(type: 'string')]
    private ?string $cancellationReason = null;

    #[MongoDB\Field(type: 'date')]
    private ?\DateTime $cancelledAt = null;

    #[MongoDB\Field(type: 'date')]
    private \DateTime $createdAt;

    #[MongoDB\Field(type: 'date')]
    private \DateTime $updatedAt;

    // Relations (non persisted, loaded separately)
    private ?Session $session = null;
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): self
    {
        $this->sessionId = $sessionId;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getCancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    public function setCancellationReason(?string $cancellationReason): self
    {
        $this->cancellationReason = $cancellationReason;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getCancelledAt(): ?\DateTime
    {
        return $this->cancelledAt;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;
        if ($session) {
            $this->sessionId = $session->getId();
        }
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        if ($user) {
            $this->userId = $user->getId();
        }
        return $this;
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Annule la réservation
     */
    public function cancel(?string $reason = null): void
    {
        $this->status = 'cancelled';
        $this->cancellationReason = $reason;
        $this->cancelledAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * Confirme la réservation
     */
    public function confirm(): void
    {
        $this->status = 'confirmed';
        $this->updatedAt = new \DateTime();
    }

    /**
     * Marque la réservation comme complétée
     */
    public function complete(): void
    {
        $this->status = 'completed';
        $this->updatedAt = new \DateTime();
    }
}
