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
             *      'table_name' => 'polymorphic_type',
             *
             *      ex. for official laravel documentation
             *          'posts' => 'commentable',
             *
             * ]
             */
            'morphs' => [
            ],

            /**
             * Interface(s) implemented by all models
             */
            'implements' => [

            ],
        ],
    ],
];
