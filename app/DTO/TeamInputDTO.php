<?php

declare(strict_types=1);

namespace App\DTO;

use Core\DTO;

/** Données du formulaire / JSON d'une tâche. */
final class TeamInputDTO extends DTO
{
    public string $name;
  
}
