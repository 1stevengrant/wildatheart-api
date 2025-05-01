<?php

return [

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
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

    'wegive' => [
        'api_key' => env('WEGIVE_API_KEY'),
        // Attendee tag found at https://dashboard.wegive.com/data/tags
        'attendee_tag_id' => env('WEGIVE_ATTENDEE_TAG_ID'),
        // Weekly email & updates list at https://dashboard.wegive.com/engagement/communication-lists
        'marketing_list_id' => env('WEGIVE_MARKETING_LIST_ID'),
    ],

];
