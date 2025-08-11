<?php

return [
    'navigation' => [
        'model_label' => 'fb-message::fb-message.navigation.label',
        'plural_model_label' => 'fb-message::fb-message.navigation.plural_label',
        'group' => null,
        'parent_item' => null,
        'icon' => 'heroicon-o-envelope',
        'active_icon' => null,
        'badge' => false,
        'badge_tooltip' => null,
        'sort' => 9999,
    ],
    'max-message-attachment-size' => env('MESSAGE_MAX_ATTACH_SIZE', 8000),
];
