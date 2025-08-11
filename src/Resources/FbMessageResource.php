<?php

namespace Mortezamasumi\FbMessage\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use Mortezamasumi\FbMessage\Models\FbMessage;
use Mortezamasumi\FbMessage\Resources\Pages\CreateMessage;
use Mortezamasumi\FbMessage\Resources\Pages\ForwardMessage;
use Mortezamasumi\FbMessage\Resources\Pages\ListMessages;
use Mortezamasumi\FbMessage\Resources\Pages\ReplyMessage;
use Mortezamasumi\FbMessage\Resources\Pages\ViewMessage;
use Mortezamasumi\FbMessage\Resources\Schemas\FbMessageForm;
use Mortezamasumi\FbMessage\Resources\Schemas\FbMessageInfolist;
use Mortezamasumi\FbMessage\Resources\Tables\FbMessagesTable;
use BackedEnum;
use UnitEnum;

class FbMessageResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = FbMessage::class;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'forward',
            'reply',
            'delete',
            'archive',
            'trash',
        ];
    }

    public static function getModelLabel(): string
    {
        return __(config('fb-message.navigation.model_label'));
    }

    public static function getPluralModelLabel(): string
    {
        return __(config('fb-message.navigation.plural_model_label'));
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __(config('fb-message.navigation.group'));
    }

    public static function getNavigationParentItem(): ?string
    {
        return __(config('fb-message.navigation.parent_item'));
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return config('fb-message.navigation.icon');
    }

    public static function getActiveNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return config('fb-message.navigation.active_icon') ?? static::getNavigationIcon();
    }

    public static function getNavigationBadge(): ?string
    {
        return config('fb-message.navigation.badge')
            ? Number::format(number: static::getModel()::whereRelation('unread', 'id', Auth::id())->count(), locale: App::getLocale())
            : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return config('fb-message.navigation.badge_tooltip');
    }

    public static function getNavigationSort(): ?int
    {
        return config('fb-message.navigation.sort');
    }

    public static function form(Schema $schema): Schema
    {
        return FbMessageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FbMessageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FbMessagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessages::route('/'),
            'create' => CreateMessage::route('/create'),
            'reply' => ReplyMessage::route('/reply/{record}'),
            'forward' => ForwardMessage::route('/forward/{record}'),
            'view' => ViewMessage::route('/{record}'),
        ];
    }
}
