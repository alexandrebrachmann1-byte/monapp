<?php

declare(strict_types=1);

/**
 * Front controller HTTP (point d'entrée unique).
 *
 * Apache / XAMPP réécrit toutes les URL vers ce fichier (voir public/.htaccess).
 * Ordre volontairement linéaire pour l'enseignement : Env → session → conteneur → router.
 */

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

use Core\Container;
use Core\Env;
use Core\Request;
use Core\Router;

// 1. Variables d'environnement avant tout le reste (DSN, APP_BASE_PATH).
Env::load(BASE_PATH . '/.env');

// 2. Session PHP native (flash, CSRF, AuthMiddleware web).
session_start();

// 3. Conteneur + bindings explicites (config/services.php).
$container = new Container();
$register = require BASE_PATH . '/config/services.php';
$register($container);

/** @var Request $request */
$request = $container->make(Request::class);

// 4. Erreurs PHP → exceptions ; exceptions → page HTML ou JSON /api.
set_exception_handler(static function (Throwable $e) use ($request): void {
    Router::handleException($e, $request);
});

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// 5. Match de route + middlewares + contrôleur.
$container->make(Router::class)->dispatch();
