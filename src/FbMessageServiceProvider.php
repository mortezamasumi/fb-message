<?php

namespace Mortezamasumi\FbMessage;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Livewire\Features\SupportTesting\Testable;
use Mortezamasumi\FbMessage\Models\FbMessage;
use Mortezamasumi\FbMessage\Policies\FbMessagePolicy;
use Mortezamasumi\FbMessage\Resources\FbMessageResource;
use Mortezamasumi\FbMessage\Testing\TestsFbMessage;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FbMessageServiceProvider extends PackageServiceProvider
{
    public static string $name = 'fb-message';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations();
            })
            ->hasConfigFile()
            ->hasMigrations($this->getMigrations())
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        config(['filament-shield.resources.manage' => [
            ...config('filament-shield.resources.manage') ?? [],
            FbMessageResource::class => [
                'view',
                'view_any',
                'create',
                'forward',
                'reply',
                'delete',
                'archive',
                'trash',
            ]
        ]]);

        Gate::policy(FbMessage::class, FbMessagePolicy::class);

        Testable::mixin(new TestsFbMessage);
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_fb_message_tables',
        ];
    }
}
