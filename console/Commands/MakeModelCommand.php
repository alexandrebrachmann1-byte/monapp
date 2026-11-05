<?php

declare(strict_types=1);

namespace Console\Commands;

use Core\Console\Command;

final class MakeModelCommand extends Command
{
    public function handle(array $arguments): int
    {
        $name = $this->positional($arguments)[0] ?? null;
        if ($name === null) {
            $this->error('Usage : php framework make:model <Nom> [--migration] [--controller] [--view] [--api]');

            return 1;
        }

        $class = $this->studly($name);
        $table = $this->plural($this->snake($class));

        $contents = $this->stub('model', [
            '{{class}}' => $class,
            '{{table}}' => $table,
        ]);
        $this->write(BASE_PATH . '/app/Models/' . $class . '.php', $contents);

        if ($this->option($arguments, 'migration') !== null) {
            $migrate = new MakeMigrationCommand();
            $migrate->handle(['create_' . $table . '_table']);
        }

        $wantController = $this->option($arguments, 'controller') !== null;
        $wantApi = $this->option($arguments, 'api') !== null;

        if ($wantController && !$wantApi) {
            $this->writeWebController($class, $table);
        }
        if ($wantController && $wantApi) {
            $this->writeWebController($class, $table);
            $this->writeApiController($class, $table);
        }
        if ($wantApi && !$wantController) {
            $this->writeApiController($class, $table);
        }

        if ($this->option($arguments, 'view') !== null) {
            $this->writeViews($table);
        }

        if ($wantController || $wantApi) {
            $this->writeOutputDto($class);
        }

        return 0;
    }

    private function writeWebController(string $model, string $table): void
    {
        $controller = $model . 'Controller';
        $contents = $this->stub('controller-crud-web', [
            '{{class}}' => $controller,
            '{{model}}' => $model,
            '{{table}}' => $table,
        ]);
        $this->write(BASE_PATH . '/app/Web/Controllers/' . $controller . '.php', $contents);
    }

    private function writeApiController(string $model, string $table): void
    {
        $controller = $model . 'Controller';
        $contents = $this->stub('controller-crud-api', [
            '{{class}}' => $controller,
            '{{model}}' => $model,
            '{{table}}' => $table,
        ]);
        $this->write(BASE_PATH . '/app/Api/Controllers/' . $controller . '.php', $contents);
    }

    private function writeViews(string $table): void
    {
        $views = new MakeViewCommand();
        $views->handle([$table . '/index']);
        $views->handle([$table . '/form']);
        $views->handle([$table . '/show']);
        $this->line('Pensez à déclarer les routes dans config/routes_web.php (et routes_api.php si --api).');
    }

    /** DTO de sortie : le contrôleur généré l'utilise (compléter les champs). */
    private function writeOutputDto(string $model): void
    {
        $class = $model . 'DTO';
        $path = BASE_PATH . '/app/DTO/' . $class . '.php';
        if (is_file($path)) {
            return;
        }

        $contents = $this->stub('dto', [
            '{{class}}' => $class,
            '{{properties}}' => "    public int \$id;\n",
        ]);
        $this->write($path, $contents);
        $this->line('Complétez les propriétés publiques de ' . $class . ' (contrat JSON / Twig).');
    }
}
