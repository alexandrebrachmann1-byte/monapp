<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Ligne de la table api_tokens (hash SHA-256 du jeton Bearer).
 */
final class ApiToken extends Model
{
    protected static string $table = 'api_tokens';
}
