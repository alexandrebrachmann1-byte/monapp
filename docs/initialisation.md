# Initialisation et fonctionnement du framework

Ce document explique comment reconstruire le projet depuis le dépôt, puis comment le framework MVC s'organise une fois lancé.

## 1. Prérequis

- PHP 8.2+
- Composer
- MariaDB / MySQL
- XAMPP recommandé sous Windows

## 2. Installation rapide

### Sous XAMPP

1. Place le projet dans `C:\xampp\htdocs\monapp`.
2. Installe les dépendances :

```bash
composer install
```

3. Crée le fichier d'environnement :

```bash
copy .env.example .env
```

4. Adapte les variables principales dans `.env` :

```dotenv
APP_DEBUG=true
APP_BASE_PATH=/monapp
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=monapp
DB_USER=root
DB_PASS=
```

`APP_BASE_PATH` doit correspondre au sous-dossier utilisé dans l'URL :

- `http://localhost/monapp` -> `APP_BASE_PATH=/monapp`
- `http://localhost/framework` -> `APP_BASE_PATH=/framework`
- VirtualHost pointant directement vers `public/` -> `APP_BASE_PATH=`

5. Crée la base vide `monapp`.
6. Lance les migrations :

```bash
C:\xampp\php\php.exe framework migrate
```

7. Ouvre l'application :

[`http://localhost/monapp/`](http://localhost/monapp/)

### Avec le serveur PHP intégré

Dans ce mode, laisse `APP_BASE_PATH=` vide :

```bash
php -S localhost:8000 -t public
```

### Avec Docker

Dans ce mode aussi, laisse `APP_BASE_PATH=` vide :

```bash
docker compose up -d --build
docker compose exec app php framework migrate
```

## 3. Ce que reconstruisent les migrations

Le dépôt contient les migrations nécessaires pour retrouver l'état actuel de la base :

- `users`
- ajout de `role` et `job` sur `users`
- `api_tokens`
- `todos`
- `teams`
- `userteam`

Ordre logique d'exécution :

1. `users`
2. `api_tokens`
3. `todos`
4. `add_role_and_job_to_users`
5. `teams`
6. `userteam`

La commande `php framework migrate` crée aussi la table `migrations` si elle n'existe pas.
Les migrations ajoutées pour `role`, `job`, `teams` et `userteam` tolèrent une base déjà partiellement à jour afin d'éviter un échec immédiat sur un environnement local existant.

## 4. Arborescence utile

```text
public/index.php          point d'entrée HTTP
config/routes_web.php     routes HTML
config/routes_api.php     routes JSON
config/services.php       conteneur DI
core/                     coeur du framework
app/Web/Controllers/      contrôleurs Twig
app/Api/Controllers/      contrôleurs JSON
app/Models/               accès SQL
app/DTO/                  validation et contrats de sortie
database/migrations/      structure de base de données
framework                 commandes CLI
```

## 5. Cycle de fonctionnement

Une requête HTTP suit ce chemin :

1. `public/index.php` charge l'autoload Composer.
2. `Env::load()` lit `.env`.
3. `session_start()` ouvre la session.
4. `config/services.php` enregistre les services dans le conteneur.
5. `Router::dispatch()` charge les routes web et API.
6. Les middlewares s'exécutent (`CSRF`, `Auth`, `CORS`, logs selon le cas).
7. Le contrôleur renvoie soit une vue Twig, soit du JSON.

## 6. Rôle des couches

- `Model` : SQL et QueryBuilder, sans ORM.
- `DTO` : validation des entrées et contrat des sorties.
- `Controller` : orchestration HTTP.
- `View` : rendu Twig, helpers `url()` et `asset()`.
- `Middleware` : sécurité et comportement transversal.

## 7. Commandes utiles

```text
php framework migrate
php framework make:model Nom --migration --controller --view
php framework make:model Nom --controller --api
php framework make:migration create_notes_table
php framework make:dto Nom
php framework make:middleware Auth
composer test
```

## 8. Notes importantes

- `.env` n'est pas versionné.
- `APP_BASE_PATH` doit correspondre exactement à l'URL d'accès.
- Les migrations décrivent l'état de structure, pas les données métier déjà saisies.
- Si tu ajoutes une nouvelle table ou modifies des colonnes, il faut créer une nouvelle migration plutôt que modifier une ancienne déjà appliquée.
