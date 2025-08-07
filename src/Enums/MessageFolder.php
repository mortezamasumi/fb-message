<?php

namespace Mortezamasumi\FbMessage\Enums;

enum MessageFolder: string
{
    case INBOX = 'inbox';
    case SENT = 'sent';
    case ARCHIVED = 'archived';
    case TRASHED = 'trashed';

    public function getLabel(): string
    {
        return match ($this) {
            self::INBOX => __('fb-message::fb-message.folders.inbox'),
            self::SENT => __('fb-message::fb-message.folders.sent'),
            self::ARCHIVED => __('fb-message::fb-message.folders.archived'),
            self::TRASHED => __('fb-message::fb-message.folders.trashed'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::INBOX => 'heroicon-o-inbox-arrow-down',
            self::SENT => 'heroicon-o-arrow-up-tray',
            self::ARCHIVED => 'heroicon-o-archive-box',
            self::TRASHED => 'heroicon-o-trash',
        };
    }
}
