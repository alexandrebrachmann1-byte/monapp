<?php

declare(strict_types=1);

namespace Core;

/**
 * Wrapper de $_SESSION.
 * session_start() n'est PAS ici : il est centralisé dans public/index.php.
 *
 * flash() / getFlash() : message lu une seule fois (PRG : Post-Redirect-Get).
 */
final class Session
{
    /** Enregistre une valeur jusqu'à la fin de la session (ou destroy). */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /** Lit une valeur persistante (pas flash). */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /** Message pour la requête suivante uniquement (après redirect). */
    public function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /** Lit et supprime le flash (une seule consommation). */
    public function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);

        return $value;
    }

    /** Vide $_SESSION et détruit l'id de session côté PHP. */
    public function destroy(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
