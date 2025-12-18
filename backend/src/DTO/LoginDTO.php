<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * LoginDTO - Design Pattern: DTO
 * 
 * Objet de transfert pour l'authentification
 */
class LoginDTO
{
    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide')]
    public string $email;

    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire')]
    public string $password;
}
