<?php

namespace Mortezamasumi\FbMessage\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Mortezamasumi\FbMessage\Enums\MessageFolder;
use Mortezamasumi\FbMessage\Enums\MessageType;
use Mortezamasumi\FbMessage\Models\Scopes\UserMessagesScope;
use Mortezamasumi\FbMessage\Observers\MessageObserver;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[ObservedBy(MessageObserver::class)]
#[ScopedBy(UserMessagesScope::class)]
class FbMessage extends Model implements HasMedia
{
    use HasFactory;
    use HasUuids;
    use InteractsWithMedia;

    protected $fillable = [
        'id',
        'subject',
        'body',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(100)->height(100);
    }

    public function users(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                config('auth.providers.users.model'),
                'fb_message_user',
                'fb_message_id',
                'user_id',
            )
            ->withPivot(['folder', 'read_at', 'trashed_at', 'type'])
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function availableRecipients(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                config('auth.providers.users.model'),
                'fb_message_user',
                'fb_message_id',
                'user_id',
            )
            ->withPivot(['folder', 'read_at', 'trashed_at', 'type'])
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->when(Auth::check(), fn (Builder $query) => $query->where('id', '<>', Auth::id()))
            ->where(fn (Builder $query) => $query->messageTo());
    }

    public function inbox(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                config('auth.providers.users.model'),
                'fb_message_user',
                'fb_message_id',
                'user_id',
            )
            ->wherePivot('folder', MessageFolder::INBOX)
            ->wherePivot('trashed_at', null)
            ->withPivot(['folder', 'read_at', 'trashed_at', 'type'])
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function unread(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                config('auth.providers.users.model'),
                'fb_message_user',
                'fb_message_id',
                'user_id',
            )
            ->wherePivot('folder', MessageFolder::INBOX)
            ->wherePivot('trashed_at', null)
            ->wherePivot('read_at', null)
            ->withPivot(['folder', 'read_at', 'trashed_at', 'type'])
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function sent(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                config('auth.providers.users.model'),
                'fb_message_user',
                'fb_message_id',
                'user_id',
            )
            ->wherePivot('folder', MessageFolder::SENT)
            ->wherePivot('trashed_at', null)
            ->withPivot(['folder', 'read_at', 'trashed_at', 'type'])
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function archived(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                config('auth.providers.users.model'),
                'fb_message_user',
                'fb_message_id',
                'user_id',
            )
            ->wherePivot('folder', MessageFolder::ARCHIVED)
            ->wherePivot('trashed_at', null)
            ->withPivot(['folder', 'read_at', 'trashed_at', 'type'])
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function trashed(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                config('auth.providers.users.model'),
                'fb_message_user',
                'fb_message_id',
                'user_id',
            )
            ->wherePivot('trashed_at', '<>', null)
            ->withPivot(['folder', 'read_at', 'trashed_at', 'type'])
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function from(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                config('auth.providers.users.model'),
                'fb_message_user',
                'fb_message_id',
                'user_id',
            )
            ->wherePivot('type', MessageType::FROM)
            ->withPivot(['folder', 'read_at', 'trashed_at', 'type'])
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function to(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                config('auth.providers.users.model'),
                'fb_message_user',
                'fb_message_id',
                'user_id',
            )
            ->wherePivot('type', MessageType::TO)
            ->withPivot(['folder', 'read_at', 'trashed_at', 'type'])
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function cc(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                config('auth.providers.users.model'),
                'fb_message_user',
                'fb_message_id',
                'user_id',
            )
            ->wherePivot('type', MessageType::CC)
            ->withPivot(['folder', 'read_at', 'trashed_at', 'type'])
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function bcc(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                config('auth.providers.users.model'),
                'fb_message_user',
                'fb_message_id',
                'user_id',
            )
            ->wherePivot('type', MessageType::BCC)
            ->withPivot(['folder', 'read_at', 'trashed_at', 'type'])
            ->withoutGlobalScope(SoftDeletingScope::class);
    }
}
