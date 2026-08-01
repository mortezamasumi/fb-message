<?php

use Illuminate\Support\Facades\DB;
use Mortezamasumi\FbMessage\Enums\MessageFolder;
use Mortezamasumi\FbMessage\Facades\FbMessage as FbMessageFacade;
use Mortezamasumi\FbMessage\Models\FbMessage as FbMessageModel;
use Mortezamasumi\FbMessage\Tests\Services\FbMessage;
use Mortezamasumi\FbMessage\Tests\Services\User;

beforeEach(function () {
    $this->actingAs($this->user = User::factory()->create());
    $this->otherUser = User::factory()->create();
});

it('marks a message as read for the current user', function () {
    $message = FbMessage::factory()->to($this->user)->from($this->otherUser)->create();

    FbMessageFacade::markAsRead($message);

    expect(
        DB::table('fb_message_users')
            ->where('fb_message_user_id', $message->getKey())
            ->where('user_id', $this->user->id)
            ->value('read_at')
    )->not->toBeNull();
});

it('moves a message to the archived folder', function () {
    $message = FbMessage::factory()->to($this->user)->from($this->otherUser)->create();

    FbMessageFacade::archive($message);

    expect(
        DB::table('fb_message_users')
            ->where('fb_message_user_id', $message->getKey())
            ->where('user_id', $this->user->id)
            ->value('folder')
    )->toBe(MessageFolder::ARCHIVED->value);
});

it('restores an archived received message to the inbox', function () {
    $message = FbMessage::factory()->to($this->user)->from($this->otherUser)->create();

    FbMessageFacade::archive($message);
    FbMessageFacade::unarchive($message);

    expect(
        DB::table('fb_message_users')
            ->where('fb_message_user_id', $message->getKey())
            ->where('user_id', $this->user->id)
            ->value('folder')
    )->toBe(MessageFolder::INBOX->value);
});

it('restores an archived sent message to the sent folder', function () {
    $message = FbMessage::factory()->from($this->user)->to($this->otherUser)->create();

    FbMessageFacade::archive($message);
    FbMessageFacade::unarchive($message);

    expect(
        DB::table('fb_message_users')
            ->where('fb_message_user_id', $message->getKey())
            ->where('user_id', $this->user->id)
            ->value('folder')
    )->toBe(MessageFolder::SENT->value);
});

it('moves a message to the trash folder', function () {
    $message = FbMessage::factory()->to($this->user)->create();

    FbMessageFacade::trash($message);

    expect(
        DB::table('fb_message_users')
            ->where('fb_message_user_id', $message->getKey())
            ->where('user_id', $this->user->id)
            ->value('trashed_at')
    )->not->toBeNull();
});

it('restores a trashed message for the current user', function () {
    $message = FbMessage::factory()->to($this->user)->create();

    FbMessageFacade::trash($message);
    FbMessageFacade::restore($message);

    expect(
        DB::table('fb_message_users')
            ->where('fb_message_user_id', $message->getKey())
            ->where('user_id', $this->user->id)
            ->value('trashed_at')
    )->toBeNull();
});

it('deletes a message when the last user forgets it', function () {
    $message = FbMessage::factory()->to($this->user)->create();

    FbMessageFacade::forget($message);

    expect(FbMessageModel::query()->withoutGlobalScopes()->find($message->getKey()))->toBeNull();
});

it('keeps a message when another user still references it', function () {
    $message = FbMessage::factory()->to($this->user)->from($this->otherUser)->create();

    FbMessageFacade::forget($message);

    expect(FbMessageModel::query()->withoutGlobalScopes()->find($message->getKey()))->not->toBeNull();

    expect(
        DB::table('fb_message_users')
            ->where('fb_message_user_id', $message->getKey())
            ->where('user_id', $this->user->id)
            ->exists()
    )->toBeFalse();
});
