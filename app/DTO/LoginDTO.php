<?php

declare(strict_types=1);

namespace App\DTO;

use Core\DTO;

/** Données du formulaire / JSON de connexion. */
final class LoginDTO extends DTO
{
    public string $email;
    public string $password;
}
