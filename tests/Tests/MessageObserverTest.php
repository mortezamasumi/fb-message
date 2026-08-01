<?php

use Illuminate\Support\Facades\Event;
use Mortezamasumi\FbMessage\Events\MessageEvent;
use Mortezamasumi\FbMessage\Models\FbMessage;
use Mortezamasumi\FbMessage\Tests\Services\FbMessage as TestFbMessage;
use Mortezamasumi\FbMessage\Tests\Services\User;

beforeEach(function () {
    $this->actingAs($this->user = User::factory()->create());
});

it('dispatches a MessageEvent when a message is created', function () {
    $dispatched = null;

    Event::listen(MessageEvent::class, function (MessageEvent $event) use (&$dispatched) {
        $dispatched = $event;
    });

    $message = TestFbMessage::factory()->create();

    expect($dispatched)->not->toBeNull()
        ->and($dispatched->message->is($message))->toBeTrue()
        ->and($dispatched->type)->toBe('new-message')
        ->and($dispatched->sender)->toBe($this->user->id)
        ->and($dispatched->message)->toBeInstanceOf(FbMessage::class);
});
