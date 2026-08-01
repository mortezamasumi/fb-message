<?php

namespace Mortezamasumi\FbMessage\Resources\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Mortezamasumi\FbMessage\Facades\FbMessage;
use Mortezamasumi\FbMessage\Models\FbMessage as FbMessageModel;
use Mortezamasumi\FbMessage\Resources\Pages\ListMessages;

class FbMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->label(__('fb-message::fb-message.table.subject'))
                    ->words(10)
                    ->searchable()
                    ->sortable()
                    ->description(fn (?FbMessageModel $record): string => str($record?->body)->words(10)),
                TextColumn::make('from')
                    ->label(__('fb-message::fb-message.table.from'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->name)
                    ->hidden(fn (ListMessages $livewire) => $livewire->activeTab === 'sent'),
                TextColumn::make('to')
                    ->label(__('fb-message::fb-message.table.to'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->name)
                    ->hidden(fn (ListMessages $livewire) => $livewire->activeTab === 'inbox'),
                IconColumn::make('attachments.0')
                    ->label(__('fb-message::fb-message.table.attachments'))
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon(''),
                TextColumn::make('created_at')
                    ->label(__('fb-message::fb-message.table.date'))
                    ->sortable()
                    ->formatStateUsing(fn ($state) => __jdatetime(null, $state))
                    ->jDateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('archive-message')
                    ->hiddenLabel()
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('primary')
                    ->tooltip(__('fb-message::fb-message.actions.archive'))
                    ->visible(fn (ListMessages $livewire) => ($livewire->activeTab === 'inbox' || $livewire->activeTab === 'sent') && Auth::check() && Auth::user()?->can('Archive:FbMessage'))
                    ->action(fn (FbMessageModel $record) => FbMessage::archive($record)),
                Action::make('unarchive-message')
                    ->hiddenLabel()
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('primary')
                    ->tooltip(__('fb-message::fb-message.actions.unarchive'))
                    ->visible(
                        fn (ListMessages $livewire) => $livewire->activeTab === 'archived' &&
                            Auth::check() && Auth::user()?->can('Archive:FbMessage')
                    )
                    ->action(fn (FbMessageModel $record) => FbMessage::unarchive($record)),
                Action::make('trash-message')
                    ->hiddenLabel()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->tooltip(__('fb-message::fb-message.actions.trash'))
                    ->hidden(fn (ListMessages $livewire) => $livewire->activeTab === 'trashed')
                    ->visible(Auth::check() && Auth::user()?->can('Trash:FbMessage'))
                    ->action(fn (FbMessageModel $record) => FbMessage::trash($record)),
                Action::make('restore-message')
                    ->hiddenLabel()
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('success')
                    ->tooltip(__('fb-message::fb-message.actions.restore'))
                    ->visible(fn (ListMessages $livewire) => $livewire->activeTab === 'trashed' && Auth::check() && Auth::user()?->can('Trash:FbMessage'))
                    ->action(fn (FbMessageModel $record) => FbMessage::restore($record)),
                Action::make('forget-message')
                    ->hiddenLabel()
                    ->icon('heroicon-o-archive-box-x-mark')
                    ->color('danger')
                    ->tooltip(__('fb-message::fb-message.actions.forget'))
                    ->visible(fn (ListMessages $livewire) => $livewire->activeTab === 'trashed' && Auth::check() && Auth::user()?->can('Delete:FbMessage'))
                    ->requiresConfirmation()
                    ->modalHeading(
                        fn (FbMessageModel $record): string => __('filament-actions::force-delete.single.modal.heading', ['label' => $record->subject])
                    )
                    ->modalSubmitActionLabel(__('filament-actions::force-delete.single.modal.actions.delete.label'))
                    ->action(fn (FbMessageModel $record) => FbMessage::forget($record)),
            ])
            ->recordClasses(
                fn (FbMessageModel $record) => $record->to()
                    ->wherePivot('read_at', null)
                    ->wherePivot('user_id', Auth::id())
                    ->exists()
                    ? 'font-black'
                    : null
            )
            ->defaultSort('created_at', 'desc')
            ->persistSearchInSession()
            ->persistSortInSession()
            ->persistFiltersInSession();
    }
}
