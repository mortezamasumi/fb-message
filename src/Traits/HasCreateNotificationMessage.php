<?php

namespace Mortezamasumi\FbMessage\Traits;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Mortezamasumi\FbMessage\Models\FbMessage;

trait HasCreateNotificationMessage
{
    protected function getCreatedNotificationMessage(): ?string
    {
        /** @var FbMessage $record */
        $record = $this->getRecord();

        Notification::make()
            ->title(__('fb-message::fb-message.notification.title', ['name' => $record->from->first()?->getAttribute('name')]))
            ->actions([
                Action::make('view')
                    ->label(__('fb-message::fb-message.notification.view'))
                    ->button()
                    ->url($this->getResource()::getUrl('view', ['record' => $record->id]))
                    ->markAsRead()
                    ->close(),
            ])
            ->sendToDatabase($record->to->union($record->cc)->union($record->bcc));

        return __('fb-message::fb-message.notification.sent');
    }
}
