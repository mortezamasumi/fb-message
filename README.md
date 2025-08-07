# This is my package fb-message

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mortezamasumi/fb-message.svg?style=flat-square)](https://packagist.org/packages/mortezamasumi/fb-message)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mortezamasumi/fb-message/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mortezamasumi/fb-message/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/mortezamasumi/fb-message/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/mortezamasumi/fb-message/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mortezamasumi/fb-message.svg?style=flat-square)](https://packagist.org/packages/mortezamasumi/fb-message)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require mortezamasumi/fb-message
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="fb-message-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="fb-message-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="fb-message-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$fbMessage = new Mortezamasumi\FbMessage();
echo $fbMessage->echoPhrase('Hello, Mortezamasumi!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Morteza Masumi](https://github.com/mortezamasumi)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
