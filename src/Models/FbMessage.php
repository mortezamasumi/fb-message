<?php

namespace Mortezamasumi\FbMessage\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Mortezamasumi\FbMessage\Enums\MessageFolder;
use Mortezamasumi\FbMessage\Enums\MessageType;
use Mortezamasumi\FbMessage\Models\Scopes\UserMessagesScope;
use Mortezamasumi\FbMessage\Observers\MessageObserver;

#[ObservedBy(MessageObserver::class)]
#[ScopedBy(UserMessagesScope::class)]
class FbMessage extends Model
{
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

    /**
     * @return MorphToMany<Model, $this>
     */
    public function users(): MorphToMany
    {
        /** @var class-string<Model> $userModel */
        $userModel = config('auth.providers.users.model');

        return $this
            ->morphToMany($userModel, 'fb_message_user')
            ->withPivot(['folder', 'read_at', 'trashed_at', 'type'])
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    /**
     * @return MorphToMany<Model, $this>
     */
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

    /**
     * @return MorphToMany<Model, $this>
     */
    public function inbox(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('folder', MessageFolder::INBOX)
            ->wherePivot('trashed_at', null);
    }

    /**
     * @return MorphToMany<Model, $this>
     */
    public function unread(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('folder', MessageFolder::INBOX)
            ->wherePivot('trashed_at', null)
            ->wherePivot('read_at', null);
    }

    /**
     * @return MorphToMany<Model, $this>
     */
    public function sent(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('folder', MessageFolder::SENT)
            ->wherePivot('trashed_at', null);
    }

    /**
     * @return MorphToMany<Model, $this>
     */
    public function archived(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('folder', MessageFolder::ARCHIVED)
            ->wherePivot('trashed_at', null);
    }

    /**
     * @return MorphToMany<Model, $this>
     */
    public function trashed(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('trashed_at', '<>', null);
    }

    /**
     * @return MorphToMany<Model, $this>
     */
    public function from(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('type', MessageType::FROM);
    }

    /**
     * @return MorphToMany<Model, $this>
     */
    public function to(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('type', MessageType::TO);
    }

    /**
     * @return MorphToMany<Model, $this>
     */
    public function cc(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('type', MessageType::CC);
    }

    /**
     * @return MorphToMany<Model, $this>
     */
    public function bcc(): MorphToMany
    {
        return $this
            ->users()
            ->wherePivot('type', MessageType::BCC);
    }
}
