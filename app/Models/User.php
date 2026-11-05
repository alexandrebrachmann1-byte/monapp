<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Compte utilisateur (authentification). Pas de CRUD public.
 * Ne jamais envoyer password_hash à Twig ni en JSON.
 */
final class User extends Model
{
    protected static string $table = 'users';
}
