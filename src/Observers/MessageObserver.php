<?php

namespace Mortezamasumi\FbMessage\Observers;

use Illuminate\Support\Facades\Auth;
use Mortezamasumi\FbMessage\Events\MessageEvent;
use Mortezamasumi\FbMessage\Models\FbMessage;

class MessageObserver
{
    public function created(FbMessage $message): void
    {
        MessageEvent::dispatch($message, 'new-message', Auth::id());
    }
}
