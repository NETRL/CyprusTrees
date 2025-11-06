<?php

namespace App\Policies;

use App\Models\Tree;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TreePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('trees.view');
    }

    public function view(User $user, Tree $tree): bool
    {
        return $user->hasPermissionTo('trees.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('trees.create');
    }

    public function update(User $user, Tree $tree): bool
    {
        return $user->hasPermissionTo('trees.edit');
    }

    public function delete(User $user, Tree $tree): bool
    {
        return $user->hasPermissionTo('trees.delete');
    }

}
