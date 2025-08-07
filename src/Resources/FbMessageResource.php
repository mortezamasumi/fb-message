<?php

namespace Mortezamasumi\FbMessage\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Mortezamasumi\FbMessage\Models\FbMessage;
use Mortezamasumi\FbMessage\Resources\Pages\CreateMessage;
use Mortezamasumi\FbMessage\Resources\Pages\ForwardMessage;
use Mortezamasumi\FbMessage\Resources\Pages\ListMessages;
use Mortezamasumi\FbMessage\Resources\Pages\ReplyMessage;
use Mortezamasumi\FbMessage\Resources\Pages\ViewMessage;
use Mortezamasumi\FbMessage\Resources\Schemas\FbMessageForm;
use Mortezamasumi\FbMessage\Resources\Schemas\FbMessageInfolist;
use Mortezamasumi\FbMessage\Resources\Tables\FbMessagesTable;
use Mortezamasumi\FbPersian\Facades\FbPersian;

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

    public static function getNavigationIcon(): ?string
    {
        return config('fb-message.resource.navigation.icon');
    }

    public static function getNavigationSort(): ?int
    {
        return config('fb-message.resource.navigation.sort');
    }

    public static function getNavigationLabel(): string
    {
        return __(config('fb-message.resource.navigation.label'));
    }

    public static function getNavigationGroup(): ?string
    {
        return __(config('fb-message.resource.navigation.group'));
    }

    public static function getModelLabel(): string
    {
        return __(config('fb-message.resource.navigation.model_label'));
    }

    public static function getPluralModelLabel(): string
    {
        return __(config('fb-message.resource.navigation.plural_model_label'));
    }

    public static function getNavigationParentItem(): ?string
    {
        return config('fb-message.resource.navigation.parent_item');
    }

    public static function getActiveNavigationIcon(): string|Htmlable|null
    {
        return config('fb-message.resource.navigation.active_icon') ?? static::getNavigationIcon();
    }

    public static function getNavigationBadge(): ?string
    {
        return config('fb-message.resource.navigation.show_count')
            ? FbPersian::digit(static::getModel()::whereRelation('unread', 'id', Auth::id())->count())
            : null;
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
