<?php

namespace Mortezamasumi\FbMessage\Resources\Tables;

use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mortezamasumi\FbEssentials\Facades\FbPersian;
use Mortezamasumi\FbMessage\Facades\FbMessage;

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
                    ->description(fn (?Model $record): ?string => str($record->body)->words(10)),
                TextColumn::make('from')
                    ->label(__('fb-message::fb-message.table.from'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->name)
                    ->hidden(fn (Page $livewire) => $livewire->activeTab === 'sent'),
                TextColumn::make('to')
                    ->label(__('fb-message::fb-message.table.to'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->name)
                    ->hidden(fn (Page $livewire) => $livewire->activeTab === 'inbox'),
                IconColumn::make('attachments.0')
                    ->label(__('fb-message::fb-message.table.attachments'))
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon(''),
                TextColumn::make('created_at')
                    ->label(__('fb-message::fb-message.table.date'))
                    ->sortable()
                    ->formatStateUsing(fn ($state) => FbPersian::jDateTime(null, $state))
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
                    ->visible(fn (Page $livewire) => ($livewire->activeTab === 'inbox' || $livewire->activeTab === 'sent') && Auth::user()->can('Archive:FbMessage'))
                    ->action(fn (Model $record) => FbMessage::archive($record)),
                Action::make('unarchive-message')
                    ->hiddenLabel()
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('primary')
                    ->tooltip(__('fb-message::fb-message.actions.unarchive'))
                    ->visible(
                        fn (Page $livewire) => $livewire->activeTab === 'archived' &&
                            Auth::user()->can('Archive:FbMessage')
                    )
                    ->action(fn (Model $record) => FbMessage::unarchive($record)),
                Action::make('trash-message')
                    ->hiddenLabel()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->tooltip(__('fb-message::fb-message.actions.trash'))
                    ->hidden(fn (Page $livewire) => $livewire->activeTab === 'trashed')
                    ->visible(Auth::user()->can('Trash:FbMessage'))
                    ->action(fn (Model $record) => FbMessage::trash($record)),
                Action::make('restore-message')
                    ->hiddenLabel()
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('success')
                    ->tooltip(__('fb-message::fb-message.actions.restore'))
                    ->visible(fn (Page $livewire) => $livewire->activeTab === 'trashed' && Auth::user()->can('Trash:FbMessage'))
                    ->action(fn (Model $record) => FbMessage::restore($record)),
                Action::make('forget-message')
                    ->hiddenLabel()
                    ->icon('heroicon-o-archive-box-x-mark')
                    ->color('danger')
                    ->tooltip(__('fb-message::fb-message.actions.forget'))
                    ->visible(fn (Page $livewire) => $livewire->activeTab === 'trashed' && Auth::user()->can('Delete:FbMessage'))
                    ->requiresConfirmation()
                    ->modalHeading(
                        fn (Model $record): string => __('filament-actions::force-delete.single.modal.heading', ['label' => $record->subject])
                    )
                    ->modalSubmitActionLabel(__('filament-actions::force-delete.single.modal.actions.delete.label'))
                    ->action(fn (Model $record) => FbMessage::forget($record)),
            ])
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
}
