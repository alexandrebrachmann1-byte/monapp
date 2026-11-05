<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Tâche de la todo list globale.
 */
final class Todo extends Model
{
    protected static string $table = 'todos';
}
