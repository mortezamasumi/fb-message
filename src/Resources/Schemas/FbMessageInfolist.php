<?php

namespace Mortezamasumi\FbMessage\Resources\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Mortezamasumi\FbMessage\Resources\Component\MessageAttachment;
use Mortezamasumi\FbPersian\Facades\FbPersian;

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
                MessageAttachment::make('attachments')
                    ->label(__('fb-message::fb-message.view.attachments')),
            ])
            ->columns(1);
    }
}
