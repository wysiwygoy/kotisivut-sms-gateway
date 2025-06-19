# Kotisivut SMS Gateway Notifications Channel for Laravel 5.4

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-notification-channels/kotisivut-sms-gateway.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/kotisivut-sms-gateway)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/wysiwygoy/kotisivut-sms-gateway/master.svg?style=flat-square)](https://travis-ci.org/laravel-notification-channels/kotisivut-sms-gateway)
[![StyleCI](https://styleci.io/repos/113566911/shield)](https://styleci.io/repos/113566911)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/4206e715-184c-4d9f-90ce-cbd7a93a2a2d.svg?style=flat-square)](https://insight.sensiolabs.com/projects/4206e715-184c-4d9f-90ce-cbd7a93a2a2d)
[![Quality Score](https://img.shields.io/scrutinizer/g/wysiwygoy/kotisivut-sms-gateway.svg?style=flat-square)](https://scrutinizer-ci.com/g/wysiwygoy/kotisivut-sms-gateway)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/wysiwygoy/kotisivut-sms-gateway/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/wysiwygoy/kotisivut-sms-gateway/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-notification-channels/kotisivut-sms-gateway.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/kotisivut-sms-gateway)

This package makes it easy to send notifications using [Kotisivut SMS-Gateway](https://www.kotisivut.fi/sovelluspalvelut/sms-gateway/) 
with Laravel 5.4. Kotisivut SMS-Gateway is mainly targeted for Finnish customers.

This is an unofficial package and not developed or endorsed by Kotisivut.

## Contents

- [Installation](#installation)
	- [Setting up the Kotisivut SMS-Gateway service](#setting-up-the-kotisivut-sms-gateway-service)
- [Usage](#usage)
	- [Available Message methods](#available-message-methods)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


## Installation

(Once we get it there) You can install the package via composer:

``` bash
composer require laravel-notification-channels/kotisivut-sms-gateway
```

You must install the service provider:

```php
// config/app.php
'providers' => [
    ...
    NotificationChannels\KotisivutSmsGateway\KotisivutSmsGatewayServiceProvider::class,
],
```

### Setting up the Kotisivut SMS-Gateway service

In order to use [Kotisivut SMS-Gateway](https://www.kotisivut.fi/sovelluspalvelut/sms-gateway/) service
you need to have an account and some
[credits](https://www.kotisivut.fi/store/sovellukset/sms-krediitit/) on the account. 

Then, configure your Kotisivut SMS-Gateway credentials:

```php
// config/services.php
...
'kotisivut-sms-gateway' => [
    'username' => env('KOTISIVUT_USERNAME'),
    'password' => env('KOTISIVUT_PASSWORD'),
    'sender' => env('KOTISIVUT_SENDER'), // Default sender number or name
],
...
```

```bash
# .env
KOTISIVUT_USERNAME=myusername
KOTISIVUT_PASSWORD=mypassword
KOTISIVUT_SENDER=mysender
```

## Usage

You can now use the channel in your `via()` method inside the Notification class.

```php
use NotificationChannels\KotisivutSmsGateway\KotisivutSmsGatewayChannel;
use NotificationChannels\KotisivutSmsGateway\KotisivutSmsGatewayMessage;
use Illuminate\Notifications\Notification;

class InvoicePaid extends Notification
{
    public function via($notifiable)
    {
        return [KotisivutSmsGatewayChannel::class];
    }

    public function toKotisivutSmsGateway($notifiable)
    {
        return new KotisivutSmsGatewayMessage('One of your invoices has been paid!');
    }
}
```

### Routing a message

You can define a receiver of the message in different ways (listed in order of priority):

1. Set the receiver in the message (in `toKotisivutSmsGateway` method of your Notification):

    ```php
        public function toKotisivutSmsGateway($notifiable)
        {
            return new KotisivutSmsGatewayMessage('One of your invoices has been paid!')
                ->receiver('3580123456789');
        }
    ```

2. Define the receiver with `routeNotificationForKotisivutSmsGateway` method in your Notifiable:

    ```php
        public function routeNotificationForKotisivutSmsGateway()
        {
            return $this->phone;
        }
    ```

3. As the last resort the channel looks for a `phone_number` attribute in the Notifiable.

### Available Message methods

- `__construct(string $content = null)`: Constructs a new message, with optional content.
- `content(string $content)`: Sets the content of the message.
- `receiver(string $number)`: Sets the receiver phone number.
- `sender(string $numberOrName)`: Sets the sender phone number or name.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

Running the unit tests:

``` bash
$ composer test
```

Running the integration test:

``` bash
$ composer test tests-integration
```

This expects a `tests-integration/.env` file with 
`KOTISIVUT_USERNAME`, `KOTISIVUT_PASSWORD` and `KOTISIVUT_TEST_RECEIVER` variables defined.

The test sends a real SMS message via Kotisivut SMS-Gateway, so it uses your credits.
Be careful with the receiver phone number.

## Security

If you discover any security related issues, please email info@wysiwyg.fi instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- Jarno Antikainen (https://github.com/jarnoan)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
