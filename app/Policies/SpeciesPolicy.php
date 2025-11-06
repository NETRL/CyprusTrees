<?php

namespace App\Policies;

use App\Models\Species;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SpeciesPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('species.view');
    }

    public function view(User $user, Species $species): bool
    {
        return $user->hasPermissionTo('species.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('species.create');
    }

    public function update(User $user, Species $species): bool
    {
        return $user->hasPermissionTo('species.edit');
    }

    public function delete(User $user, Species $species): bool
    {
        return $user->hasPermissionTo('species.delete');
    }

}
