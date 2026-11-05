<?php

declare(strict_types=1);

namespace Console\Commands;

use Core\Console\Command;
use Core\Database;
use Core\Model;
use PDO;

final class MakeDtoCommand extends Command
{
    public function handle(array $arguments): int
    {
        $name = $this->positional($arguments)[0] ?? null;
        if ($name === null) {
            $this->error('Usage : php framework make:dto <Nom> [--model=X]');

            return 1;
        }

        $class = $this->studly($name);
        if (!str_ends_with($class, 'DTO')) {
            $class .= 'DTO';
        }

        $modelOption = $this->option($arguments, 'model');
        $properties = '';
        if ($modelOption !== null && $modelOption !== 'true') {
            $properties = $this->propertiesFromModel($modelOption);
        }

        $contents = $this->stub('dto', [
            '{{class}}' => $class,
            '{{properties}}' => $properties,
        ]);
        $this->write(BASE_PATH . '/app/DTO/' . $class . '.php', $contents);

        return 0;
    }

    private function propertiesFromModel(string $model): string
    {
        $class = 'App\\Models\\' . $this->studly($model);
        if (!class_exists($class) || !is_subclass_of($class, Model::class)) {
            throw new \RuntimeException("Modèle inconnu : {$class}");
        }

        $table = $class::tableName();
        $pdo = Database::connection();
        $stmt = $pdo->query('DESCRIBE `' . str_replace('`', '', $table) . '`');
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lines = [];
        foreach ($columns as $column) {
            $field = $column['Field'];
            $phpType = $this->phpType((string) $column['Type'], $column['Null'] === 'YES');
            $lines[] = '    public ' . $phpType . ' $' . $field . ';';
        }

        return $lines === [] ? '' : implode("\n", $lines) . "\n";
    }

    private function phpType(string $mysqlType, bool $nullable): string
    {
        $mysqlType = strtolower($mysqlType);
        $type = 'string';
        if (str_contains($mysqlType, 'int')) {
            $type = 'int';
        } elseif (preg_match('/(decimal|float|double)/', $mysqlType)) {
            $type = 'float';
        } elseif (preg_match('/(tinyint\(1\)|bool)/', $mysqlType)) {
            $type = 'bool';
        }

        return ($nullable ? '?' : '') . $type;
    }
}
