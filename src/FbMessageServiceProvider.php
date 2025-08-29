<?php

namespace Mortezamasumi\FbMessage;

use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;
use Livewire\Features\SupportTesting\Testable;
use Mortezamasumi\FbMessage\Models\FbMessage;
use Mortezamasumi\FbMessage\Policies\FbMessagePolicy;
use Mortezamasumi\FbMessage\Testing\TestsFbMessage;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Route;

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
        Gate::policy(FbMessage::class, FbMessagePolicy::class);

        Route::get('/fb-message-assets/{filename}', function ($filename) {
            $path = __DIR__.'/../resources/images/'.$filename;
            if (! file_exists($path)) {
                abort(404);
            }

            return Response::file($path);
        });

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
