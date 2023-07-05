# Wild at Heart Local
A Laravel-based platform for connecting like-hearted friends of Wild at Heart to events around the world.

## Local Development

### Prerequisites
To run this locally you will need to make sure you have a SQL db and PHP running on your device. If you're on a Mac, you may want to consider installing the following tools:
* Running PHP - [Laravel Valet](https://laravel.com/docs/10.x/valet)
* Running SQL - [Dbngin](https://dbngin.com/)

You will also need to make sure you have the following tools installed:
* [Comoposer](https://getcomposer.org/) for dependency management.
*  [Artisan](https://laravel.com/docs/10.x/installation) for running db migrations. Follow Laravel installation docs.
*  [Vapor CLI](https://docs.vapor.build/1.0/introduction.html#installing-the-vapor-cli) - for manual deployments and getting local .env files.

### First time setup
You will need to do a few extra steps if it's your first time running the project locally.

**Update dependencies**

Run `composer update` in the project root to install all of the required dependencies.

**Create a `.env` file**

Run `vapor env:pull dev` to get a local env file you can use that has all of the api keys for things like mapbox and auth0. Once you have it, rename it from `.env.dev` to `.env`

**Update `.env` with your local SQL details**

Inside your `.env` file update the following variables:
```bash
DB_HOST=your_local_db_host
DB_PORT=your_local_db_port
DB_DATABASE=your_local_db_name
DB_USERNAME=your_local_db_username
DB_PASSWORD=your_local_db_password
```
If your everything is setup correctly you should be able to run the artisan migrate commands:

```
php artisan db:wipe --force

php artisan migrate --force

php artisan db:seed --class=RoleSeeder --force

php artisan db:seed --class=StateSeeder --force
```

These commands set the database back up to a default state with a few sample fires to work with.