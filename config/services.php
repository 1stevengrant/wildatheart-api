<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'auth0' => [
        'client_id' => env('AUTH0_CLIENT_ID'),
        'client_secret' => env('AUTH0_CLIENT_SECRET'),
        'redirect' => env('AUTH0_REDIRECT_URI'),
        'base_url' => env('AUTH0_DOMAIN'),
        'cookie_secret' => 'c1XhulxTocmsvHIQ1tBOp7McazNhkZVO',
    ],

    'mapbox' => [
        'api_url' => 'https://api.mapbox.com/geocoding/v5/mapbox.places/',
        'api_key' => env('MAPBOX_API_KEY'),
        'mapbox_style' => 'mapbox://styles/mapbox/navigation-day-v1',
        'mapbox_style_light' => 'mapbox://styles/mapbox/light-v11',
        'types' => 'address,postcode',
        'initial_zoom_level' => 10,
    ],

    'zendesk' => [
        'ticket_api' => 'https://' . env('ZENDESK_SUBDOMAIN') . '.zendesk.com/api/v2/tickets.json',
        'token' => env('ZENDESK_TOKEN'),
        'user' => env('ZENDESK_USERNAME'),
    ],

    'salesforce' => [
        'callback_uri' => env('SALESFORCE_CALLBACK_URI'),
        'key' => env('SALESFORCE_KEY'),
        'secret' => env('SALESFORCE_SECRET'),
    ],

    'slack' => [
        'webhook_url' => env('SLACK_WEBHOOK_URL'),
        'events_webhook_url' => env('SLACK_EVENTS_WEBHOOK_URL'),
    ],

    'bunny' => [
        's3_cdn_url' => env('BUNNY_S3_CDN_URL'),
        'api_url' => env('BUNNY_CDN_URL'),
    ],

    'zapier' => [
        'enabled' => env('ZAPIER_ENABLED', false),
    ],

    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY'),
    ],

    'subsplash' => [
        'client_id' => env('SUBSPLASH_CLIENT_ID'),
        'client_secret' => env('SUBSPLASH_CLIENT_SECRET'),
        'scope' => env('SUBSPLASH_SCOPE'),
        'authentication_token_url' => env('SUBSPLASH_AUTHENTICATION_TOKEN_URL'),
        'cache_duration' => env('SUBSPLASH_CACHE_DURATION'),
    ],
    'transistor' => [
        'api_key' => env('TRANSISTOR_API_KEY'),
        'api_url' => env('TRANSISTOR_API_URL'),
        'cache_duration' => env('TRANSISTOR_CACHE_DURATION'),
    ],
];
