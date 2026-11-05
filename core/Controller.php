<?php

declare(strict_types=1);

namespace Core;

/**
 * Classe de base des contrôleurs web (HTML / Twig).
 * L'API a sa propre classe : ApiController (JSON, autre namespace).
 */
abstract class Controller
{
    /**
     * DI : View, Session, Request, Auth (pour $this->auth dans les enfants).
     */
    public function __construct(
        protected View $view,
        protected Session $session,
        protected Request $request,
        protected \App\Auth $auth,
    ) {
    }

    /**
     * Affiche un template Twig (chemin relatif à app/Web/Views/).
     *
     * @param array<string, mixed> $data
     */
    protected function view(string $template, array $data = []): void
    {
        echo $this->view->render($template, $data);
    }

    /** En-tête Location. $path est une route interne (« /todos »), pas une URL absolue. */
    protected function redirect(string $path): never
    {
        $base = Env::basePath();
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        header('Location: ' . $base . $path);
        exit;
    }
}
