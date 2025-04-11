# Generate Laravel models from an existing database

[![Latest Version on Packagist](https://img.shields.io/packagist/v/giacomomasseron/laravel-models-generator.svg?style=flat-square)](https://packagist.org/packages/giacomomasseron/laravel-models-generator)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/giacomomasseron/laravel-models-generator/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/giacomomasseron/laravel-models-generator/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/giacomomasseron/laravel-models-generator/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/giacomomasseron/laravel-models-generator/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/giacomomasseron/laravel-models-generator.svg?style=flat-square)](https://packagist.org/packages/giacomomasseron/laravel-models-generator)

Generate Laravel models from an existing database.

Compatible with Laravel 9/10/11/12.

Major features:  
- PHPStan level 9/10 compliant
- Laravel 11 style
- Polymorphic relationships
- Enums casting
- Rules generation


## Drivers supported

- MariaDB
- MySQL
- SQLite
- PostgreSQL
- SQLServer

Coming soon ... all drivers supported by doctrine/dbal.


## Installation

You can install the package via composer:

```bash
composer require giacomomasseron/laravel-models-generator
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-models-generator-config"
```

This is the contents of the published config file:

```php
return [
    'clean_models_directory_before_generation' => true,

    'generate_views' => false,
    
    /*
    |--------------------------------------------------------------------------
    | Generate rules
    |--------------------------------------------------------------------------
    |
    | Generate rules for the model
    | Ex.
    | $rules = [
    |       'column_name' => 'required|integer',
    |       'column_name2' => 'required|integer',
    | ];
    |
    */
    'rules' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable generation alias
    |--------------------------------------------------------------------------
    |
    | Enable generation alias command:
    | php artisan models:generate
    |
    */
    'enable_alias' => false,

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
    | Define the way relation name are created.
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
    | Enums
    |--------------------------------------------------------------------------
    |
    | Enum(s) implemented by all models
    | Ex.
    |   'column' => EnumClass::class,
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

    /*
    |--------------------------------------------------------------------------
    | Excluded Columns
    |--------------------------------------------------------------------------
    |
    | These columns will not be added to $fillable array.
    |
    | You can use a string or any valid pattern for preg_match function.
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
    | These relationships will not be added to Model class.
    | Ex.
    |   'table_of_starting_relationship' => [
    |       'table_of_relationship',
    |   ],
    |
    */
    'exclude_relationships' => [
    ],

    /*
    | --------------------------------------------------------------------------
    | Define rules in array or in string
    | --------------------------------------------------------------------------
    |
    | This will define the format of the rules in the model.
    | Available formats:
    |  - 'array'
    |  - 'string'
    |
    */
    'rules_format' => 'array',
];
```

## Usage

```bash
php artisan laravel-models-generator:generate
```

## Polymorphic relationships

To add polymorphic relationships to your models, you can use `morphs` array in the config file.  
If you have tables like this:

```
posts
id - integer
name - string

users
id - integer
name - string

images
id - integer
url - string
imageable_id - integer
imageable_type - string
```
And config file like this:

```php
'morphs' => [
    'posts' => 'imageable'
],
```

This relationship will be created in the `Image` model:

```php
public function imageable(): MorphTo
{
    return $this->morphTo(__FUNCTION__, 'imageable_type', 'imageable_id');
}
```

This relationship will be created in the `Post` model:

```php
public function images(): MorphMany
{
    return $this->morphMany(Image::class, 'images');
}
```

## Interfaces

If you want your models implement interface(s), use `interfaces` value in config:

```php
'interfaces' => [
],
```

## Traits

If you want your models use trait(s), use `traits` value in config:

```php
'traits' => [
],
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Giacomo Masseroni](https://github.com/giacomomasseron)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
