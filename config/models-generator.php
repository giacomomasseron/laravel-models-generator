<?php

declare(strict_types=1);

// config for GiacomoMasseroni/LaravelModelsGenerator
return [
    'clean_models_directory_before_generation' => true,

    /**
     * Add declare(strict_types=1); to the top of each generated model file
     */
    'strict_types' => true,

    /**
     * Add $table model property
     */
    'table' => true,

    /**
     * Add $connection model property
     */
    'connection' => true,

    /*'phpdocs' => [
        'scopes' => true,
    ],*/

    /**
     * Add $primaryKey model property
     */
    'primary_key' => true,

    /**
     * Add $primaryKey field to fillable array
     */
    'primary_key_in_fillable' => true,

    'path' => app_path('Models'),

    'namespace' => 'App\Models',

    'parent' => Illuminate\Database\Eloquent\Model::class,

    'base_files' => [
        'enabled' => false,
        'abstract' => true,
    ],

    /**
     * Define polymorphic relationships
     *
     * [
     *      'table_name' => 'polymorphic_type',
     *
     *      ex. for official laravel documentation
     *          'posts' => 'commentable',
     * ]
     */
    'morphs' => [
    ],

    /**
     * Interface(s) implemented by all models
     */
    'interfaces' => [
    ],

    /**
     * Trait(s) used by all models
     */
    'traits' => [
    ],

    /**
     * Enum(s) used in laravel casts function
     */
    'enums_casting' => [
    ],

    /**
     * Excluded Tables
     */
    'except' => [
        'migrations',
        'failed_jobs',
        'password_resets',
        'personal_access_tokens',
        'password_reset_tokens',
    ],
];
