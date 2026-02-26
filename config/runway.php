<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    |
    | Configure the resources (models) you'd like to be available in Runway.
    |
    */

    'resources' => [
        \App\Models\Redirect::class => [
            'name' => 'Redirects',
        ],

        \App\Models\EventType::class => [
            'name' => 'Local Platform Event Types',
        ],

        \App\Models\EventTypeGroup::class => [
            'name' => 'Local Platform Event Type Groups',
        ],

        \App\Models\Page::class => [
            'name' => 'Local Platform Pages',
            'cp_icon' => 'book-pages',
        ],

        \App\Models\Event::class => [
            'name' => 'Local Platform Events',
            'cp_icon' => 'calendar',
            'title_field' => 'id',
        ],

        \App\Models\User::class => [
            'name' => 'Local Platform Users',
            'cp_icon' => 'users',
        ],

        \App\Models\Attendee::class => [
            'name' => 'Local Platform Attendees',
            'cp_icon' => 'user',
        ],

        \App\Models\EmailTemplate::class => [
            'name' => 'Local Platform Email Templates',
            'cp_icon' => 'email-utility',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Runway URIs Table
    |--------------------------------------------------------------------------
    |
    | When using Runway's front-end routing functionality, Runway will store model
    | URIs in a table to enable easy "URI -> model" lookups. If needed, you can
    | customize the table name here.
    |
    */

    'uris_table' => 'runway_uris',

    /*
    |--------------------------------------------------------------------------
    | Disable Migrations?
    |--------------------------------------------------------------------------
    |
    | Should Runway's migrations be disabled?
    | (eg. not automatically run when you next vendor:publish)
    |
    */

    'disable_migrations' => false,

];
