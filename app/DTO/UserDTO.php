<?php

declare(strict_types=1);

namespace App\DTO;

use Core\DTO;

/**
 * Utilisateur exposé (JSON, Twig). Jamais password_hash :
 * fromArray() ignore les clés absentes des propriétés publiques.
 */
final class UserDTO extends DTO
{
    public int $id;
    public string $username;
    public string $email;
    public ?string $role = null;
    public ?string $job = null;
}
