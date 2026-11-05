<?php

declare(strict_types=1);

use Core\Container;
use Core\Csrf;
use Core\Database;
use Core\Request;
use Core\Session;
use Core\View;

/**
 * Bindings explicites du conteneur — le seul endroit où l'on « branche » les services.
 * Pas de découverte automatique de packages.
 *
 * PDO, View, Request, Session, Csrf sont des singletons (une instance par requête).
 *
 * @return callable(Container): void
 */
return function (Container $c): void {
    $c->singleton(\PDO::class, fn () => Database::connection());
    $c->singleton(Session::class, fn () => new Session());
    $c->singleton(Csrf::class, fn (Container $c) => new Csrf($c->make(Session::class)));
    $c->singleton(\App\Auth::class, fn (Container $c) => new \App\Auth($c->make(Session::class)));
    $c->singleton(View::class, fn (Container $c) => new View(
        BASE_PATH . '/app/Web/Views',
        $c->make(Csrf::class),
        $c->make(\App\Auth::class)
    ));
    $c->singleton(Request::class, fn () => Request::fromGlobals());
};
