<?php

declare(strict_types=1);

namespace Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * Wrapper Twig. Templates uniquement dans app/Web/Views/.
 *
 * Fonctions globales enregistrées : asset(), csrf_field(), url().
 * Le flash session est injecté à chaque render() (consommé à la lecture).
 */
final class View
{
    private Environment $twig;

    /**
     * Enregistre Twig, les fonctions url/asset/csrf_field.
     * $auth est optionnel (page d'erreur 500 sans base de données).
     */
    public function __construct(
        private string $viewsPath,
        private Csrf $csrf,
        private \App\Auth|null $auth = null,
    ) {
        $loader = new FilesystemLoader($this->viewsPath);
        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => (string) Env::get('APP_DEBUG', 'false') === 'true',
            'strict_variables' => false,
        ]);

        $this->twig->addFunction(new TwigFunction('asset', [$this, 'asset']));
        $this->twig->addFunction(new TwigFunction('csrf_field', [$this->csrf, 'field'], ['is_safe' => ['html']]));
        $this->twig->addFunction(new TwigFunction('url', [$this, 'url']));
    }

    /**
     * URL d'un fichier dans public/assets/, avec cache-busting (filemtime).
     */
    public function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $file = BASE_PATH . '/public/assets/' . $path;
        $version = is_file($file) ? (string) filemtime($file) : (string) time();

        return $this->url('/assets/' . $path) . '?v=' . $version;
    }

    /**
     * Préfixe APP_BASE_PATH : sous XAMPP, /todos devient /framework/todos.
     */
    public function url(string $path): string
    {
        $base = Env::basePath();
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        return $base . $path;
    }

    /**
     * Rend un template. Injecte flash + auth_user pour le layout.
     *
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        $data['flash_success'] = $data['flash_success'] ?? (new Session())->getFlash('success');
        $data['flash_error'] = $data['flash_error'] ?? (new Session())->getFlash('error');
        $data['flash_errors'] = $data['flash_errors'] ?? (new Session())->getFlash('errors');
        $data['old'] = $data['old'] ?? (new Session())->getFlash('old');
        $data['auth_user'] = $data['auth_user'] ?? $this->auth?->user();

        return $this->twig->render($template, $data);
    }
}
