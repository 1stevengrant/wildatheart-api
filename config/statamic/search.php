<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default search index
    |--------------------------------------------------------------------------
    |
    | This option controls the search index that gets queried when performing
    | search functions without explicitly selecting another index.
    |
    */

    'default' => env('STATAMIC_DEFAULT_SEARCH_INDEX', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Search Indexes
    |--------------------------------------------------------------------------
    |
    | Here you can define all the available search indexes.
    |
    | Laravel Cloud sets STATAMIC_SEARCH_DRIVER=database, because a local index
    | lives on each container's ephemeral disk and is rebuilt from scratch
    | whenever a worker finds it missing.
    |
    | Runway resources are listed explicitly so the activity log, email log
    | and attendee tables stay out of the indexes.
    |
    */

    'indexes' => [

        'default' => [
            'driver' => env('STATAMIC_SEARCH_DRIVER', 'local'),
            'searchables' => ['collection:*', 'taxonomy:*', 'assets:*'],
            'fields' => ['title'],
        ],

        'cp' => [
            'driver' => env('STATAMIC_SEARCH_DRIVER', 'local'),
            'searchables' => [
                'collection:*',
                'taxonomy:*',
                'assets:*',
                'users',
                'runway:event',
                'runway:event_type',
                'runway:user',
                'runway:page',
                'runway:email_template',
                'runway:redirect',
            ],
            'fields' => ['title'],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Driver Defaults
    |--------------------------------------------------------------------------
    |
    | Here you can specify default configuration to be applied to all indexes
    | that use the corresponding driver. For instance, if you have two
    | indexes that use the "local" driver, both of them can have the
    | same base configuration. You may override for each index.
    |
    */

    'drivers' => [

        'local' => [
            'path' => storage_path('statamic/search'),
        ],

        'algolia' => [
            'credentials' => [
                'id' => env('ALGOLIA_APP_ID', ''),
                'secret' => env('ALGOLIA_SECRET', ''),
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Search Defaults
    |--------------------------------------------------------------------------
    |
    | Here you can specify default configuration to be applied to all indexes
    | regardless of the driver. You can override these per driver or per index.
    |
    */

    'defaults' => [
        'fields' => ['title'],
    ],

];
