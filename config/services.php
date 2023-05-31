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
    ],

    'mapbox' => [
        'api_url' => 'https://api.mapbox.com/geocoding/v5/mapbox.places/',
        'api_key' => env('MAPBOX_API_KEY'),
        'mapbox_style' => 'mapbox://styles/1stevengrant/cli1piqa3004z01r11o0hby73',
        'mapbox_style_light' => 'mapbox://styles/mapbox/light-v11',
        'types' => 'address,postcode'
    ],

    'zendesk' => [
        'ticket_api' => 'https://'.env('ZENDESK_SUBDOMAIN').'.zendesk.com/api/v2/tickets.json',
        'token' => env('ZENDESK_TOKEN'),
        'user' => env('ZENDESK_USERNAME'),
    ],

    'salesforce' => [
        'callback_uri' => env('SALESFORCE_CALLBACK_URI'),
        'key' => env('SALESFORCE_KEY'),
        'secret' => env('SALESFORCE_SECRET'),
    ],

];
