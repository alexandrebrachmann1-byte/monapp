<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Env;
use Core\Request;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class RequestTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->setEnv('APP_BASE_PATH', '');
        parent::tearDown();
    }

    public function testPathStripsXamppBaseAndPublic(): void
    {
        $this->setEnv('APP_BASE_PATH', '/framework');

        $request = new Request([], [], [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/framework/public/users/3?x=1',
        ]);

        $this->assertSame('/users/3', $request->path());
        $this->assertSame('GET', $request->method());
    }

    public function testPathAlsoStripsBasePathWithoutLeadingSlash(): void
    {
        $this->setEnv('APP_BASE_PATH', 'monapp');

        $request = new Request([], [], [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/monapp/public/users/3?x=1',
        ]);

        $this->assertSame('/users/3', $request->path());
    }

    private function setEnv(string $key, string $value): void
    {
        $reflection = new ReflectionClass(Env::class);
        $vars = $reflection->getProperty('vars');
        $vars->setAccessible(true);
        $current = $vars->getValue();
        $current[$key] = $value;
        $vars->setValue(null, $current);
    }
}
