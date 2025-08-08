<?php

namespace Mortezamasumi\FbMessage\Resources\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mortezamasumi\FbMessage\Enums\MessageFolder;
use Mortezamasumi\FbMessage\Enums\MessageType;

class FbMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('to')
                    ->label(__('fb-message::fb-message.form.to'))
                    ->relationship('availableRecipients')
                    ->multiple()
                    ->preload()
                    ->disabledOn('reply')
                    ->required()
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => $record->name)
                    ->saveRelationshipsUsing(static function (Select $component, $state) {
                        $relations = collect([Auth::id() => [
                            'type' => MessageType::FROM,
                            'folder' => MessageFolder::SENT,
                        ]])->merge(collect($state)->mapWithKeys(fn ($s) => [$s => [
                            'type' => MessageType::TO,
                            'folder' => MessageFolder::INBOX,
                        ]]));

                        $component->getRelationship()->syncWithoutDetaching($relations);
                    }),
                TextInput::make('subject')
                    ->label(__('fb-message::fb-message.form.subject'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('body')
                    ->label(__('fb-message::fb-message.form.body'))
                    ->rows(4),
                SpatieMediaLibraryFileUpload::make('attachments')
                    ->label(__('fb-message::fb-message.form.attachments'))
                    ->disk('public')
                    ->visibility('public')
                    ->multiple()
                    ->maxSize(config('fb-message.max-message-attachment-size'))
                    ->maxFiles(3)
                    ->acceptedFileTypes(['application/pdf', 'image/*', 'audio/*', 'video/*'])
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }
}
