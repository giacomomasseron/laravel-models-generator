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

    'parent' => Illuminate\Database\Eloquent\Model::class,
    'namespace' => 'App\Models',

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
