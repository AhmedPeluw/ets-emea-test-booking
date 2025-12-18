<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * CreateBookingDTO - Design Pattern: DTO
 * 
 * Objet de transfert pour la création d'une réservation
 */
class CreateBookingDTO
{
    #[Assert\NotBlank(message: 'L\'ID de la session est obligatoire')]
    public string $sessionId;
}
