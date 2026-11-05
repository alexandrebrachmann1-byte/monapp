<?php

declare(strict_types=1);

namespace Core\Console;

/**
 * Classe de base des commandes CLI.
 * Helpers pédagogiques : studly() / snake() / plural() pour générer des noms de fichiers,
 * stub() pour charger un modèle de code, write() pour refuser d'écraser un fichier existant.
 */
abstract class Command
{
    /**
     * Exécute la commande. Code retour Unix : 0 = OK, 1 = erreur.
     *
     * @param list<string> $arguments
     */
    abstract public function handle(array $arguments): int;

    /** article-status → ArticleStatus (classe PHP). */
    public function studly(string $value): string
    {
        $value = str_replace(['-', '_', '/'], ' ', $value);
        $value = ucwords($value);

        return str_replace(' ', '', $value);
    }

    /** ArticleStatus → article_status (nom de table). */
    public function snake(string $value): string
    {
        $value = preg_replace('/(?<!^)[A-Z]/', '_$0', $value) ?? $value;

        return strtolower($value);
    }

    /** Pluriel naïf pédagogique : category → categories, todo → todos. */
    public function plural(string $snake): string
    {
        if (str_ends_with($snake, 'y') && !preg_match('/[aeiou]y$/', $snake)) {
            return substr($snake, 0, -1) . 'ies';
        }
        if (preg_match('/(s|x|z|ch|sh)$/', $snake)) {
            return $snake . 'es';
        }

        return $snake . 's';
    }

    /**
     * Charge un stub et remplace les jetons {{NOM}}.
     *
     * @param array<string, string> $replacements
     */
    public function stub(string $name, array $replacements): string
    {
        $path = BASE_PATH . '/console/Commands/stubs/' . $name . '.stub';
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException("Stub introuvable : {$path}");
        }

        return str_replace(array_keys($replacements), array_values($replacements), $contents);
    }

    /** Écrit un fichier ; refuse d'écraser (évite d'effacer le travail de l'étudiant). */
    public function write(string $path, string $contents): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (file_exists($path)) {
            throw new \RuntimeException("Le fichier existe déjà : {$path}");
        }
        file_put_contents($path, $contents);
        $this->line('Créé : ' . str_replace(BASE_PATH . DIRECTORY_SEPARATOR, '', $path));
    }

    /** Message sur stdout. */
    public function line(string $message): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }

    /** Message d'erreur sur stderr. */
    public function error(string $message): void
    {
        fwrite(STDERR, $message . PHP_EOL);
    }

    /**
     * Lit --nom ou --nom=valeur. Flag sans valeur → chaîne "true".
     *
     * @param list<string> $arguments
     */
    protected function option(array $arguments, string $name): ?string
    {
        foreach ($arguments as $argument) {
            if ($argument === '--' . $name || $argument === '-' . $name) {
                return 'true';
            }
            if (str_starts_with($argument, '--' . $name . '=')) {
                return substr($argument, strlen($name) + 3);
            }
        }

        return null;
    }

    /**
     * Arguments qui ne commencent pas par - (le nom du modèle, etc.).
     *
     * @param list<string> $arguments
     */
    protected function positional(array $arguments): array
    {
        return array_values(array_filter(
            $arguments,
            static fn (string $arg): bool => !str_starts_with($arg, '-')
        ));
    }
}
