<?php

namespace Mortezamasumi\FbMessage\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class UserMessagesScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereRelation('users', 'id', Auth::id() ?? null);
    }
}
