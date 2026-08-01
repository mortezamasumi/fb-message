<?php

namespace Mortezamasumi\FbMessage\Resources\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => $record->getAttribute('name'))
                    ->saveRelationshipsUsing(static function (Select $component, $state) {
                        $relationship = $component->getRelationship();

                        if (! $relationship instanceof BelongsToMany) {
                            return;
                        }

                        $sender = Auth::id() ?? throw new \RuntimeException('No authenticated user.');

                        /** @var list<int|string> $recipients */
                        $recipients = $state;

                        $relationship->syncWithoutDetaching([
                            $sender => [
                                'type' => MessageType::FROM,
                                'folder' => MessageFolder::SENT,
                            ],
                        ]);

                        $relationship->syncWithoutDetaching(
                            collect($recipients)
                                ->mapWithKeys(fn ($recipient) => [$recipient => [
                                    'type' => MessageType::TO,
                                    'folder' => MessageFolder::INBOX,
                                ]])
                        );
                    }),
                TextInput::make('subject')
                    ->label(__('fb-message::fb-message.form.subject'))
                    ->required()
                    ->maxLength(255),
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
                        static function ($state) {
                            /** @var list<string> $files */
                            $files = $state;

                            return collect($files)
                                ->map(fn ($file) => ['file' => $file])
                                ->all();
                        }
                    ),
            ])
            ->columns(1);
    }
}
