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
];
```

## Usage

```bash
php artisan laravel-models-generator:generate
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
