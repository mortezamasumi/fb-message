<?php

namespace Mortezamasumi\FbMessage;

use Illuminate\Support\Facades\Auth;
use Mortezamasumi\FbMessage\Enums\MessageFolder;
use Mortezamasumi\FbMessage\Enums\MessageType;
use Mortezamasumi\FbMessage\Models\FbMessage as FbMessageModel;

class FbMessage
{
    public function markAsRead(FbMessageModel $record): void
    {
        $record
            ->users()
            ->updateExistingPivot(Auth::id(), ['read_at' => now()]);
    }

    public function archive(FbMessageModel $record): void
    {
        $record
            ->users()
            ->updateExistingPivot(Auth::id(), ['folder' => MessageFolder::ARCHIVED]);
    }

    public function unarchive(FbMessageModel $record): void
    {
        $record
            ->users()
            ->wherePivot('type', MessageType::FROM)
            ->updateExistingPivot(Auth::id(), ['folder' => MessageFolder::SENT]);

        $record
            ->users()
            ->wherePivotIn('type', [MessageType::TO, MessageType::CC, MessageType::BCC])
            ->updateExistingPivot(Auth::id(), ['folder' => MessageFolder::INBOX]);
    }

    public function trash(FbMessageModel $record): void
    {
        $record
            ->users()
            ->updateExistingPivot(Auth::id(), ['trashed_at' => now()]);
    }

    public function restore(FbMessageModel $record): void
    {
        $record
            ->users()
            ->updateExistingPivot(Auth::id(), ['trashed_at' => null]);
    }

    public function forget(FbMessageModel $record): void
    {
        $record->users()->detach(Auth::id());

        if (! $record->users->count()) {
            $record->delete();
        }
    }
}
