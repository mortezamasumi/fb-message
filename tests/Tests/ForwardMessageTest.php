<?php

use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Gate;
use Mortezamasumi\FbMessage\Resources\FbMessageResource;
use Mortezamasumi\FbMessage\Resources\Pages\ForwardMessage;
use Mortezamasumi\FbMessage\Tests\Services\FbMessage;
use Mortezamasumi\FbMessage\Tests\Services\User;

beforeEach(function () {
    $this->actingAs($this->user = User::factory()->create());

    Gate::before(fn () => true);

    $this->otherUser = User::factory()->create();
});

it('renders the forward page for a received message', function () {
    $message = FbMessage::factory()->to($this->user)->from($this->otherUser)->create();

    $this
        ->get(FbMessageResource::getUrl('forward', ['record' => $message]))
        ->assertSuccessful();
});

it('forwards a message to a new recipient', function () {
    $message = FbMessage::factory()->to($this->user)->from($this->otherUser)->create();
    $forwardTo = User::factory()->create();

    $this
        ->livewire(ForwardMessage::class, ['record' => $message->getKey()])
        ->fillForm([
            'to' => [$forwardTo->id],
            'subject' => 'Fwd: '.$message->subject,
            'body' => 'Please review this.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    FilamentNotification::assertNotified(__('fb-message::fb-message.notification.sent'));

    $forwarded = FbMessage::query()->whereKeyNot($message->getKey())->firstOrFail();

    expect($forwarded->subject)->toBe('Fwd: '.$message->subject);
    expect($forwarded->from->pluck('id'))->toContain($this->user->id);
    expect($forwarded->to->pluck('id'))->toContain($forwardTo->id);
});
