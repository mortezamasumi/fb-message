# FB Message — Filament Internal Messaging

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mortezamasumi/fb-message.svg?style=flat-square)](https://packagist.org/packages/mortezamasumi/fb-message)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mortezamasumi/fb-message/ci.yml?branch=main&label=tests&style=flat-square)](https://github.com/mortezamasumi/fb-message/actions?query=branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mortezamasumi/fb-message.svg?style=flat-square)](https://packagist.org/packages/mortezamasumi/fb-message)
[![License](https://img.shields.io/packagist/l/mortezamasumi/fb-message.svg?style=flat-square)](LICENSE.md)

An internal messaging and mailbox toolkit for Filament v5. Users can send messages with attachments, manage them across inbox, sent, archived, and trash folders, reply to and forward received messages, and get notified in the panel when a new message arrives.

---

## Features

- **Folders** — inbox, sent, archived, and trash tabs with per-user counts
- **Reply & forward** — respond to or forward a message with the original recipients pre-filled
- **Read tracking** — unread badge on the inbox tab and the navigation item
- **Attachments** — multiple PDF, image, audio, and video files with configurable disk, size, and count limits
- **Database notifications** — recipients are notified in the panel with a link to the message
- **Filament Shield integration** — `view`, `create`, `reply`, `forward`, `archive`, `trash`, and `delete` permissions
- **Localized** — English and Persian (Farsi) translations, Jalali date display via fb-essentials

---

## Installation

```bash
composer require mortezamasumi/fb-message
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="fb-message-migrations"
php artisan migrate
```

Publish the config file:

```bash
php artisan vendor:publish --tag="fb-message-config"
```

---

## Configuration

```php
// config/fb-message.php
return [
    'navigation' => [
        'model_label' => 'fb-message::fb-message.navigation.label',
        'plural_model_label' => 'fb-message::fb-message.navigation.plural_label',
        'group' => null,
        'parent_item' => null,
        'icon' => 'heroicon-o-envelope',
        'active_icon' => 'heroicon-s-envelope',
        'badge' => false,
        'badge_tooltip' => null,
        'sort' => 9999,
    ],
    'max_attachments' => env('MESSAGE_MAX_ATTACHMENTS', 5),
    'max_attachment_size' => env('MESSAGE_MAX_ATTACHMENT_SIZE', 8000),
    'attachment_disk' => env('MESSAGE_ATTACHMENT_DISK', 'public'),
    'attachment_folder' => env('MESSAGE_ATTACHMENT_FOLDER', '/uploads/attachments'),
    'attachment_visibility' => env('MESSAGE_ATTACHMENT_VISIBILITY', 'public'),
];
```

---

## Usage

### Register the plugin in a panel

```php
use Mortezamasumi\FbMessage\FbMessagePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(FbMessagePlugin::make());
}
```

### Recipient scope

Your user model should define a `messageTo` scope to control which users can be selected as recipients. Return `$query` to allow all users:

```php
use Illuminate\Database\Eloquent\Builder;

public function scopeMessageTo(Builder $query): Builder
{
    return $query;
}
```

### Managing messages programmatically

```php
use Mortezamasumi\FbMessage\Facades\FbMessage;

FbMessage::markAsRead($message);   // mark as read for the current user
FbMessage::archive($message);      // move to the archived folder
FbMessage::unarchive($message);    // restore to inbox / sent
FbMessage::trash($message);        // move to the trash folder
FbMessage::restore($message);      // restore from trash
FbMessage::forget($message);       // detach the current user and delete when nobody references it
```

---

## Support policy

| PHP | Laravel | Filament |
| --- | --- | --- |
| 8.3 | 12 | 5.x |

---

## Testing

```bash
composer test
```

---

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

If you discover a security vulnerability, please review our [security policy](.github/SECURITY.md) on how to report it.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

---

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.
