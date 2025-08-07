<?php

namespace Mortezamasumi\FbMessage;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Mortezamasumi\FbMessage\Resources\FbMessageResource;

class FbMessagePlugin implements Plugin
{
    public function getId(): string
    {
        return 'fb-message';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->databaseNotifications()
            ->resources([
                FbMessageResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
