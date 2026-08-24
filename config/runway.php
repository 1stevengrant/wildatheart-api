<?php

use App\Models\Page;
use App\Models\User;
use App\Models\Event;
use App\Models\Activity;
use App\Models\Attendee;
use App\Models\Redirect;
use App\Models\EventType;
use App\Models\EmailTemplate;

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
        Activity::class => [
            'name' => 'Activity Log',
            'cp_icon' => 'history',
            'title_field' => 'description',
            'read_only' => true,
            'duplicatable' => false,
            'order_by' => 'created_at',
            'order_by_direction' => 'DESC',
        ],

        Redirect::class => [
            'name' => 'Redirects',
        ],

        EventType::class => [
            'name' => 'Local Platform Event Types',
        ],

        Page::class => [
            'name' => 'Local Platform Pages',
            'cp_icon' => 'book-pages',
        ],

        Event::class => [
            'name' => 'Local Platform Events',
            'cp_icon' => 'calendar',
            'title_field' => 'id',
        ],

        User::class => [
            'name' => 'Local Platform Users',
            'cp_icon' => 'users',
            'title_field' => 'name',
        ],

        Attendee::class => [
            'name' => 'Local Platform Attendees',
            'cp_icon' => 'user',
        ],

        EmailTemplate::class => [
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
