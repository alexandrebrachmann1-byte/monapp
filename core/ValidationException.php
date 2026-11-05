<?php

declare(strict_types=1);

namespace Core;

/**
 * Échec de fromArray() : champs manquants ou mauvais type.
 * $errors = ['email' => 'Le champ email est requis.', ...]
 */
class ValidationException extends \RuntimeException
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(
        public readonly array $errors,
    ) {
        parent::__construct('Validation échouée');
    }
}
