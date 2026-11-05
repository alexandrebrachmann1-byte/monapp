<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Csrf;
use Core\HttpException;
use Core\Middleware;
use Core\Request;

/**
 * Vérifie le jeton CSRF sur les formulaires web.
 * Appliqué automatiquement par le routeur sur POST/PUT/PATCH/DELETE hors /api.
 */
final class CsrfMiddleware implements Middleware
{
    public function __construct(private Csrf $csrf)
    {
    }

    public function handle(Request $request, callable $next): mixed
    {
        $token = $request->input('_csrf');
        if (is_string($token) === false || $this->csrf->verify($token) === false) {
            throw new HttpException(419, 'Jeton CSRF invalide ou manquant.');
        }

        return $next($request);
    }
}
