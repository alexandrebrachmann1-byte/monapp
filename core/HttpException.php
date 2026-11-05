<?php

declare(strict_types=1);

namespace Core;

/**
 * Exception HTTP métier (401, 419, 500…).
 * Router::handleException lit $status pour le code de réponse (HTML ou JSON /api).
 */
class HttpException extends \RuntimeException
{
    /**
     * @param int $status code HTTP (401, 419…)
     */
    public function __construct(
        public readonly int $status,
        string $message,
    ) {
        parent::__construct($message, $status);
    }
}
