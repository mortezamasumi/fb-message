<?php

namespace Mortezamasumi\FbMessage\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mortezamasumi\FbMessage\Models\FbMessage;

class MessageEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public FbMessage $message,
        public string $type,
        public string|int $sender
    ) {}
}
