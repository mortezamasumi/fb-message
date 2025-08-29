<?php

namespace Mortezamasumi\FbMessage\Traits;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

trait HasCreateNotificationMessage
{
    protected function getCreatedNotificationMessage(): ?string
    {
        // dd($this->getRecord()->from);
        Notification::make()
            ->title(__('fb-message::fb-message.notification.title', ['name' => $this->getRecord()->from->first()?->name]))
            ->actions([
                Action::make('view')
                    ->label(__('fb-message::fb-message.notification.view'))
                    ->button()
                    ->url($this->getResource()::getUrl('view', ['record' => $this->getRecord()->id]))
                    ->markAsRead()
                    ->close(),
            ])
            ->sendToDatabase($this->getRecord()->to->union($this->getRecord()->cc, $this->getRecord()->bcc));

        DB::table('notifications')
            ->where('notifiable_type', config('auth.providers.users.model'))
            ->update([
                'notifiable_type' => config('auth.providers.users.model')
            ]);

        return __('fb-message::fb-message.notification.sent');
    }
}
