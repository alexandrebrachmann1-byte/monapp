<?php

declare(strict_types=1);

namespace Core;

/**
 * Parseur .env maison (pas de vlucas/phpdotenv).
 *
 * Syntaxe acceptée : KEY=value, commentaires #, valeurs entre quotes.
 * À charger une seule fois au boot (public/index.php ou tests/bootstrap.php).
 */
final class Env
{
    /** @var array<string, string> */
    private static array $vars = [];

    private static bool $loaded = false;

    /**
     * Lit le fichier .env et remplit le tableau interne.
     * Les lignes vides et les commentaires # sont ignorés.
     */
    public static function load(string $path): void
    {
        if (!is_file($path)) {
            throw new \RuntimeException("Fichier .env introuvable : {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            throw new \RuntimeException("Impossible de lire le fichier .env : {$path}");
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $eq = strpos($line, '=');
            if ($eq === false) {
                continue;
            }

            $key = trim(substr($line, 0, $eq));
            $value = trim(substr($line, $eq + 1));

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            self::$vars[$key] = $value;
        }

        self::$loaded = true;
    }

    /**
     * Lit une clé. Si elle n'existe pas, retourne $default (jamais d'exception).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$loaded && !array_key_exists($key, self::$vars)) {
            return $default;
        }

        return self::$vars[$key] ?? $default;
    }

    /**
     * Base path normalisé pour les URLs internes.
     * Accepte "monapp" ou "/monapp" dans .env et retourne toujours "/monapp".
     */
    public static function basePath(): string
    {
        $base = trim((string) self::get('APP_BASE_PATH', ''));
        if ($base === '' || $base === '/') {
            return '';
        }

        if (!str_starts_with($base, '/')) {
            $base = '/' . $base;
        }

        return rtrim($base, '/');
    }

    /** Indique si load() a déjà réussi (utile en tests). */
    public static function isLoaded(): bool
    {
        return self::$loaded;
    }
}
