<?php

namespace Mortezamasumi\FbMessage\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Mortezamasumi\FbMessage\Models\FbMessage;

class FbMessagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny($user): bool
    {
        return $user->can('view_any_fb::message');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view($user, FbMessage $fbMessage): bool
    {
        return $user->can('view_fb::message');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create($user): bool
    {
        return $user->can('create_fb::message');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update($user, FbMessage $fbMessage): bool
    {
        return $user->can('{{ Update }}');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete($user, FbMessage $fbMessage): bool
    {
        return $user->can('delete_fb::message');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny($user): bool
    {
        return $user->can('{{ DeleteAny }}');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete($user, FbMessage $fbMessage): bool
    {
        return $user->can('{{ ForceDelete }}');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny($user): bool
    {
        return $user->can('{{ ForceDeleteAny }}');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore($user, FbMessage $fbMessage): bool
    {
        return $user->can('{{ Restore }}');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny($user): bool
    {
        return $user->can('{{ RestoreAny }}');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate($user, FbMessage $fbMessage): bool
    {
        return $user->can('{{ Replicate }}');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder($user): bool
    {
        return $user->can('{{ Reorder }}');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function forward($user): bool
    {
        return $user->can('{{ Forward }}');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reply($user): bool
    {
        return $user->can('{{ Reply }}');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function archive($user): bool
    {
        return $user->can('{{ Archive }}');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function trash($user): bool
    {
        return $user->can('{{ Trash }}');
    }
}
