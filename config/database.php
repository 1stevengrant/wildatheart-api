<?php

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
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => false, // disable to preserve original behavior for existing applications
    ],

];
