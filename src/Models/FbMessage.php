<?php

namespace Mortezamasumi\FbMessage\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Mortezamasumi\FbMessage\Enums\MessageFolder;
use Mortezamasumi\FbMessage\Enums\MessageType;
use Mortezamasumi\FbMessage\Models\Scopes\UserMessagesScope;
use Mortezamasumi\FbMessage\Observers\MessageObserver;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[ObservedBy(MessageObserver::class)]
#[ScopedBy(UserMessagesScope::class)]
class FbMessage extends Model
{
    use HasFactory;
    // use HasUuids;

    protected $fillable = [
        'id',
        'subject',
        'body',
        'attachments',
    ];

    public function casts(): array
    {
        return [
            'attachments' => 'array',
        ];
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(100)->height(100);
    }

    public function users(): MorphToMany
    {
        return $this
            ->morphToMany(
                config('auth.providers.users.model'),
                'fb_message_user',
            )
            ->withPivot(['folder', 'read_at', 'trashed_at', 'type'])
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function availableRecipients(): MorphToMany
    {
        return $this
            ->users()
            ->when(
                Auth::check(),
                fn (Builder $query) => $query->where('id', '<>', Auth::id())
            )
            ->where(fn (Builder $query) => $query->messageTo());
    }

    public function inbox(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('folder', MessageFolder::INBOX)
            ->wherePivot('trashed_at', null);
    }

    public function unread(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('folder', MessageFolder::INBOX)
            ->wherePivot('trashed_at', null)
            ->wherePivot('read_at', null);
    }

    public function sent(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('folder', MessageFolder::SENT)
            ->wherePivot('trashed_at', null);
    }

    public function archived(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('folder', MessageFolder::ARCHIVED)
            ->wherePivot('trashed_at', null);
    }

    public function trashed(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('trashed_at', '<>', null);
    }

    public function from(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('type', MessageType::FROM);
    }

    public function to(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('type', MessageType::TO);
    }

    public function cc(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('type', MessageType::CC);
    }

    public function bcc(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('type', MessageType::BCC);
    }
}
