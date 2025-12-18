<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * CreateSessionDTO - Design Pattern: DTO
 * 
 * Objet de transfert pour la création/modification d'une session
 */
class CreateSessionDTO
{
    #[Assert\NotBlank(message: 'La langue est obligatoire')]
    #[Assert\Choice(
        choices: ['Anglais', 'Français', 'Espagnol', 'Allemand', 'Italien', 'Portugais', 'Chinois', 'Japonais', 'Arabe'],
        message: 'La langue {{ value }} n\'est pas supportée'
    )]
    public string $language;

    #[Assert\NotBlank(message: 'La date est obligatoire')]
    #[Assert\Date(message: 'La date n\'est pas valide')]
    public string $date;

    #[Assert\NotBlank(message: 'L\'heure est obligatoire')]
    #[Assert\Regex(
        pattern: '/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/',
        message: 'L\'heure doit être au format HH:MM'
    )]
    public string $time;

    #[Assert\NotBlank(message: 'Le lieu est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 200,
        minMessage: 'Le lieu doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le lieu ne peut pas dépasser {{ limit }} caractères'
    )]
    public string $location;

    #[Assert\NotNull(message: 'Le nombre de places est obligatoire')]
    #[Assert\Positive(message: 'Le nombre de places doit être positif')]
    #[Assert\LessThanOrEqual(
        value: 100,
        message: 'Le nombre de places ne peut pas dépasser {{ compared_value }}'
    )]
    public int $totalSeats;

    #[Assert\Length(max: 1000)]
    public ?string $description = null;

    #[Assert\Choice(
        choices: ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'],
        message: 'Le niveau {{ value }} n\'est pas valide'
    )]
    public ?string $level = null;

    #[Assert\Positive]
    public ?int $durationMinutes = 120;

    #[Assert\PositiveOrZero]
    public ?float $price = 0.0;
}
