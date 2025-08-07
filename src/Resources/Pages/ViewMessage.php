<?php

namespace Mortezamasumi\FbMessage\Resources\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Mortezamasumi\FbMessage\Facades\FbMessage;
use Mortezamasumi\FbMessage\Resources\FbMessageResource;

class ViewMessage extends ViewRecord
{
    protected static string $resource = FbMessageResource::class;

    protected function getHeaderActions(): array
    {
        FbMessage::markAsRead($this->getRecord());

        return [
            Actions\Action::make('reply')
                ->label(__('fb-message::fb-message.actions.reply'))
                ->color('gray')
                ->url($this->getResource()::getUrl('reply', ['record' => $this->getRecord()]))
                ->hidden($this->record->from()->wherePivot('user_id', Auth::id())->exists())
                ->visible(Auth::user()->can('reply_message')),
            Actions\Action::make('forward')
                ->label(__('fb-message::fb-message.actions.forward'))
                ->color('gray')
                ->url($this->getResource()::getUrl('forward', ['record' => $this->getRecord()]))
                ->visible(Auth::user()->can('forward_message')),
            Actions\Action::make('return')
                ->label(__('fb-message::fb-message.actions.return'))
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
