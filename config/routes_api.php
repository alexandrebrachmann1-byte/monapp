<?php

declare(strict_types=1);

use App\Middleware\AuthMiddleware;
use App\Middleware\CorsMiddleware;
use App\Middleware\LoggingMiddleware;

/**
 * Routes JSON uniquement (préfixe /api). Pas de CSRF : jeton Bearer.
 *
 * @return list<array{0: string, 1: string, 2: string, 3?: array{middleware?: list<class-string>}}>
 */
return [
    ['POST', '/api/register', 'App\\Api\\Controllers\\AuthController#register', ['middleware' => [CorsMiddleware::class]]],
    ['POST', '/api/login', 'App\\Api\\Controllers\\AuthController#login', ['middleware' => [CorsMiddleware::class]]],

    ['GET', '/api/todos', 'App\\Api\\Controllers\\TodoController#index', ['middleware' => [CorsMiddleware::class, AuthMiddleware::class, LoggingMiddleware::class]]],
    ['GET', '/api/todos/[i:id]', 'App\\Api\\Controllers\\TodoController#show', ['middleware' => [CorsMiddleware::class, AuthMiddleware::class]]],
    ['POST', '/api/todos', 'App\\Api\\Controllers\\TodoController#store', ['middleware' => [CorsMiddleware::class, AuthMiddleware::class]]],
    ['PUT', '/api/todos/[i:id]', 'App\\Api\\Controllers\\TodoController#update', ['middleware' => [CorsMiddleware::class, AuthMiddleware::class]]],
    ['PATCH', '/api/todos/[i:id]', 'App\\Api\\Controllers\\TodoController#update', ['middleware' => [CorsMiddleware::class, AuthMiddleware::class]]],
    ['DELETE', '/api/todos/[i:id]', 'App\\Api\\Controllers\\TodoController#destroy', ['middleware' => [CorsMiddleware::class, AuthMiddleware::class]]],
];
