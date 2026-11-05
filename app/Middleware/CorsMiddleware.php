<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Middleware;
use Core\Request;

/** CORS pour les routes API uniquement. */
final class CorsMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): mixed
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

        if ($request->method() === 'OPTIONS') {
            http_response_code(204);

            return null;
        }

        return $next($request);
    }
}
