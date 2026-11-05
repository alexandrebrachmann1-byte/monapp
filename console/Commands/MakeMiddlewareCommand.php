<?php

declare(strict_types=1);

namespace Console\Commands;

use Core\Console\Command;

final class MakeMiddlewareCommand extends Command
{
    public function handle(array $arguments): int
    {
        $name = $this->positional($arguments)[0] ?? null;
        if ($name === null) {
            $this->error('Usage : php framework make:middleware <Nom>');

            return 1;
        }

        $class = $this->studly($name);
        if (!str_ends_with($class, 'Middleware')) {
            $class .= 'Middleware';
        }

        $contents = $this->stub('middleware', [
            '{{class}}' => $class,
        ]);
        $this->write(BASE_PATH . '/app/Middleware/' . $class . '.php', $contents);

        return 0;
    }
}
