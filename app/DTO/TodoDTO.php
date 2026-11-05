<?php

declare(strict_types=1);

namespace App\DTO;

use Core\DTO;

/**
 * Représentation publique d'une tâche (API JSON et vues Twig).
 * Pas de colonnes internes « de trop » : le contrat est cette classe.
 */
final class TodoDTO extends DTO
{
    public int $id;
    public string $title;
    public bool $is_done;
    public string $created_at;
}
