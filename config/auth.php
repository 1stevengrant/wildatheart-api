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

];
