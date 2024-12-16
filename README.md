# Generate Laravel models from an existing database

[![Latest Version on Packagist](https://img.shields.io/packagist/v/giacomomasseron/laravel-models-generator.svg?style=flat-square)](https://packagist.org/packages/giacomomasseron/laravel-models-generator)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/giacomomasseron/laravel-models-generator/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/giacomomasseron/laravel-models-generator/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/giacomomasseron/laravel-models-generator/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/giacomomasseron/laravel-models-generator/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/giacomomasseron/laravel-models-generator.svg?style=flat-square)](https://packagist.org/packages/giacomomasseron/laravel-models-generator)

Generate Laravel models from an existing database.

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
    
    /**
     * Add declare(strict_types=1); to the top of each generated model file
     */
    'strict_types' => true,
    
    /**
     * Add $connection model property
     */
    'connection' => true,
    
    /**
     * Add $table model property
     */
    'table' => true,
    
    /**
     * Add $primaryKey model property
     */
    'primary_key' => true,

    /**
     * Add $primaryKey field to fillable array
     */
    'primary_key_in_fillable' => true,

    'parent' => Illuminate\Database\Eloquent\Model::class,
    'namespace' => 'App\Models',

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
```

## Usage

```bash
php artisan laravel-models-generator:generate
```


## Drivers supported

- MySQL
- SQLite

Coming soon ... all drivers supported by doctrine/dbal.

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
