<?php

use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Gate;
use Mortezamasumi\FbMessage\Resources\FbMessageResource;
use Mortezamasumi\FbMessage\Resources\Pages\ReplyMessage;
use Mortezamasumi\FbMessage\Tests\Services\FbMessage;
use Mortezamasumi\FbMessage\Tests\Services\User;

beforeEach(function () {
    $this->actingAs($this->user = User::factory()->create());

    Gate::before(fn () => true);

    $this->otherUser = User::factory()->create();
});

it('renders the reply page for a received message', function () {
    $message = FbMessage::factory()->to($this->user)->from($this->otherUser)->create();

    $this
        ->get(FbMessageResource::getUrl('reply', ['record' => $message]))
        ->assertSuccessful();
});

it('creates a reply delivered to the original sender', function () {
    $message = FbMessage::factory()->to($this->user)->from($this->otherUser)->create();

    $this
        ->livewire(ReplyMessage::class, ['record' => $message->getKey()])
        ->fillForm([
            'subject' => 'Re: '.$message->subject,
            'body' => 'Thanks for your message.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    FilamentNotification::assertNotified(__('fb-message::fb-message.notification.sent'));

    $reply = FbMessage::query()->whereKeyNot($message->getKey())->firstOrFail();

    expect($reply->subject)->toBe('Re: '.$message->subject);
    expect($reply->from->pluck('id'))->toContain($this->user->id);
    expect($reply->to->pluck('id'))->toContain($this->otherUser->id);
});
