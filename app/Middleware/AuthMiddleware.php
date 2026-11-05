<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Auth;
use Core\Env;
use Core\HttpException;
use Core\Middleware;
use Core\Request;

/**
 * Protège une route : session web ou jeton Bearer API.
 * Web : redirection vers /login. API : 401 JSON.
 */
final class AuthMiddleware implements Middleware
{
    public function __construct(private Auth $auth)
    {
    }

    public function handle(Request $request, callable $next): mixed
    {
        if ($request->isApi()) {
            $header = (string) $request->header('Authorization');
            if (!str_starts_with($header, 'Bearer ')) {
                throw new HttpException(401, 'Jeton Bearer manquant ou invalide.');
            }
            $plain = substr($header, 7);
            if ($this->auth->userFromToken($plain) === null) {
                throw new HttpException(401, 'Jeton Bearer manquant ou invalide.');
            }

            return $next($request);
        }

        if (!$this->auth->check()) {
            $base = Env::basePath();
            header('Location: ' . $base . '/login');
            exit;
        }

        return $next($request);
    }
}
