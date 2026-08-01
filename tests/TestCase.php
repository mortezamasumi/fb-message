<?php

namespace Mortezamasumi\FbMessage\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Mortezamasumi\FbEssentials\FbEssentialsPlugin;
use Mortezamasumi\FbEssentials\FbEssentialsServiceProvider;
use Mortezamasumi\FbMessage\FbMessagePlugin;
use Mortezamasumi\FbMessage\FbMessageServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Orchestra\Workbench\WorkbenchServiceProvider;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

class TestCase extends TestbenchTestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app)
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->uuidMorphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Filament::registerPanel(
            Panel::make()
                ->id('admin')
                ->path('/')
                ->login()
                ->default()
                ->pages([
                    Dashboard::class,
                ])
                ->plugins([
                    FbEssentialsPlugin::make(),
                    FbMessagePlugin::make(),
                ])
        );
    }

    protected function defineDatabaseMigrations()
    {
        $this->artisan('vendor:publish', ['--tag' => 'fb-message-migrations']);
    }

    protected function getPackageProviders($app)
    {
        return [
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            ActionsServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            LivewireServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            WorkbenchServiceProvider::class,
            FbEssentialsServiceProvider::class,
            FbMessageServiceProvider::class,
        ];
    }
}
