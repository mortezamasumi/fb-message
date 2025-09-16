<?php

namespace Mortezamasumi\FbMessage\Resources\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mortezamasumi\FbEssentials\Facades\FbPersian;

class FbMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('from')
                    ->label(__('fb-message::fb-message.view.from'))
                    ->badge()
                    ->getStateUsing(fn (Model $record) => $record->from->map(fn ($item) => $item->name)),
                TextEntry::make('to')
                    ->label(__('fb-message::fb-message.view.to'))
                    ->badge()
                    ->getStateUsing(fn (Model $record) => $record->to->map(fn ($item) => $item->name)),
                // TextEntry::make('cc')
                //
                // ->label('fb-message::fb-message.cc')
                // ->badge()
                // ->getStateUsing(fn (Model $record) => $record->cc->pluck('full_name')),
                TextEntry::make('created_at')
                    ->label(__('fb-message::fb-message.view.date'))
                    ->formatStateUsing(fn ($state) => FbPersian::jDateTime(null, $state))
                    ->jDateTime(),
                //    ->weight(FontWeight::Black)
                //    ->localeDigit()
                //    ->copyable()
                //    ->copyMessage(__('filament-base::filament-base.copied')),
                TextEntry::make('subject')
                    ->label(__('fb-message::fb-message.view.subject')),
                TextEntry::make('body')
                    ->label(__('fb-message::fb-message.view.body')),
                RepeatableEntry::make('attachments')
                    ->label(__('fb-message::fb-message.form.attachments'))
                    ->extraAttributes(['class' => 'flex justify-center'])
                    ->schema([
                        ImageEntry::make('file')
                            ->hiddenLabel()
                            ->disk(config('fb-message.attachment_disk'))
                            ->square()
                            ->imageSize(50)
                            ->url(fn (?string $state): string => Storage::disk(config('fb-message.attachment_disk'))->url($state), true)
                            ->visible(fn ($state) => Str::startsWith(Storage::disk(config('fb-message.attachment_disk'))->mimeType($state), 'image/')),
                        TextEntry::make('file')
                            ->hiddenLabel()
                            ->url(fn (?string $state): string => Storage::disk(config('fb-message.attachment_disk'))->url($state), true)
                            ->html()
                            ->formatStateUsing(fn () => '<img src="/fb-essentials-assets/pdf.png" style="max-width: 50px; max-height: 50px;" />')
                            ->visible(fn ($state) => Str::contains(Storage::disk(config('fb-message.attachment_disk'))->mimeType($state), 'pdf')),
                        TextEntry::make('file')
                            ->hiddenLabel()
                            ->url(fn (?string $state): string => Storage::disk(config('fb-message.attachment_disk'))->url($state), true)
                            ->html()
                            ->formatStateUsing(fn () => '<img src="/fb-essentials-assets/audio.png" style="max-width: 50px; max-height: 50px;" />')
                            ->visible(fn ($state) => Str::startsWith(Storage::disk(config('fb-message.attachment_disk'))->mimeType($state), 'audio/')),
                        TextEntry::make('file')
                            ->hiddenLabel()
                            ->url(fn (?string $state): string => Storage::disk(config('fb-message.attachment_disk'))->url($state), true)
                            ->html()
                            ->formatStateUsing(fn () => '<img src="/fb-essentials-assets/video.png" style="max-width: 50px; max-height: 50px;" />')
                            ->visible(fn ($state) => Str::startsWith(Storage::disk(config('fb-message.attachment_disk'))->mimeType($state), 'video/')),
                    ])
            ])
            ->columns(1);
    }
}
