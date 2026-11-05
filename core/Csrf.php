<?php

declare(strict_types=1);

namespace Core;

/**
 * Jeton CSRF stocké en session.
 * Côté vue : {{ csrf_field() }} produit <input name="_csrf">.
 * Côté serveur : CsrfMiddleware compare le champ POST au jeton (hash_equals).
 */
final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    /** Le jeton vit dans la session (même objet que le reste de l'app). */
    public function __construct(private Session $session)
    {
    }

    /** Crée le jeton au premier appel, le réutilise ensuite. */
    public function token(): string
    {
        $token = $this->session->get(self::SESSION_KEY);
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $this->session->set(self::SESSION_KEY, $token);
        }

        return $token;
    }

    /** HTML du champ hidden (échappé). Utilisé par la fonction Twig csrf_field(). */
    public function field(): string
    {
        $token = htmlspecialchars($this->token(), ENT_QUOTES, 'UTF-8');

        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }

    /** Compare le jeton reçu au jeton de session (hash_equals : timing-safe). */
    public function verify(?string $token): bool
    {
        $expected = $this->session->get(self::SESSION_KEY);
        if (!is_string($expected) || !is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($expected, $token);
    }
}
