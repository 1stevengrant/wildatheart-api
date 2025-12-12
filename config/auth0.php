<?php

return [
    'client_id' => env('AUTH0_CLIENT_ID'),
    'client_secret' => env('AUTH0_CLIENT_SECRET'),
    'redirect' => env('AUTH0_REDIRECT_URI'),
    'base_url' => env('AUTH0_DOMAIN'),
    'domain' => 'https://auth.wildatheart.org',
    'cookie_secret' => 'c1XhulxTocmsvHIQ1tBOp7McazNhkZVO',
    'audience' => env('AUTH0_AUDIENCE') ? [env('AUTH0_AUDIENCE')] : null,
    'management' => [
        'client_id' => env('AUTH0_MANAGEMENT_CLIENT_ID'),
        'client_secret' => env('AUTH0_MANAGEMENT_CLIENT_SECRET'),
        'audience' => env('AUTH0_MANAGEMENT_AUDIENCE'),
        'endpoint' => env('AUTH0_MANAGEMENT_OAUTH_TOKEN_ENDPOINT'),
    ],
];
