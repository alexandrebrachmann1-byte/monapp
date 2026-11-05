<?php

declare(strict_types=1);

namespace Console\Commands;

use Core\Console\Command;
use Core\Database;
use Core\MigrationRunner;

final class MigrateCommand extends Command
{
    public function handle(array $arguments): int
    {
        $runner = new MigrationRunner(Database::connection());
        $executed = $runner->migrate();

        if ($executed === []) {
            $this->line('Aucune migration en attente.');

            return 0;
        }

        foreach ($executed as $file) {
            $this->line('Migrée : ' . $file);
        }

        return 0;
    }
}
