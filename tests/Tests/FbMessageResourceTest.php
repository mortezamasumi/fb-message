<?php

use Filament\Notifications\Notification as FilamentNotification;
use Filament\Pages\Dashboard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mortezamasumi\FbMessage\Enums\MessageFolder;
use Mortezamasumi\FbMessage\Resources\FbMessageResource;
use Mortezamasumi\FbMessage\Resources\Pages\CreateMessage;
use Mortezamasumi\FbMessage\Resources\Pages\ListMessages;
use Mortezamasumi\FbMessage\Tests\Services\FbMessage;
use Mortezamasumi\FbMessage\Tests\Services\User;

beforeEach(function () {
    $this->actingAs($this->user = User::factory()->create());

    Gate::before(fn () => true);

    $this->otherUser = User::factory()->create();
});

it('can see message navigation', function () {
    $this
        ->get(Dashboard::getUrl())
        ->assertSuccessful()
        ->assertSeeText('Messages');
});

it('can render message index page', function () {
    $this
        ->get(FbMessageResource::getUrl('index'))
        ->assertSuccessful()
        ->assertSee(MessageFolder::INBOX->getLabel())
        ->assertSee(MessageFolder::SENT->getLabel())
        ->assertSee(MessageFolder::ARCHIVED->getLabel())
        ->assertSee(MessageFolder::TRASHED->getLabel());
});

it('can show messages in inbox and not in sent', function () {
    $count = 5;
    $messages = FbMessage::factory()
        ->count($count)
        ->to($this->user)
        ->from($this->otherUser)
        ->create();

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::INBOX->value,
        ])
        ->assertCanSeeTableRecords($messages)
        ->assertCountTableRecords($count);

    return;
    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::SENT->value,
        ])
        ->assertCanSeeTableRecords([])
        ->assertCountTableRecords(0);
});

it('can show only unread count messages', function () {
    $numberOfMessages = rand(15, 50);
    $numberOfReadMessages = rand(1, $numberOfMessages);

    $numberOfMessages = 1;
    $numberOfReadMessages = 1;

    FbMessage::factory()
        ->count($numberOfMessages)
        ->to($this->user)
        ->from($this->otherUser)
        ->create();

    $tabs = $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::INBOX->value,
        ])
        ->instance()
        ->getTabs();

    expect($tabs['inbox']->getBadge())
        ->toBe(__digit($numberOfMessages));

    Db::table('fb_message_users')
        ->limit($numberOfReadMessages)
        ->update(['read_at' => now()]);

    $tabs = $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::INBOX->value,
        ])
        ->instance()
        ->getTabs();

    expect($tabs['inbox']->getBadge())
        ->toBe(__digit($numberOfMessages - $numberOfReadMessages));
});

it('can show sent messages', function () {
    $count = 5;
    $messages = FbMessage::factory()
        ->count($count)
        ->from($this->user)
        ->to($this->otherUser)
        ->create();

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::SENT->value,
        ])
        ->assertCanSeeTableRecords($messages)
        ->assertCountTableRecords($count);

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::INBOX->value,
        ])
        ->assertCanSeeTableRecords([])
        ->assertCountTableRecords(0);
});

it('can not show messages belongs to other users', function () {
    FbMessage::factory()->from($this->otherUser)->to(User::factory()->create())->create();

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::INBOX->value,
        ])
        ->assertCanSeeTableRecords([])
        ->assertCountTableRecords(0);

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::SENT->value,
        ])
        ->assertCanSeeTableRecords([])
        ->assertCountTableRecords(0);
});

it('can render view page', function () {
    $message = FbMessage::factory()->to($this->user)->create();

    $this
        ->get(FbMessageResource::getUrl('view', [
            'record' => $message,
        ]))
        ->assertSuccessful()
        ->assertSeeText($message->subject)
        ->assertSeeText($message->body);
});

it('can render create page', function () {
    $this
        ->get(FbMessageResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create message and show in sent and inbox', function () {
    $formData = [
        'to' => [$this->otherUser->id],
        ...FbMessage::factory()->make()->toArray()
    ];

    $this
        ->livewire(CreateMessage::class)
        ->fillForm($formData)
        ->assertFormSet($formData)
        ->call('create')
        ->assertHasNoFormErrors();

    FilamentNotification::assertNotified(__('fb-message::fb-message.notification.sent'));

    $messages = FbMessage::all();

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::SENT->value,
        ])
        ->assertCanSeeTableRecords($messages);

    $this
        ->actingAs($this->otherUser)
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::INBOX->value,
        ])
        ->assertCanSeeTableRecords($messages);
});

return;

it('can archive/unarchice message', function () {
    $message = FbMessage::factory()->to($this->user)->create();

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::INBOX->value,
        ])
        ->callTableAction('archive-message', $message);

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::ARCHIVED->value,
        ])
        ->assertCanSeeTableRecords([$message])
        ->assertCountTableRecords(1)
        ->callTableAction('unarchive-message', $message);

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::INBOX->value,
        ])
        ->assertCanSeeTableRecords([$message])
        ->assertCountTableRecords(1);
});

it('can trash/restore message', function () {
    $message = FbMessage::factory()->to($this->user)->create();

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::INBOX->value,
        ])
        ->callTableAction('trash-message', $message);

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::TRASHED->value,
        ])
        ->assertCanSeeTableRecords([$message])
        ->assertCountTableRecords(1)
        ->callTableAction('restore-message', $message);

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::INBOX->value,
        ])
        ->assertCanSeeTableRecords([$message])
        ->assertCountTableRecords(1);
});

it('can delete trashed message forever', function () {
    $message = FbMessage::factory()->to($this->user)->create();

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::INBOX->value,
        ])
        ->callTableAction('trash-message', $message);

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::TRASHED->value,
        ])
        ->assertCanSeeTableRecords([$message])
        ->assertCountTableRecords(1)
        ->callTableAction('forget-message', $message);

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::TRASHED->value,
        ])
        ->assertCanSeeTableRecords([])
        ->assertCountTableRecords(0);
});
