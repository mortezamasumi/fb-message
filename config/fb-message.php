<?php

return [
    'user_id_column_type' => 'id',
    'resource' => [
        'navigation' => [
            'icon' => 'heroicon-o-envelope',
            'sort' => 9999,
            'label' => 'fb-message::fb-message.resource.navigation.label',
            'group' => null,
            'model_label' => 'fb-message::fb-message.resource.navigation.message',
            'plural_model_label' => 'fb-message::fb-message.resource.navigation.messages',
            'show_count' => true,
            'group' => null,
            'parent_item' => null,
            'active_icon' => null,
        ],
    ],
    'max-message-attachment-size' => env('MESSAGE_MAX_ATTACH_SIZE', 8000),
];
