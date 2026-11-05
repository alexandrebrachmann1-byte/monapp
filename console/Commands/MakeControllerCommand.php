<?php

declare(strict_types=1);

namespace Console\Commands;

use Core\Console\Command;

final class MakeControllerCommand extends Command
{
    public function handle(array $arguments): int
    {
        $name = $this->positional($arguments)[0] ?? null;
        if ($name === null) {
            $this->error('Usage : php framework make:controller <Nom> [--api]');

            return 1;
        }

        $class = $this->studly($name);
        if (!str_ends_with($class, 'Controller')) {
            $class .= 'Controller';
        }

        $api = $this->option($arguments, 'api') !== null;
        if ($api) {
            $contents = $this->stub('controller-api', [
                '{{class}}' => $class,
            ]);
            $this->write(BASE_PATH . '/app/Api/Controllers/' . $class . '.php', $contents);
        } else {
            $contents = $this->stub('controller-web', [
                '{{class}}' => $class,
            ]);
            $this->write(BASE_PATH . '/app/Web/Controllers/' . $class . '.php', $contents);
        }

        return 0;
    }
}
