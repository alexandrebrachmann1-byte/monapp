<?php

declare(strict_types=1);

use Core\Env;

/**
 * Paramètres PDO. Toutes les valeurs viennent de .env (via Env::get).
 * Défauts = installation XAMPP typique (root, mot de passe vide).
 */
return [
    'host' => Env::get('DB_HOST', '127.0.0.1'),
    'port' => Env::get('DB_PORT', '3306'),
    'name' => Env::get('DB_NAME', 'framework'),
    'user' => Env::get('DB_USER', 'root'),
    'pass' => Env::get('DB_PASS', ''),
];
