# Framework MVC pédagogique (PHP ^8.2)

Framework minimaliste pour l'enseignement : AltoRouter, Twig, PDO / QueryBuilder (`toSql()`), **sans ORM** et **sans facades**. PHP cible : **^8.2**.

## Documentation

- Guide d'initialisation : [docs/initialisation.md](docs/initialisation.md)
- Cours complet : [docs/cours.md](docs/cours.md)

## Démarrage rapide

### XAMPP

1. Copier le projet dans `C:\xampp\htdocs\monapp`
2. Installer les dépendances :

```bash
composer install
copy .env.example .env
```

3. Configurer `.env` :

```dotenv
APP_DEBUG=true
APP_BASE_PATH=/monapp
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=monapp
DB_USER=root
DB_PASS=
```

4. Créer la base `monapp`
5. Exécuter les migrations :

```bash
C:\xampp\php\php.exe framework migrate
```

6. Ouvrir :

[`http://localhost/monapp/`](http://localhost/monapp/)

### Docker

```bash
docker compose up -d --build
docker compose exec app php framework migrate
```

Application disponible sur [`http://localhost:8080`](http://localhost:8080) avec `APP_BASE_PATH=` vide.

### Serveur PHP intégré

Laisser `APP_BASE_PATH=` vide, puis lancer :

```bash
php -S localhost:8000 -t public
```

## Fonctionnalités actuelles

- authentification web par session
- authentification API par jeton Bearer
- gestion des utilisateurs
- gestion des todos
- gestion des teams

## Base de données

Le dépôt contient les migrations pour reconstruire l'état actuel du schéma :

- `users`
- ajout de `role` et `job` sur `users`
- `api_tokens`
- `todos`
- `teams`
- `userteam`

## Commandes utiles

```text
php framework migrate
php framework make:model Article --migration --controller --view
php framework make:model Article --controller --api
php framework make:controller Nom
php framework make:controller Nom --api
php framework make:dto Nom
php framework make:view articles/index
php framework make:migration create_posts_table
php framework make:middleware Auth
composer test
```

Les routes ne sont pas générées automatiquement : elles restent à déclarer dans `config/routes_web.php` et `config/routes_api.php`.
