<?php

declare(strict_types=1);

namespace Console\Commands;

use Core\Console\Command;

final class MakeViewCommand extends Command
{
    public function handle(array $arguments): int
    {
        $path = $this->positional($arguments)[0] ?? null;
        if ($path === null) {
            $this->error('Usage : php framework make:view <chemin>   (ex. users/index)');

            return 1;
        }

        $path = str_replace('\\', '/', $path);
        if (!str_ends_with($path, '.twig')) {
            $path .= '.twig';
        }

        $contents = $this->stub('view', [
            '{{title}}' => $path,
        ]);
        $this->write(BASE_PATH . '/app/Web/Views/' . $path, $contents);

        return 0;
    }
}
