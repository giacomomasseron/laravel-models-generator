<?php

declare(strict_types=1);

// config for GiacomoMasseroni/LaravelModelsGenerator
use GiacomoMasseroni\LaravelModelsGenerator\Enums\RelationshipsNameCaseTypeEnum;

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
    | Add primary key column field to the fillable array
    |
    */
    'primary_key_in_fillable' => true,

    /*
    |--------------------------------------------------------------------------
    | Default values
    |--------------------------------------------------------------------------
    |
    | Add the $attributes array for default values
    |
    */
    'attributes' => true,

    /*
    |--------------------------------------------------------------------------
    | Timestamps customized fields
    |--------------------------------------------------------------------------
    |
    | Change the default Laravel timestamps fields.
    | Ex. created_at => 'created_at',
    |     updated_at => 'updated_at'
    |
    */
    'timestamps' => [
        'fields' => [
            'created_at' => null,
            'updated_at' => null,
        ],
        'format' => null,
    ],

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
    | The base file will be created within the' Base 'directory inside the models' directory.
    | If you want your base files to be abstract, you can enable it.
    |
    */
    'base_files' => [
        'enabled' => false,
        'abstract' => true,
        'generate_children_classes' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table prefix
    |--------------------------------------------------------------------------
    |
    | Remove table prefix value from laravel model name
    |
    */
    'table_prefix' => '',

    /*
    |--------------------------------------------------------------------------
    | Add comments in PHPDocs
    |--------------------------------------------------------------------------
    |
    | Add comments to PHPDocs column property (Ex. @property int $id (comment))
    |
    */
    'add_comments_in_phpdocs' => true,

    /*
    |--------------------------------------------------------------------------
    | Relationships name case type
    |--------------------------------------------------------------------------
    |
    | Define the way relation name is created.
    | Possible values: "camel_case", "snake_case"
    |
    */
    'relationships_name_case_type' => RelationshipsNameCaseTypeEnum::CAMEL_CASE,

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
    | Table Traits
    |--------------------------------------------------------------------------
    |
    | Trait(s) implemented by specific models.
    | Ex.
    |   'table' => TraitClass::class|[TraitClass::class],
    |
    */
    'table_traits' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Enums
    |--------------------------------------------------------------------------
    |
    | Enum(s) implemented by all models.
    | Ex.
    |   'column' => EnumClass::class,
    |
    */
    'enums_casting' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Uuids
    |--------------------------------------------------------------------------
    |
    | If you want to use UUIDs in your models, you can define them here.
    | Ex.
    |   'table' => ['column1', 'column2'],
    |
    */
    'uuids' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Ulids
    |--------------------------------------------------------------------------
    |
    | If you want to use Ulids in your models, you can define them here.
    | Ex.
    |   ['table1', 'table2'],
    |
    */
    'ulids' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Observers
    |--------------------------------------------------------------------------
    |
    | Observer(s) implemented by specific models.
    | Ex.
    |   'table' => ObserverClass::class,
    |
    */
    'observers' => [
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

    /*
    |--------------------------------------------------------------------------
    | Excluded Columns
    |--------------------------------------------------------------------------
    |
    | These columns will not be added to $fillable array.
    |
    | You can use a string or any valid pattern for the preg_match function.
    | Ex. '/your_pattern/'
    |     '/your_pattern/i' (case-insensitive)
    |     'column_not_to_generate'
    |
    */
    'exclude_columns' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Relationships
    |--------------------------------------------------------------------------
    |
    | These relationships will not be added to the Model class.
    | Ex.
    |   'table_of_starting_relationship' => [
    |       'table_of_relationship',
    |   ],
    |
    */
    'exclude_relationships' => [
    ],
];
