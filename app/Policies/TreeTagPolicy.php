<?php

namespace App\Policies;

use App\Models\TreeTag;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TreeTagPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('treeTags.view');
    }

    public function view(User $user, TreeTag $treeTag): bool
    {
        return $user->hasPermissionTo('treeTags.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('treeTags.create');
    }

    public function update(User $user, TreeTag $treeTag): bool
    {
        return $user->hasPermissionTo('treeTags.edit');
    }

    public function delete(User $user, TreeTag $treeTag): bool
    {
        return $user->hasPermissionTo('treeTags.delete');
    }

}
