<?php

return [

    'passwords' => [
        'activations' => [
            'provider' => 'users',
            'table' => 'password_activation_tokens',
            'expire' => 4320,
            'throttle' => 60,
        ],
    ],

    /*
     * Staff who sign in with a verified address at one of these domains become super admins,
     * but only in these environments. Production is refused in code whatever this says.
     */
    'staff_super_admins' => [
        'environments' => ['local', 'staging'],
        'domains' => array_values(array_filter(array_map(trim(...), explode(',', (string) env('STAFF_SUPER_ADMIN_DOMAINS', 'ghijk.co.uk,wildatheart.org'))))),
    ],

];
