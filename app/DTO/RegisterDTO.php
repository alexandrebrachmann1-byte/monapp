<?php

declare(strict_types=1);

namespace App\DTO;

use Core\DTO;

/** Données du formulaire / JSON d'inscription. */
final class RegisterDTO extends DTO
{
    public string $username;
    public string $email;
    public string $password;
}
