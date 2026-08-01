<?php

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fb_messages', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('body')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
        });

        $userModel = config('auth.providers.users.model');
        $usesUuids = class_exists($userModel) && in_array(HasUuids::class, class_uses_recursive($userModel), true);

        Schema::create('fb_message_users', function (Blueprint $table) use ($usesUuids) {
            if ($usesUuids) {
                $table->nullableUuidMorphs('fb_message_user', 'fb_message_user');
            } else {
                $table->nullableMorphs('fb_message_user', 'fb_message_user');
            }
            $table->{$usesUuids ? 'uuid' : 'unsignedBigInteger'}('user_id');
            $table->string('user_type')->nullable();
            $table->string('type');
            $table->string('folder');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('trashed_at')->nullable();

            $table->index(['user_id', 'user_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fb_message_users');
        Schema::dropIfExists('fb_messages');
    }
};
