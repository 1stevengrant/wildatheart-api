<p align="center"><a href="https://local.wildatheart.org" target="_blank"><img src="public/favicon.svg" alt="Wild at 
Heart Fires Logo"></a></p>

# Wild at Heart Local
A Laravel-based platform for connecting like-hearted friends of Wild at Heart to events around the world.

## Requirements
To run this locally you will need to make sure you have a SQL db and PHP running on your device. If you're on a Mac, you may want to consider installing the following tools:
* Running PHP - [Laravel Valet](https://laravel.com/docs/10.x/valet) or [Laravel Herd](https://herd.laravel.com/)
* Running SQL - [DBngin](https://dbngin.com/)
- PHP 8.1
- npm as a package manager. This is important. While npm could be used, it's not advised to have package-lock.json and 
  yarn.lock files in the same project.
- MySQL 8.0

You will also need to make sure you have the following tools installed:

* [Composer](https://getcomposer.org/) for dependency management.
* [Vapor CLI](https://docs.vapor.build/1.0/introduction.html#installing-the-vapor-cli) - for manual deployments and 
  getting local .env files.

## Installation

Make sure your server meets the minimum requirements before you start. [Official Documentation](https://laravel.com/docs/10.x/installation).

1. clone the repository `git clone git@github.com:wild-at-heart/local.wildatheart.org.git`
2. change into the directory `cd local.wildatheart.org`
3. install composer dependencies `composer install`
4. install npm dependencies `npm install`
5. copy the `.env.example` file to `.env` and fill in the database credentials `cp .env.example .env`

   ```bash
    DB_HOST=your_local_db_host
    DB_PORT=your_local_db_port
    DB_DATABASE=your_local_db_name
    DB_USERNAME=your_local_db_username
    DB_PASSWORD=your_local_db_password
   ```
6. populate AWS credentials in `.env` file
    
    ```bash
    AWS_ACCESS_KEY_ID=
    AWS_SECRET_ACCESS_KEY=
    AWS_DEFAULT_REGION=us-east-1
    AWS_BUCKET=
    AWS_USE_PATH_STYLE_ENDPOINT=false
    ```
7. generate an application key `php artisan key:generate`
8. run the database migrations `php artisan migrate`
9. run the database seeders `php artisan db:seed`
   
    These commands set the database back up to a default state with a few sample events to work with. 
10. optionally, you might want to grab a production database backup. For that, add the production database credentials 
   to the `.env` file and run `php artisan app:pull-prod-database`
11. run the development server `php artisan serve` if not using something like Laravel Valet, Sail or Herd.
