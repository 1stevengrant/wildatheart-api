<?php

if (config('app.env') === 'production') {
    return [
        'path' => 'command-runner',

        'commands' => [],

        'history' => 10,

        'navigation_label' => 'Command Runner',

        'help' => '',

        // Allow running of custom artisan and bash(shell) commands
        'custom_commands' => ['artisan', 'bash'],

        'without_overlapping' => [
            // Blocks running commands simultaneously under the given groups. Use '*' for block all groups
            'groups' => [
                //
            ],

            // Blocks running commands simultaneously. Use '*' for block all groups
            'commands' => [
                //
            ],
        ],

        'polling_time' => 2500,
    ];
}

return [
    'path' => 'command-runner',

    'commands' => [
        // Basic command
        'Send Monthly Email to Fire Hosts' => [
            'run' => 'app:send-monthly-email-to-fire-hosts',
            'type' => 'primary',
            'group' => 'Email',
            'help' => 'These emails will land in Postmark and won\'t be sent to the actual users.',
        ],
    ],

    // Limit the command run history to latest 10 runs
    'history' => 10,

    // Tool name displayed in the navigation menu
    'navigation_label' => 'Command Runner',

    // Any additional info to display on the tool page. Can contain string and html.
    'help' => '',

    // Allow running of custom artisan and bash(shell) commands
    'custom_commands' => ['artisan', 'bash'],

    'polling_time' => 2500,
];
