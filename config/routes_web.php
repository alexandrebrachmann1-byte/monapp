<?php

declare(strict_types=1);

use App\Middleware\AuthMiddleware;
use App\Middleware\LoggingMiddleware;

/**
 * Routes HTML uniquement. CSRF auto sur POST (voir Core\Router).
 *
 * @return list<array{0: string, 1: string, 2: string, 3?: array{middleware?: list<class-string>}}>
 */
return [
    ['GET', '/', 'App\\Web\\Controllers\\HomeController#index', ['middleware' => [LoggingMiddleware::class]]],

    ['GET', '/login', 'App\\Web\\Controllers\\AuthController#showLogin'],
    ['POST', '/login', 'App\\Web\\Controllers\\AuthController#login'],
    ['GET', '/register', 'App\\Web\\Controllers\\AuthController#showRegister'],
    ['POST', '/register', 'App\\Web\\Controllers\\AuthController#register'],
    ['POST', '/logout', 'App\\Web\\Controllers\\AuthController#logout', ['middleware' => [AuthMiddleware::class]]],

    ['GET', '/users', 'App\\Web\\Controllers\\UserController#index', ['middleware' => [AuthMiddleware::class]]],

    ["GET","/teams", "App\\Web\\Controllers\\TeamController#index",["middleware"=> [AuthMiddleware::class]]],
    ["GET","/teams/create", "App\\Web\\Controllers\\TeamController#create",["middleware"=> [AuthMiddleware::class]]],
    ["POST","/teams", "App\\Web\\Controllers\\TeamController#store",["middleware"=> [AuthMiddleware::class]]],
    ["GET","/teams/[i:id]", "App\\Web\\Controllers\\TeamController#show",["middleware"=> [AuthMiddleware::class]]],
    ["POST","/teams/[i:id]/members", "App\\Web\\Controllers\\TeamController#addMember",["middleware"=> [AuthMiddleware::class]]],
    ["POST","/teams/[i:id]/members/[i:userId]/delete", "App\\Web\\Controllers\\TeamController#removeMember",["middleware"=> [AuthMiddleware::class]]],

    ['GET', '/todos', 'App\\Web\\Controllers\\TodoController#index', ['middleware' => [AuthMiddleware::class]]],
    ['GET', '/todos/create', 'App\\Web\\Controllers\\TodoController#create', ['middleware' => [AuthMiddleware::class]]],
    ['POST', '/todos', 'App\\Web\\Controllers\\TodoController#store', ['middleware' => [AuthMiddleware::class]]],
    ['GET', '/todos/[i:id]', 'App\\Web\\Controllers\\TodoController#show', ['middleware' => [AuthMiddleware::class]]],
    ['GET', '/todos/[i:id]/edit', 'App\\Web\\Controllers\\TodoController#edit', ['middleware' => [AuthMiddleware::class]]],
    ['POST', '/todos/[i:id]', 'App\\Web\\Controllers\\TodoController#update', ['middleware' => [AuthMiddleware::class]]],
    ['POST', '/todos/[i:id]/delete', 'App\\Web\\Controllers\\TodoController#destroy', ['middleware' => [AuthMiddleware::class]]],
];
