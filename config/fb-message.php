<?php

return [
    'navigation' => [
        'model_label' => 'fb-message::fb-message.navigation.label',
        'plural_model_label' => 'fb-message::fb-message.navigation.plural_label',
        'group' => null,
        'parent_item' => null,
        'icon' => 'heroicon-o-envelope',
        'active_icon' => 'heroicon-s-envelope',
        'badge' => false,
        'badge_tooltip' => null,
        'sort' => 9999,
    ],
    'max_attachments' => env('MESSAGE_MAX_ATTACHMENTS', 5),
    'max_attachment_size' => env('MESSAGE_MAX_ATTACHMENT_SIZE', 8000),
    'attachment_disk' => env('MESSAGE_ATTACHMENT_DISK', 'public'),
    'attachment_folder' => env('MESSAGE_ATTACHMENT_FOLDER', 'attachments'),
    'attachment_visibility' => env('MESSAGE_ATTACHMENT_VISIBILITY', 'public'),
];
