<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

use Core\Env;

$testing = BASE_PATH . '/.env.testing';
$example = BASE_PATH . '/.env.testing.example';

Env::load(is_file($testing) ? $testing : $example);
