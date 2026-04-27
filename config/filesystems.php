<?php

return [

    'disks' => [
        'wordpress' => [
            'driver' => 's3',
            'key' => env('WP_AWS_ACCESS_KEY_ID'),
            'secret' => env('WP_AWS_SECRET_ACCESS_KEY'),
            'region' => env('WP_AWS_DEFAULT_REGION'),
            'bucket' => env('WP_AWS_BUCKET'),
            'url' => env('WP_AWS_URL'),
            'endpoint' => env('WP_AWS_ENDPOINT'),
            'use_path_style_endpoint' => env(
                'WP_AWS_USE_PATH_STYLE_ENDPOINT',
                false
            ),
            'throw' => false,
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env(
                'AWS_USE_PATH_STYLE_ENDPOINT',
                false
            ),
            'throw' => false,
            'visibility' => 'public',
        ],

        'memory' => [
            'driver' => 'local',
            'root' => storage_path('app/testing'),
        ],

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],
    ],

];
