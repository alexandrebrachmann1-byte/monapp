<?php

declare(strict_types=1);

namespace App\DTO;

use Core\DTO;

/**
 * Utilisateur exposé (JSON, Twig). Jamais password_hash :
 * fromArray() ignore les clés absentes des propriétés publiques.
 */
final class TeamDTO extends DTO
{
    public int $id;
    public string $name;
  //  public int $dockyard_id;

}
