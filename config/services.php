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
        'ticket_api' => 'https://' . env('ZENDESK_SUBDOMAIN', 'ransomedheart') . '.zendesk.com/api/v2/tickets.json',
        'token' => env('ZENDESK_TOKEN'),
        'user' => env('ZENDESK_USERNAME'),
    ],

    'slack' => [
        'webhook_url' => env('SLACK_WEBHOOK_URL'),
        'events_webhook_url' => env('SLACK_EVENTS_WEBHOOK_URL'),
    ],

    'bunny' => [
        's3_cdn_url' => env('BUNNY_S3_CDN_URL', 'https://b-cdn.wildatheart.org'),
        'api_url' => env('BUNNY_CDN_URL', 'https://fires.b-cdn.net'),
    ],

    'zapier' => [
        'enabled' => env('ZAPIER_ENABLED', false),
    ],

    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY'),
    ],

    'subsplash' => [
        'base_url' => env('SUBSPLASH_BASE_URL', 'https://core.subsplash.com'),
        'client_id' => env('SUBSPLASH_CLIENT_ID'),
        'client_secret' => env('SUBSPLASH_CLIENT_SECRET'),
        'scope' => env('SUBSPLASH_SCOPE', '5289PV'),
        'authentication_token_url' => env('SUBSPLASH_AUTHENTICATION_TOKEN_URL', 'https://core.subsplash.com/tokens/v1/token'),
        'cache_duration' => env('SUBSPLASH_CACHE_DURATION', 600),
    ],

    'transistor' => [
        'api_key' => env('TRANSISTOR_API_KEY'),
        'api_url' => env('TRANSISTOR_API_URL', 'https://api.transistor.fm/v1/'),
        'cache_duration' => env('TRANSISTOR_CACHE_DURATION', 600),
    ],

    'wegive' => [
        'api_key' => env('WEGIVE_API_KEY'),
        // Attendee tag found at https://dashboard.wegive.com/data/tags
        'attendee_tag_id' => env('WEGIVE_ATTENDEE_TAG_ID', 331),
        // Weekly email & updates list at https://dashboard.wegive.com/engagement/communication-lists
        'marketing_list_id' => env('WEGIVE_MARKETING_LIST_ID', 'xxx'),
        // Event type tag IDs found at https://dashboard.wegive.com/data/tags/
        'event_type_tags' => [
            'production' => [
                'Fires' => 239,
                'Captivating Fires' => 240,
                'Wild at Heart Experience' => 241,
                'Wild at Heart Retreat' => 242,
                'Survival School' => 243,
                'Becoming A King Experience' => 244,
                'Becoming A King Retreat' => 245,
                'Captivating Experience' => 246,
                '4-Day Captivating Retreat' => 247,
                '1-Day Captivating Retreat' => 854,
            ],
            'staging' => [
                'Fires' => 281,
                'Wild at Heart Experience' => 282,
                'Wild at Heart Retreat' => 283,
                'Survival School' => 284,
                'Becoming A King Experience' => 285,
                'Becoming A King Retreat' => 286,
                'Captivating Experience' => 287,
                '4-Day Captivating Retreat' => 288,
                'Captivating Fires' => 289,
                '1-Day Captivating Retreat' => 855,
            ],
        ],
    ],

];
