<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Styles
    |--------------------------------------------------------------------------
    |
    | List of styles.
    |
    */

    'styles' => [
        'prose-lg' => [
            'type' => 'paragraph',
            'name' => 'Prose',
            'ident' => 'P',
            'class' => 'prose-lg',
            'cp_css' => null,
            'cp_badge' => true,
        ],
        'hero-1' => [
            'type' => 'paragraph',
            'name' => 'Hero 1',
            'ident' => 'H1',
            'class' => 'wah-hero-1',
            'cp_css' => null,
            'cp_badge' => true,
        ],
        'hero-2' => [
            'type' => 'paragraph',
            'name' => 'Hero 2',
            'ident' => 'H2',
            'class' => 'wah-hero-2',
            'cp_css' => null,
            'cp_badge' => true,
        ],
        'heading-1' => [
            'type' => 'paragraph',
            'name' => 'Heading 1',
            'ident' => 'H1',
            'class' => 'wah-heading-1',
            'cp_css' => null,
            'cp_badge' => true,
        ],
        'body-large' => [
            'type' => 'paragraph',
            'name' => 'Body Large',
            'ident' => 'BL',
            'class' => 'wah-body-large',
            'cp_css' => null,
            'cp_badge' => true,
        ],
        'body-base' => [
            'type' => 'paragraph',
            'name' => 'Body Base',
            'ident' => 'B',
            'class' => 'wah-body-base',
            'cp_css' => null,
            'cp_badge' => true,
        ],
        'body-small' => [
            'type' => 'paragraph',
            'name' => 'Body Small',
            'ident' => 'BS',
            'class' => 'wah-body-small',
            'cp_css' => null,
            'cp_badge' => true,
        ],
        'body-xsmall' => [
            'type' => 'paragraph',
            'name' => 'Body XSmall',
            'ident' => 'BXS',
            'class' => 'wah-body-xsmall',
            'cp_css' => null,
            'cp_badge' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pins (pro only)
    |--------------------------------------------------------------------------
    |
    | List of pins.
    |
    */

    'pins' => [

        // 'icon' => [
        //     'display' => 'Icon',
        //     'icon' => 'fire-flame-burn-hot',
        //     'instructions' => 'An icon graphic.',
        //     'fields' => [
        //         'src' => [
        //             'display' => 'Icon',
        //             'type' => 'assets',
        //             'max_files' => 1,
        //             'mode' => 'list',
        //         ],
        //     ],
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Attributes (pro only)
    |--------------------------------------------------------------------------
    |
    | The attributes that can be edited through the attributes panel.
    |
    */

    'attributes' => [
        'div' => [
            'color' => [
                'type' => 'select',
                'display' => 'Color',
                'default' => '#ffffff',
                'options' => [
                    '#010101' => 'Black',
                    '#ffffff' => 'White',
                    // ... add other color options as needed
                ],
                'rendered' => 'style',
                'style_prefix' => 'color: '
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    |
    | Default styles that will be applied to elements with no style set. It's
    | also possible configure multiple sets of defaults to use with different
    | Bard fields, refer to the docs for more info.
    |
    */

    'defaults' => [

        // 'heading_1' => [
        //     'class' => 'heading-1',
        //     'cp_css' => null,
        //     'cp_badge' => false,
        // ],
        // 'heading_2' => [
        //     'class' => 'heading-2',
        //     'cp_css' => null,
        //     'cp_badge' => false,
        // ],
        // 'paragraph' => [
        //     'class' => 'paragraph',
        //     'cp_css' => null,
        //     'cp_badge' => false,
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    |
    | By default the class names are saved to your content. If you would prefer
    | to save the style keys instead you can change this option to "key".
    |
    */

    'store' => 'class',

];
