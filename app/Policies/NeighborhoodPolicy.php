<?php

namespace App\Policies;

use App\Models\Neighborhood;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NeighborhoodPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('neighborhoods.view');
    }

    public function view(User $user, Neighborhood $neighborhood): bool
    {
        return $user->hasPermissionTo('neighborhoods.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('neighborhoods.create');
    }

    public function update(User $user, Neighborhood $neighborhood): bool
    {
        return $user->hasPermissionTo('neighborhoods.edit');
    }

    public function delete(User $user, Neighborhood $neighborhood): bool
    {
        return $user->hasPermissionTo('neighborhoods.delete');
    }

}
