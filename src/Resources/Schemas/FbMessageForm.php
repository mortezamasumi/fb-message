<?php

namespace Mortezamasumi\FbMessage\Resources\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
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
                        $component
                            ->getRelationship()
                            ->syncWithoutDetaching([
                                Auth::id() => [
                                    'type' => MessageType::FROM,
                                    'folder' => MessageFolder::SENT,
                                ]
                            ]);

                        $component
                            ->getRelationship()
                            ->syncWithoutDetaching(
                                collect($state)
                                    ->mapWithKeys(fn ($s) => [$s => [
                                        'type' => MessageType::TO,
                                        'folder' => MessageFolder::INBOX,
                                    ]])
                            );
                    }),
                TextInput::make('subject')
                    ->label(__('fb-message::fb-message.form.subject'))
                    ->required()
                    ->maxLength(255)
                    ->default('aaaaaaa'),
                Textarea::make('body')
                    ->label(__('fb-message::fb-message.form.body'))
                    ->rows(4),
                FileUpload::make('attachments')
                    ->label(__('fb-message::fb-message.form.attachments'))
                    ->multiple()
                    ->acceptedFileTypes(['application/pdf', 'image/*', 'audio/*', 'video/*'])
                    ->maxFiles(config('fb-message.max_attachments'))
                    ->maxSize(config('fb-message.max_attachment_size'))
                    ->disk(config('fb-message.attachment_disk'))
                    ->directory(config('fb-message.attachment_folder'))
                    ->visibility(config('fb-message.attachment_visibility'))
                    ->columnSpanFull()
                    ->dehydrateStateUsing(
                        fn ($state) => array_map(fn ($file) => ['file' => $file], $state)
                    )
            ])
            ->columns(1);
    }
}
