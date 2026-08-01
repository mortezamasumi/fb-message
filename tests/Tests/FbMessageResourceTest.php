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

it('shows the messages navigation item', function () {
    $this
        ->get(Dashboard::getUrl())
        ->assertSuccessful()
        ->assertSeeText('Messages');
});

it('renders the message index page with all folder tabs', function () {
    $this
        ->get(FbMessageResource::getUrl('index'))
        ->assertSuccessful()
        ->assertSee(MessageFolder::INBOX->getLabel())
        ->assertSee(MessageFolder::SENT->getLabel())
        ->assertSee(MessageFolder::ARCHIVED->getLabel())
        ->assertSee(MessageFolder::TRASHED->getLabel());
});

it('shows received messages only in the inbox tab', function () {
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

    $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::SENT->value,
        ])
        ->assertCanSeeTableRecords([])
        ->assertCountTableRecords(0);
});

it('shows the unread count as the inbox tab badge', function () {
    $messages = FbMessage::factory()
        ->count(3)
        ->to($this->user)
        ->from($this->otherUser)
        ->create();

    $tabs = $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::INBOX->value,
        ])
        ->instance()
        ->getTabs();

    expect($tabs['inbox']->getBadge())->toBe(__digit(3));

    DB::table('fb_message_users')
        ->where('fb_message_user_id', $messages->first()->getKey())
        ->update(['read_at' => now()]);

    $tabs = $this
        ->livewire(ListMessages::class, [
            'activeTab' => MessageFolder::INBOX->value,
        ])
        ->instance()
        ->getTabs();

    expect($tabs['inbox']->getBadge())->toBe(__digit(2));
});

it('shows sent messages only in the sent tab', function () {
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

it('hides messages that belong to other users', function () {
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

it('renders the view page for a received message', function () {
    $message = FbMessage::factory()->to($this->user)->create();

    $this
        ->get(FbMessageResource::getUrl('view', [
            'record' => $message,
        ]))
        ->assertSuccessful()
        ->assertSeeText($message->subject)
        ->assertSeeText($message->body);
});

it('returns a 404 when viewing a message the user does not belong to', function () {
    $message = FbMessage::factory()->from($this->otherUser)->to(User::factory()->create())->create();

    $this
        ->get(FbMessageResource::getUrl('view', [
            'record' => $message,
        ]))
        ->assertNotFound();
});

it('renders the create page', function () {
    $this
        ->get(FbMessageResource::getUrl('create'))
        ->assertSuccessful();
});

it('creates a message and shows it in the sent and inbox folders', function () {
    $formData = [
        'to' => [$this->otherUser->id],
        ...FbMessage::factory()->make()->toArray(),
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

it('archives and unarchives a message', function () {
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

it('trashes and restores a message', function () {
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

it('deletes a trashed message forever', function () {
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
