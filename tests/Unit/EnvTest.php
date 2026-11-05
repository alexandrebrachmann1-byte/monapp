<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Env;
use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase
{
    public function testParsesQuotedValuesAndIgnoresComments(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($file, "FOO=bar\n# comment\nBAZ=\"quoted value\"\n");

        $method = new \ReflectionClass(Env::class);
        $vars = $method->getProperty('vars');
        $vars->setAccessible(true);
        $loaded = $method->getProperty('loaded');
        $loaded->setAccessible(true);
        $previousVars = $vars->getValue();
        $previousLoaded = $loaded->getValue();

        try {
            Env::load($file);

            $this->assertSame('bar', Env::get('FOO'));
            $this->assertSame('quoted value', Env::get('BAZ'));
            $this->assertSame('fallback', Env::get('MISSING', 'fallback'));
        } finally {
            $vars->setValue(null, $previousVars);
            $loaded->setValue(null, $previousLoaded);
            unlink($file);
        }
    }
}
