<?php

use Pdo\Mysql;
use Illuminate\Support\Str;

return [

    'connections' => [
        'production' => [
            'driver' => 'mysql',
            'url' => env('PRODUCTION_DATABASE_URL'),
            'host' => env('PRODUCTION_DB_HOST', '127.0.0.1'),
            'port' => env('PRODUCTION_DB_PORT', '3306'),
            'database' => env('PRODUCTION_DB_DATABASE', 'forge'),
            'username' => env('PRODUCTION_DB_USERNAME', 'forge'),
            'password' => env('PRODUCTION_DB_PASSWORD', ''),
            'unix_socket' => env('PRODUCTION_DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        'drupal' => [
            'driver' => 'mysql',
            'host' => env('DRUPAL_DB_HOST', '127.0.0.1'),
            'port' => env('DRUPAL_DB_PORT', '3307'),
            'database' => env('DRUPAL_DB_DATABASE', 'drupal'),
            'username' => env('DRUPAL_DB_USERNAME', 'drupal'),
            'password' => env('DRUPAL_DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
        'local' => [
            'driver' => 'mysql',
            'host' => env('LOCAL_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('LOCAL_DB_PORT', env('DB_PORT', '3306')),
            'database' => env('LOCAL_DB_DATABASE', 'wahlocal'),
            'username' => env('LOCAL_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('LOCAL_DB_PASSWORD', env('DB_PASSWORD', '')),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => false, // disable to preserve original behavior for existing applications
    ],

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel'), '_') . '_database_'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],
    ],

];
