<?php

declare(strict_types=1);

namespace Core;

/**
 * Contrat des middlewares applicatifs.
 *
 * handle() doit appeler $next($request) pour continuer, ou court-circuiter
 * (ex. 401, 419). Voir Router::dispatch() pour l'assemblage de la chaîne.
 */
interface Middleware
{
    /**
     * @param callable(Request): mixed $next Contrôleur ou middleware suivant
     */
    public function handle(Request $request, callable $next): mixed;
}
