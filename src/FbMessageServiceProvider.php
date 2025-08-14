<?php

namespace Mortezamasumi\FbMessage;

use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Gate;
use Livewire\Features\SupportTesting\Testable;
use Mortezamasumi\FbMessage\Models\FbMessage;
use Mortezamasumi\FbMessage\Policies\FbMessagePolicy;
use Mortezamasumi\FbMessage\Testing\TestsFbMessage;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FbMessageServiceProvider extends PackageServiceProvider
{
    public static string $name = 'fb-message';
    public static string $viewNamespace = 'fb-message';

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
            ->hasTranslations()
            ->hasViews(static::$viewNamespace);
    }

    public function packageBooted(): void
    {
        Gate::policy(FbMessage::class, FbMessagePolicy::class);

        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        Testable::mixin(new TestsFbMessage);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'mortezamasumi/fb-message';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            Css::make('fb-message-styles', __DIR__.'/../resources/dist/css/index.css'),
        ];
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
