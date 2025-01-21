<?php

declare(strict_types=1);

// config for GiacomoMasseroni/LaravelModelsGenerator
return [
    'clean_models_directory_before_generation' => true,

    'generate_views' => false,

    /*
    |--------------------------------------------------------------------------
    | Strict types
    |--------------------------------------------------------------------------
    |
    | Add declare(strict_types=1); to the top of each generated model file
    |
    */
    'strict_types' => true,

    /*
    |--------------------------------------------------------------------------
    | Models $table property
    |--------------------------------------------------------------------------
    |
    | Add $table model property
    |
    */
    'table' => true,

    /*
    |--------------------------------------------------------------------------
    | Models $connection property
    |--------------------------------------------------------------------------
    |
    | Add $connection model property
    |
    */
    'connection' => true,

    /*'phpdocs' => [
        'scopes' => true,
    ],*/

    /*
    |--------------------------------------------------------------------------
    | Models $primaryKey property
    |--------------------------------------------------------------------------
    |
    | Add $primaryKey model property
    |
    */
    'primary_key' => true,

    /*
    |--------------------------------------------------------------------------
    | Primary Key in Fillable
    |--------------------------------------------------------------------------
    |
    | Add primary key column field to fillable array
    |
    */
    'primary_key_in_fillable' => true,

    /*
    |--------------------------------------------------------------------------
    | Models path
    |--------------------------------------------------------------------------
    |
    | Where the models will be created
    |
    */
    'path' => app_path('Models'),

    /*
    |--------------------------------------------------------------------------
    | Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace of the generated models
    |
    */
    'namespace' => 'App\Models',

    /*
    |--------------------------------------------------------------------------
    | Parent
    |--------------------------------------------------------------------------
    |
    | The parent class of the generated models
    |
    */
    'parent' => Illuminate\Database\Eloquent\Model::class,

    /*
    |--------------------------------------------------------------------------
    | Base files
    |--------------------------------------------------------------------------
    |
    | If you want to generate a base file for each model, you can enable this.
    | The base file will be created within 'Base' directory inside the models' directory.
    | If you want your base files be abstract you can enable it.
    |
    */
    'base_files' => [
        'enabled' => false,
        'abstract' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Polymorphic relationships
    |--------------------------------------------------------------------------
    |
    | Define polymorphic relationships
    |
    | [
    |       'table_name' => 'polymorphic_type',
    |
    |       ex. for official laravel documentation
    |       'posts' => 'commentable',
    | ]
    |
    */
    'morphs' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Interfaces
    |--------------------------------------------------------------------------
    |
    | Interface(s) implemented by all models
    |
    */
    'interfaces' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Traits
    |--------------------------------------------------------------------------
    |
    | Trait(s) implemented by all models
    |
    */
    'traits' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Enums
    |--------------------------------------------------------------------------
    |
    | Enum(s) implemented by all models
    |
    */
    'enums_casting' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Tables
    |--------------------------------------------------------------------------
    |
    | These models will not be generated
    |
    */
    'except' => [
        'migrations',
        'failed_jobs',
        'password_resets',
        'personal_access_tokens',
        'password_reset_tokens',
    ],
];
