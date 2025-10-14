<?php

namespace Mortezamasumi\FbMessage\Tests\Services;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mortezamasumi\FbMessage\Enums\MessageFolder;
use Mortezamasumi\FbMessage\Enums\MessageType;
use Mortezamasumi\FbMessage\Tests\Services\User;

class FbMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject' => fake()->sentence(),
            'body' => fake()->text(),
        ];
    }

    public function to($user): static
    {
        return $this->hasAttached(
            $user,
            [
                'user_type' => $user->getMorphClass(),
                'fb_message_user_type' => 'Mortezamasumi\FbMessage\Models\FbMessage',
                'type' => MessageType::TO,
                'folder' => MessageFolder::INBOX,
            ]
        );
    }

    public function from($user): static
    {
        return $this->hasAttached(
            $user,
            [
                'user_type' => $user->getMorphClass(),
                'fb_message_user_type' => 'Mortezamasumi\FbMessage\Models\FbMessage',
                'type' => MessageType::FROM,
                'folder' => MessageFolder::SENT,
            ]
        );
    }
}
