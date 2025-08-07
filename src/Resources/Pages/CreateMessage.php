<?php

namespace Mortezamasumi\FbMessage\Resources\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Mortezamasumi\FbMessage\Resources\FbMessageResource;
use Mortezamasumi\FbMessage\Traits\HasCreateNotificationMessage;

class CreateMessage extends CreateRecord
{
    use HasCreateNotificationMessage;

    protected static string $resource = FbMessageResource::class;
    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label(__('fb-message::fb-message.form.send'))
            ->submit('create')
            ->keyBindings(['mod+s']);
    }
}
