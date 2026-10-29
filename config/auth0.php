<?php

return [
    'client_id' => env('AUTH0_CLIENT_ID'),
    'client_secret' => env('AUTH0_CLIENT_SECRET'),
    'redirect' => env('AUTH0_REDIRECT_URI', mb_rtrim((string) env('APP_URL'), '/') . '/callback'),
    'base_url' => env('AUTH0_DOMAIN', 'https://auth.wildatheart.org'),
    'cookie_secret' => 'c1XhulxTocmsvHIQ1tBOp7McazNhkZVO',
    'audience' => env('AUTH0_AUDIENCE', 'https://local.wildatheart.org'),
    'api' => [
        'audience' => env('AUTH0_API_AUDIENCE', 'https://api.wildatheart.org'),
        'custom_domain' => env('AUTH0_API_CUSTOM_DOMAIN', 'auth.wildatheart.org'),
        'tenant_domain' => env('AUTH0_API_TENANT_DOMAIN', 'ransomed-heart.auth0.com'),
        'jwks_uri' => env('AUTH0_API_JWKS_URI', 'https://auth.wildatheart.org/.well-known/jwks.json'),
        'userinfo_url' => env('AUTH0_API_USERINFO_URL', 'https://auth.wildatheart.org/userinfo'),
    ],
    'management' => [
        'client_id' => env('AUTH0_MANAGEMENT_CLIENT_ID'),
        'client_secret' => env('AUTH0_MANAGEMENT_CLIENT_SECRET'),
        'audience' => env('AUTH0_MANAGEMENT_AUDIENCE', 'https://ransomed-heart.auth0.com/api/v2/'),
        'endpoint' => env('AUTH0_MANAGEMENT_OAUTH_TOKEN_ENDPOINT', 'https://ransomed-heart.auth0.com/oauth/token'),
    ],
];
