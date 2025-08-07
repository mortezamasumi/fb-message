<?php

namespace Mortezamasumi\Message\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Infolists;
use Filament\Tables;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mortezamasumi\Message\Enums\MessageFolder;
use Mortezamasumi\Message\Enums\MessageType;
use Mortezamasumi\Message\Facades\Message as FacadeMessage;
use Mortezamasumi\Message\Infolists\Components\MessageAttachment;
use Mortezamasumi\Message\Models\Message;
use Mortezamasumi\Message\Resources\MessageResource\Pages;
use Mortezamasumi\Persian\Facades\Persian;

class MessageResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Message::class;

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
        return config('message.resource.navigation.icon');
    }

    public static function getNavigationSort(): ?int
    {
        return config('message.resource.navigation.sort');
    }

    public static function getNavigationLabel(): string
    {
        return __(config('message.resource.navigation.label'));
    }

    public static function getNavigationGroup(): ?string
    {
        return __(config('message.resource.navigation.group'));
    }

    public static function getModelLabel(): string
    {
        return __(config('message.resource.navigation.model_label'));
    }

    public static function getPluralModelLabel(): string
    {
        return __(config('message.resource.navigation.plural_model_label'));
    }

    public static function getNavigationParentItem(): ?string
    {
        return config('message.resource.navigation.parent_item');
    }

    public static function getActiveNavigationIcon(): string|Htmlable|null
    {
        return config('message.resource.navigation.active_icon') ?? static::getNavigationIcon();
    }

    public static function getNavigationBadge(): ?string
    {
        return config('message.resource.navigation.show_count')
            ? Persian::digit(static::getModel()::whereRelation('unread', 'id', Auth::id())->count())
            : null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Select::make('to')
                        ->label(__('message::message.form.to'))
                        ->disabled(fn (string $operation) => $operation === 'reply')
                        ->relationship('availableRecipients')
                        ->multiple()
                        ->preload()
                        ->required()
                        ->getOptionLabelFromRecordUsing(fn (Model $record) => $record->name)
                        ->saveRelationshipsUsing(static function (Forms\Components\Select $component, Model $record, $state) {
                            $relations = [];

                            $relations[Auth::id()] = [
                                'type' => MessageType::FROM,
                                'folder' => MessageFolder::SENT,
                            ];

                            foreach ($state as $to) {
                                $relations[$to] = [
                                    'type' => MessageType::TO,
                                    'folder' => MessageFolder::INBOX,
                                ];
                            }

                            $component->getRelationship()->syncWithoutDetaching($relations);
                        }),
                    Forms\Components\TextInput::make('subject')
                        ->label(__('message::message.form.subject'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('body')
                        ->label(__('message::message.form.body'))
                        ->rows(4),
                    Forms\Components\SpatieMediaLibraryFileUpload::make('attachments')
                        ->label(__('message::message.form.attachments'))
                        ->multiple()
                        ->maxSize(config('message.max-message-attachment-size'))
                        ->maxFiles(3)
                        ->acceptedFileTypes(['application/pdf', 'image/*', 'audio/*', 'video/*'])
                        ->columnSpanFull(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->label(__('message::message.table.subject'))
                    ->words(10)
                    ->searchable()
                    ->sortable()
                    ->description(fn (?Model $record): ?string => str($record->body)->words(10)),
                Tables\Columns\TextColumn::make('from')
                    ->label(__('message::message.table.from'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->name)
                    ->hidden(fn (Page $livewire) => $livewire->activeTab === 'sent'),
                Tables\Columns\TextColumn::make('to')
                    ->label(__('message::message.table.to'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->name)
                    ->hidden(fn (Page $livewire) => $livewire->activeTab === 'inbox'),
                Tables\Columns\IconColumn::make('attachments')
                    ->label(__('message::message.table.attachments'))
                    ->state(fn (Model $record): bool => (bool) $record->getFirstMedia())
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon(''),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('message::message.table.date'))
                    ->sortable()
                    ->formatStateUsing(fn ($state) => Persian::jDateTime(null, $state)),
                //    ->jDateTime(),
            ])
            ->actions([
                Tables\Actions\Action::make('archive-message')
                    ->hiddenLabel()
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('primary')
                    ->tooltip(__('message::message.actions.archive'))
                    ->visible(fn (Page $livewire) => ($livewire->activeTab === 'inbox' || $livewire->activeTab === 'sent') && Auth::user()->can('archive_message'))
                    ->action(fn (Model $record) => FacadeMessage::archive($record)),
                Tables\Actions\Action::make('unarchive-message')
                    ->hiddenLabel()
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('primary')
                    ->tooltip(__('message::message.actions.unarchive'))
                    ->visible(
                        fn (Page $livewire) => $livewire->activeTab === 'archived' &&
                            Auth::user()->can('archive_message')
                    )
                    ->action(fn (Model $record) => FacadeMessage::unarchive($record)),
                Tables\Actions\Action::make('trash-message')
                    ->hiddenLabel()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->tooltip(__('message::message.actions.trash'))
                    ->hidden(fn (Page $livewire) => $livewire->activeTab === 'trashed')
                    ->visible(Auth::user()->can('trash_message'))
                    ->action(fn (Model $record) => FacadeMessage::trash($record)),
                Tables\Actions\Action::make('restore-message')
                    ->hiddenLabel()
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('success')
                    ->tooltip(__('message::message.actions.restore'))
                    ->visible(fn (Page $livewire) => $livewire->activeTab === 'trashed' && Auth::user()->can('trash_message'))
                    ->action(fn (Model $record) => FacadeMessage::restore($record)),
                Tables\Actions\Action::make('forget-message')
                    ->hiddenLabel()
                    ->icon('heroicon-o-archive-box-x-mark')
                    ->color('danger')
                    ->tooltip(__('message::message.actions.forget'))
                    ->visible(fn (Page $livewire) => $livewire->activeTab === 'trashed' && Auth::user()->can('delete_message'))
                    ->requiresConfirmation()
                    ->modalHeading(
                        fn (Model $record): string => __('filament-actions::force-delete.single.modal.heading', ['label' => $record->subject])
                    )
                    ->modalSubmitActionLabel(__('filament-actions::force-delete.single.modal.actions.delete.label'))
                    ->action(fn (Model $record) => FacadeMessage::forget($record)),
            ])
            // ->bulkActions([
            //     Tables\Actions\BulkAction::make('archive-message-bulk')
            //         ->label(__('message::message.actions.archive_selected'))
            //         ->icon('heroicon-o-archive-box-arrow-down')
            //         ->color('primary')
            //         ->deselectRecordsAfterCompletion()
            //         ->visible(fn(Page $livewire) => $livewire->activeTab === 'inbox' || $livewire->activeTab === 'sent')
            //         ->action(fn(Collection $records) => $records->each(fn($record) => FacadeMessage::archive($record))),
            //     Tables\Actions\BulkAction::make('unarchive-message-bulk')
            //         ->label(__('message::message.actions.unarchive_selected'))
            //         ->icon('heroicon-o-document-arrow-up')
            //         ->color('primary')
            //         ->deselectRecordsAfterCompletion()
            //         ->visible(fn(Page $livewire) => $livewire->activeTab === 'archived')
            //         ->action(fn(Collection $records) => $records->each(fn($record) => FacadeMessage::unarchive($record))),
            //     Tables\Actions\BulkAction::make('trash-message-bulk')
            //         ->label(__('message::message.actions.trash_selected'))
            //         ->icon('heroicon-o-trash')
            //         ->color('danger')
            //         ->deselectRecordsAfterCompletion()
            //         ->hidden(fn(Page $livewire) => $livewire->activeTab === 'trashed')
            //         ->action(fn(Collection $records) => $records->each(fn($record) => FacadeMessage::trash($record))),
            //     Tables\Actions\BulkAction::make('restore-message-bulk')
            //         ->label(__('message::message.actions.restore_selected'))
            //         ->icon('heroicon-o-arrow-path-rounded-square')
            //         ->color('success')
            //         ->deselectRecordsAfterCompletion()
            //         ->visible(fn(Page $livewire) => $livewire->activeTab === 'trashed')
            //         ->action(fn(Collection $records) => $records->each(fn($record) => FacadeMessage::restore($record))),
            //     Tables\Actions\BulkAction::make('forget-message-bulk')
            //         ->label(__('message::message.actions.forget_selected'))
            //         ->icon('heroicon-o-archive-box-x-mark')
            //         ->color('danger')
            //         ->deselectRecordsAfterCompletion()
            //         ->visible(fn(Page $livewire) => $livewire->activeTab === 'trashed')
            //         ->requiresConfirmation()
            //         ->modalHeading(
            //             fn(): string => __('filament-actions::force-delete.multiple.modal.heading', ['label' => __('message::message.messages')])
            //         )
            //         ->modalSubmitActionLabel(__('filament-actions::force-delete.multiple.modal.actions.delete.label'))
            //         ->action(fn(Collection $records) => $records->each(fn($record) => FacadeMessage::forget($record))),
            // ])
            ->recordClasses(
                fn (Model $record) => $record->to->filter(fn ($e) => ! $e->pivot->read_at && $e->pivot->user_id === Auth::id())->count()
                    ? 'font-black'
                    : null
            )
            ->defaultSort('created_at', 'desc')
            ->persistSearchInSession()
            ->persistSortInSession()
            ->persistFiltersInSession();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make([
                    Infolists\Components\TextEntry::make('from')
                        ->label(__('message::message.view.from'))
                        ->badge()
                        ->getStateUsing(fn (Model $record) => $record->from->map(fn ($item) => $item->name)),
                    Infolists\Components\TextEntry::make('to')
                        ->label(__('message::message.view.to'))
                        ->badge()
                        ->getStateUsing(fn (Model $record) => $record->to->map(fn ($item) => $item->name)),
                    // Infolists\Components\TextEntry::make('cc')
                    //
                    // ->label('message::message.cc')
                    // ->badge()
                    // ->getStateUsing(fn (Model $record) => $record->cc->pluck('full_name')),
                    Infolists\Components\TextEntry::make('created_at')
                        ->label(__('message::message.view.date'))
                        ->formatStateUsing(fn ($state) => Persian::jDateTime(null, $state)),
                    //    ->jDateTime(),
                    //    ->weight(FontWeight::Black)
                    //    ->localeDigit()
                    //    ->copyable()
                    //    ->copyMessage(__('filament-base::filament-base.copied')),
                    Infolists\Components\TextEntry::make('subject')
                        ->label(__('message::message.view.subject')),
                    Infolists\Components\TextEntry::make('body')
                        ->label(__('message::message.view.body')),
                    MessageAttachment::make('attachments')
                        ->label(__('message::message.view.attachments')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMessages::route('/'),
            'create' => Pages\CreateMessage::route('/create'),
            'reply' => Pages\ReplyMessage::route('/reply/{record}'),
            'forward' => Pages\ForwardMessage::route('/forward/{record}'),
            'view' => Pages\ViewMessage::route('/{record}'),
        ];
    }
}
