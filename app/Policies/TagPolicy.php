<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('tags.view');
    }

    public function view(User $user, Tag $tag): bool
    {
        return $user->hasPermissionTo('tags.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('tags.create');
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->hasPermissionTo('tags.edit');
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->hasPermissionTo('tags.delete');
    }

}
