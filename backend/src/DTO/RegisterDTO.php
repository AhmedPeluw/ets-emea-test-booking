<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * RegisterDTO - Design Pattern: DTO (Data Transfer Object)
 * 
 * Objet de transfert pour l'inscription d'un utilisateur
 */
class RegisterDTO
{
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    public string $name;

    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide')]
    public string $email;

    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire')]
    #[Assert\Length(
        min: 8,
        max: 100,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le mot de passe ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        message: 'Le mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre'
    )]
    public string $password;

    #[Assert\NotBlank(message: 'La confirmation du mot de passe est obligatoire')]
    #[Assert\EqualTo(
        propertyPath: 'password',
        message: 'Les mots de passe ne correspondent pas'
    )]
    public string $confirmPassword;
}
