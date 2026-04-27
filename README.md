
<p align="center"><a href="https://local.wildatheart.org" target="_blank"><img src="public/favicon.svg" alt="Wild at 
Heart Fires Logo"></a></p>

[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2Fa72658a2-4156-4bbb-bdd7-69082094396d%3Fdate%3D1%26label%3D1&style=for-the-badge)](https://forge.laravel.com/servers/830827/sites/2459991)

# Wild at Heart Local

A Laravel-based platform for connecting like-hearted friends of Wild at Heart to events around the world via a map-based interface. Local also includes a headless CMS that powers the main Wild at Heart website.

## Requirements

To run this locally you will need to make sure you have a SQL db and PHP running on your device. If you're on a Mac, you may want to consider installing the following tools:

-   Running PHP - [Laravel Valet](https://laravel.com/docs/10.x/valet) or [Laravel Herd](https://herd.laravel.com/). Laravel Herd is the easiest way to get a Laravel project up and running locally.
-   Running SQL - [DBngin](https://dbngin.com/)
-   Running Redis - Laravel Herd includes Redis by default

*   PHP 8.1
*   npm as a package manager. This is important. While npm could be used, it's not advised to have package-lock.json and
    yarn.lock files in the same project.
*   MySQL 8.0
*   Redis (included with Herd)

You will also need to make sure you have the following tools installed:

-   [Composer](https://getcomposer.org/) for dependency management (included with Herd)

## Installation

Make sure your server meets the minimum requirements before you start. [Official Documentation](https://laravel.com/docs/10.x/installation).

1. clone the repository `git clone git@github.com:wild-at-heart/local.wildatheart.org.git`
2. change into the directory `cd local.wildatheart.org`
3. install composer dependencies `composer install`
4. copy the `.env.example` file to `.env` and fill in the database credentials `cp .env.example .env`
5. install npm dependencies `npm install`
6. run build script `npm run build`

    ```bash
     DB_HOST=your_local_db_host
     DB_PORT=your_local_db_port
     DB_DATABASE=your_local_db_name
     DB_USERNAME=your_local_db_username
     DB_PASSWORD=your_local_db_password

     REDIS_HOST=127.0.0.1
     REDIS_PASSWORD=null
     REDIS_PORT=6379
    ```

7. populate AWS credentials in `.env` file

    ```bash
    AWS_ACCESS_KEY_ID=
    AWS_SECRET_ACCESS_KEY=
    AWS_DEFAULT_REGION=us-east-1
    AWS_BUCKET=
    AWS_USE_PATH_STYLE_ENDPOINT=false
    ```

8. generate an application key `php artisan key:generate`
9. run the database migrations `php artisan migrate`
10. run the database seeders `php artisan db:seed`

    These commands set the database back up to a default state with a few sample events to work with.

11. optionally, you might want to grab a production database backup. For that, see the [database](docs/database.md) docs.
12. run the development server `php artisan serve` if not using something like Laravel Valet, Sail or Herd.

## Running tests

1. Follow installation steps above.
2. Make sure you have a `wahlocal_test` database created.
3. If you're using Herd, set your PHP memory limit to at least 256M.
4. Copy `.env.testing.example` to `.env.testing` and fill in the missing variables.
5. Run the tests with `composer test`.

### Test commands

| Command | Description |
|---|---|
| `composer test` | Parallel tests (8 processes), excludes comparison tests |
| `composer test:sequential` | Non-parallel tests, excludes comparison tests |
| `composer test:fast` | Parallel tests without coverage, excludes comparison tests |
| `composer test:comparison` | Pre-rollout comparison tests (requires `php artisan db:production-sync`) |
| `composer test:unit` | Unit tests only |
| `composer test:feature` | Feature tests only (4 parallel processes) |

Parallel test runs create temporary databases (`wahlocal_test_test_1`, etc.) that are automatically cleaned up after the test suite finishes. If you need to clean them up manually, run `php artisan test:cleanup-databases`.

## Notes

-   useful terminal commands when switching branches:

    php artisan cache:clear && php artisan config:clear && php artisan view:clear && php artisan route:clear && php artisan event:clear

## Project Specifics

-   [Database](docs/database.md)
-   [Front End](docs/front-end.md)
-   [Auth](docs/auth.md)
-   [API](docs/api.md)
-   [Integrations](docs/integrations.md)
-   [CMS](docs/cms.md)
-   [Deployment](docs/deployment.md)
-   [Testing](docs/testing.md)
