<?php

declare(strict_types=1);

namespace Mortezamasumi\FbMessage\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Mortezamasumi\FbMessage\Models\FbMessage;

class FbMessagePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FbMessage');
    }

    public function view(AuthUser $authUser, FbMessage $fbMessage): bool
    {
        return $authUser->can('View:FbMessage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FbMessage');
    }

    public function delete(AuthUser $authUser, FbMessage $fbMessage): bool
    {
        return $authUser->can('Delete:FbMessage');
    }

    public function forwatd(AuthUser $authUser): bool
    {
        return $authUser->can('Forward:FbMessage');
    }

    public function reply(AuthUser $authUser): bool
    {
        return $authUser->can('Reply:FbMessage');
    }

    public function archive(AuthUser $authUser): bool
    {
        return $authUser->can('Archive:FbMessage');
    }

    public function trash(AuthUser $authUser): bool
    {
        return $authUser->can('Trash:FbMessage');
    }
}
