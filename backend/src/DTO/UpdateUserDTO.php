<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * UpdateUserDTO - Design Pattern: DTO
 * 
 * Objet de transfert pour la mise à jour du profil utilisateur
 */
class UpdateUserDTO
{
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    public ?string $name = null;

    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide')]
    public ?string $email = null;

    #[Assert\Length(
        min: 8,
        max: 100,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        message: 'Le mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre'
    )]
    public ?string $password = null;

    public ?string $currentPassword = null;
}
