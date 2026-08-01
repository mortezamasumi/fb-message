<?php

namespace Mortezamasumi\FbMessage\Resources\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Mortezamasumi\FbMessage\Facades\FbMessage;
use Mortezamasumi\FbMessage\Models\FbMessage as FbMessageModel;
use Mortezamasumi\FbMessage\Resources\FbMessageResource;

class ViewMessage extends ViewRecord
{
    protected static string $resource = FbMessageResource::class;

    protected function getHeaderActions(): array
    {
        /** @var FbMessageModel $record */
        $record = $this->getRecord();

        FbMessage::markAsRead($record);

        return [
            Actions\Action::make('reply')
                ->label(__('fb-message::fb-message.actions.reply'))
                ->color('gray')
                ->url($this->getResource()::getUrl('reply', ['record' => $record]))
                ->hidden($record->from()->wherePivot('user_id', Auth::id())->exists())
                ->visible(fn () => Auth::check() && Auth::user()?->can('Reply:FbMessage')),
            Actions\Action::make('forward')
                ->label(__('fb-message::fb-message.actions.forward'))
                ->color('gray')
                ->url($this->getResource()::getUrl('forward', ['record' => $record]))
                ->visible(fn () => Auth::check() && Auth::user()?->can('Forward:FbMessage')),
            Actions\Action::make('return')
                ->label(__('fb-message::fb-message.actions.return'))
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
