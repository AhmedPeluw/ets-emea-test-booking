<?php

declare(strict_types=1);

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Session Document - Représente une session de test de langue
 * 
 * Design Pattern: Value Object avec validation métier
 */
#[MongoDB\Document(collection: 'sessions')]
#[MongoDB\Index(keys: ['language' => 'asc', 'date' => 'desc'])]
#[MongoDB\Index(keys: ['date' => 'desc'])]
class Session
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: 'La langue est obligatoire')]
    #[Assert\Choice(
        choices: ['Anglais', 'Français', 'Espagnol', 'Allemand', 'Italien', 'Portugais', 'Chinois', 'Japonais', 'Arabe'],
        message: 'La langue {{ value }} n\'est pas supportée'
    )]
    private string $language;

    #[MongoDB\Field(type: 'date')]
    #[Assert\NotNull(message: 'La date est obligatoire')]
    #[Assert\GreaterThanOrEqual(
        value: 'today',
        message: 'La date doit être dans le futur'
    )]
    private \DateTime $date;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: 'L\'heure est obligatoire')]
    #[Assert\Regex(
        pattern: '/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/',
        message: 'L\'heure doit être au format HH:MM'
    )]
    private string $time;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Le lieu est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 200,
        minMessage: 'Le lieu doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le lieu ne peut pas dépasser {{ limit }} caractères'
    )]
    private string $location;

    #[MongoDB\Field(type: 'int')]
    #[Assert\NotNull(message: 'Le nombre de places est obligatoire')]
    #[Assert\Positive(message: 'Le nombre de places doit être positif')]
    #[Assert\LessThanOrEqual(
        value: 100,
        message: 'Le nombre de places ne peut pas dépasser {{ compared_value }}'
    )]
    private int $totalSeats;

    #[MongoDB\Field(type: 'int')]
    private int $availableSeats;

    #[MongoDB\Field(type: 'string')]
    #[Assert\Length(max: 1000)]
    private ?string $description = null;

    #[MongoDB\Field(type: 'string')]
    #[Assert\Choice(
        choices: ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'],
        message: 'Le niveau {{ value }} n\'est pas valide'
    )]
    private ?string $level = null;

    #[MongoDB\Field(type: 'int')]
    #[Assert\Positive]
    private ?int $durationMinutes = 120;

    #[MongoDB\Field(type: 'float')]
    #[Assert\PositiveOrZero]
    private ?float $price = 0.0;

    #[MongoDB\Field(type: 'bool')]
    private bool $isActive = true;

    #[MongoDB\Field(type: 'date')]
    private \DateTime $createdAt;

    #[MongoDB\Field(type: 'date')]
    private \DateTime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getTime(): string
    {
        return $this->time;
    }

    public function setTime(string $time): self
    {
        $this->time = $time;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getTotalSeats(): int
    {
        return $this->totalSeats;
    }

    public function setTotalSeats(int $totalSeats): self
    {
        $this->totalSeats = $totalSeats;
        $this->availableSeats = $totalSeats;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getAvailableSeats(): int
    {
        return $this->availableSeats;
    }

    public function setAvailableSeats(int $availableSeats): self
    {
        $this->availableSeats = $availableSeats;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function hasAvailableSeats(): bool
    {
        return $this->availableSeats > 0;
    }

    public function decrementAvailableSeats(): void
    {
        if ($this->availableSeats > 0) {
            $this->availableSeats--;
            $this->updatedAt = new \DateTime();
        }
    }

    public function incrementAvailableSeats(): void
    {
        if ($this->availableSeats < $this->totalSeats) {
            $this->availableSeats++;
            $this->updatedAt = new \DateTime();
        }
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): self
    {
        $this->level = $level;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(?int $durationMinutes): self
    {
        $this->durationMinutes = $durationMinutes;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Retourne la date et l'heure complète de la session
     */
    public function getDateTime(): \DateTime
    {
        $dateTime = clone $this->date;
        [$hours, $minutes] = explode(':', $this->time);
        $dateTime->setTime((int)$hours, (int)$minutes);
        return $dateTime;
    }

    /**
     * Vérifie si la session est passée
     */
    public function isPast(): bool
    {
        return $this->getDateTime() < new \DateTime();
    }
}
