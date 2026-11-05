<?php

declare(strict_types=1);

namespace Tests\Unit;

use AltoRouter;
use PHPUnit\Framework\TestCase;

final class RoutesTest extends TestCase
{
    public function testDeclaredRoutesMatch(): void
    {
        $web = require BASE_PATH . '/config/routes_web.php';
        $api = require BASE_PATH . '/config/routes_api.php';
        $alto = new AltoRouter();
        foreach (array_merge($web, $api) as $route) {
            $alto->map($route[0], $route[1], $route[2]);
        }

        $this->assertNotFalse($alto->match('/', 'GET'));
        $this->assertNotFalse($alto->match('/login', 'GET'));
        $this->assertNotFalse($alto->match('/users', 'GET'));
        $this->assertNotFalse($alto->match('/todos', 'GET'));
        $this->assertNotFalse($alto->match('/todos/12', 'GET'));
        $this->assertNotFalse($alto->match('/api/login', 'POST'));
        $this->assertNotFalse($alto->match('/api/todos/4', 'PUT'));
        $this->assertNotFalse($alto->match('/api/todos/4', 'DELETE'));
        $this->assertFalse($alto->match('/nope', 'GET'));
    }
}
