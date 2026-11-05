<?php

declare(strict_types=1);

namespace App;

use App\DTO\UserDTO;
use App\Models\ApiToken;
use App\Models\User;
use Core\Session;

/**
 * Authentification web (session) et API (jeton Bearer).
 * Pas de facade : instancié par le conteneur (singleton).
 */
final class Auth
{
    /** @var array<string, mixed>|null Ligne user sans password_hash */
    private ?array $user = null;

    public function __construct(private Session $session)
    {
        $id = $this->session->get('user_id');
        if (is_int($id) || (is_string($id) && ctype_digit($id))) {
            $row = User::find((int) $id);
            if ($row !== null) {
                $this->user = $this->publicUser($row);
            }
        }
    }

    /**
     * Vérifie email + mot de passe. En cas de succès, ouvre la session web.
     */
    public function attempt(string $email, string $password): bool
    {
        $row = User::table()->where('email', '=', $email)->first();
        if ($row === null) {
            return false;
        }
        if (!password_verify($password, (string) $row['password_hash'])) {
            return false;
        }

        $this->login($row);

        return true;
    }

    /**
     * Mémorise l'utilisateur connecté dans la session (web).
     *
     * @param array<string, mixed> $row ligne SQL users
     */
    public function login(array $row): void
    {
        $this->session->set('user_id', (int) $row['id']);
        $this->user = $this->publicUser($row);
    }

    /**
     * Déconnexion web : on enlève user_id, sans casser flash ni CSRF.
     */
    public function logout(): void
    {
        $this->user = null;
        unset($_SESSION['user_id']);
    }

    /**
     * Utilisateur courant (web session ou API après userFromToken), sans hash.
     *
     * @return array{id: int, username: string, email: string}|null
     */
    public function user(): ?array
    {
        return $this->user;
    }

    public function id(): ?int
    {
        if ($this->user === null) {
            return null;
        }

        return (int) $this->user['id'];
    }

    public function check(): bool
    {
        return $this->user !== null;
    }

    /**
     * Crée un jeton API : le clair n'est montré qu'une fois au client.
     */
    public function issueToken(int $userId): string
    {
        $plain = bin2hex(random_bytes(32));
        ApiToken::insert([
            'user_id' => $userId,
            'token_hash' => hash('sha256', $plain),
        ]);

        return $plain;
    }

    /**
     * Retrouve l'utilisateur à partir du jeton Bearer (clair).
     *
     * @return array<string, mixed>|null
     */
    public function userFromToken(string $plain): ?array
    {
        $hash = hash('sha256', $plain);
        $token = ApiToken::table()->where('token_hash', '=', $hash)->first();
        if ($token === null) {
            return null;
        }

        $row = User::find((int) $token['user_id']);
        if ($row === null) {
            return null;
        }

        $this->user = $this->publicUser($row);

        return $this->user;
    }

    /**
     * Enlève le hash du mot de passe avant toute exposition.
     *
     * @param array<string, mixed> $row
     * @return array{id: int, username: string, email: string}
     */
    private function publicUser(array $row): array
    {
        return UserDTO::fromArray($row)->toArray();
    }
}
