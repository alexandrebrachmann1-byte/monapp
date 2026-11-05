<?php

declare(strict_types=1);

namespace Console\Commands;

use Core\Console\Command;

final class MakeMigrationCommand extends Command
{
    public function handle(array $arguments): int
    {
        $name = $this->positional($arguments)[0] ?? null;
        if ($name === null) {
            $this->error('Usage : php framework make:migration <nom>');

            return 1;
        }

        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $name) ?? $name);
        $filename = date('YmdHis') . '_' . $slug . '.php';
        $contents = $this->stub('migration', []);
        $this->write(BASE_PATH . '/database/migrations/' . $filename, $contents);

        return 0;
    }
}
