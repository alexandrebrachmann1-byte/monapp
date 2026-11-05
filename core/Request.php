<?php

declare(strict_types=1);

namespace Core;

/**
 * Objet requête : remplace l'accès direct aux superglobales ($_GET, $_POST, $_SERVER).
 *
 * path() retire APP_BASE_PATH pour que les routes restent « /todos »
 * même sous http://localhost/framework/todos (XAMPP).
 */
final class Request
{
    /** @var array<string, mixed> */
    private array $query;

    /** @var array<string, mixed> */
    private array $body;

    /** @var array<string, mixed> */
    private array $server;

    /** @var array<string, mixed> */
    private array $params = [];

    private string $rawBody;

    /**
     * Construit la requête à partir des tableaux PHP (tests : on passe des tableaux factices).
     *
     * @param array<string, mixed> $query $_GET
     * @param array<string, mixed> $body $_POST ou JSON décodé
     * @param array<string, mixed> $server $_SERVER
     */
    public function __construct(array $query, array $body, array $server, string $rawBody = '')
    {
        $this->query = $query;
        $this->body = $body;
        $this->server = $server;
        $this->rawBody = $rawBody;

        // Corps JSON (API) fusionné dans $this->body comme le serait $_POST.
        if ($this->isJson() && $rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                $this->body = $decoded;
            }
        }
    }

    /** Fabrique depuis les vraies superglobales + php://input (API JSON). */
    public static function fromGlobals(): self
    {
        return new self($_GET, $_POST, $_SERVER, (string) file_get_contents('php://input'));
    }

    /** Verbe HTTP en majuscules (GET, POST, PUT…). */
    public function method(): string
    {
        return strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));
    }

    /**
     * Chemin sans query string, sans APP_BASE_PATH.
     * Les routes restent donc « /todos » même sous http://localhost/framework/todos.
     */
    public function path(): string
    {
        $uri = (string) ($this->server['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);
        $path = is_string($path) ? $path : '/';

        $base = Env::basePath();
        if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
            $path = substr($path, strlen($base)) ?: '/';
        }

        // Après réécriture Apache, l'URI peut contenir /public/ en trop.
        if ($path === '/public') {
            $path = '/';
        } elseif (str_starts_with($path, '/public/')) {
            $path = substr($path, strlen('/public')) ?: '/';
        }

        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        return $path === '' ? '/' : $path;
    }

    /** Chemin brut (REQUEST_URI sans query), pour AltoRouter + setBasePath. */
    public function rawPath(): string
    {
        $uri = (string) ($this->server['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);

        return is_string($path) ? $path : '/';
    }

    /** Une valeur POST, GET ou paramètre de route. */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $this->params[$key] ?? $default;
    }

    /**
     * Fusion GET + POST/JSON + params de route.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->body, $this->params);
    }

    /** Paramètre d'URL AltoRouter, ex. [i:id] → param('id'). */
    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    /**
     * Injecté par le Router après le match (id, etc.).
     *
     * @param array<string, mixed> $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /** En-tête HTTP (Authorization, Content-Type…). Apache peut préfixer HTTP_. */
    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if (isset($this->server[$key])) {
            return (string) $this->server[$key];
        }

        if (strcasecmp($name, 'Content-Type') === 0 && isset($this->server['CONTENT_TYPE'])) {
            return (string) $this->server['CONTENT_TYPE'];
        }

        if (strcasecmp($name, 'Authorization') === 0 && isset($this->server['REDIRECT_HTTP_AUTHORIZATION'])) {
            return (string) $this->server['REDIRECT_HTTP_AUTHORIZATION'];
        }

        return isset($this->server[$name]) ? (string) $this->server[$name] : null;
    }

    /** True si Content-Type contient application/json. */
    public function isJson(): bool
    {
        $type = $this->header('Content-Type') ?? '';

        return str_contains(strtolower($type), 'application/json');
    }

    /** True si le chemin (après APP_BASE_PATH) commence par /api. */
    public function isApi(): bool
    {
        return str_starts_with($this->path(), '/api');
    }
}
