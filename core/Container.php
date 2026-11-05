<?php

declare(strict_types=1);

namespace Core;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

/**
 * Conteneur d'injection de dépendances (un seul, pas de « service providers »).
 *
 * - bind() / singleton() : déclarés dans config/services.php
 * - make() : si aucun bind, auto-wiring par réflexion du constructeur
 *   (chaque paramètre typé par une classe est résolu récursivement).
 * Un scalaire sans valeur par défaut lève une exception lisible :
 * il faut alors un bind() explicite (ex. View a besoin du chemin des templates).
 */
final class Container
{
    /** @var array<string, callable(self): mixed> */
    private array $bindings = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<string, true> */
    private array $singletons = [];

    /**
     * Le conteneur se connaît lui-même : make(Container::class) fonctionne.
     */
    public function __construct()
    {
        $this->instances[self::class] = $this;
        $this->singletons[self::class] = true;
    }

    /**
     * Associe un identifiant (souvent une interface) à une fabrique.
     * Chaque make() rappelle la fabrique (nouvelle instance).
     *
     * @param callable(self): mixed $factory
     */
    public function bind(string $id, callable $factory): void
    {
        $this->bindings[$id] = $factory;
        unset($this->instances[$id], $this->singletons[$id]);
    }

    /**
     * Comme bind(), mais une seule instance est conservée (PDO, Auth, View…).
     *
     * @param callable(self): mixed $factory
     */
    public function singleton(string $id, callable $factory): void
    {
        $this->bindings[$id] = $factory;
        $this->singletons[$id] = true;
        unset($this->instances[$id]);
    }

    /**
     * Fabrique une instance. Les singletons déjà créés sont renvoyés tels quels.
     */
    public function make(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->bindings[$id])) {
            $object = ($this->bindings[$id])($this);
        } else {
            $object = $this->build($id);
        }

        if (isset($this->singletons[$id])) {
            $this->instances[$id] = $object;
        }

        return $object;
    }

    /**
     * Auto-wiring : lit le constructeur et appelle make() sur chaque type objet.
     */
    private function build(string $class): object
    {
        if (!class_exists($class)) {
            throw new RuntimeException("Impossible de résoudre « {$class} » : classe inconnue, et aucun bind() dans config/services.php.");
        }

        $reflection = new ReflectionClass($class);
        if (!$reflection->isInstantiable()) {
            throw new RuntimeException("Impossible d'instancier « {$class} » (interface ou classe abstraite). Ajoutez un bind() explicite dans config/services.php.");
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return new $class();
        }

        $args = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $args[] = $this->make($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
                continue;
            }

            $name = $parameter->getName();
            throw new RuntimeException(
                "Le constructeur de {$class} demande le paramètre scalaire \${$name} sans valeur par défaut. "
                . "Enregistrez un bind() dans config/services.php."
            );
        }

        return $reflection->newInstanceArgs($args);
    }
}
