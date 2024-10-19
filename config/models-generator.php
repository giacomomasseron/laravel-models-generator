<?php

declare(strict_types=1);

// config for GiacomoMasseroni/LaravelModelsGenerator
return [
    'connections' => [
        'default' => [
            'table' => true,
            'connection' => true,
            'primary_key' => true,

            /**
             * [
             *      'column_name' => 'table',
             * ]
             */
            'morphs' => [
                'settingable' => 'settings',
            ],

            'morphs_one' => [
                'customer' => 'settings',
            ],
        ],
    ],
];
