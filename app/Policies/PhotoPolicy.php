<?php

namespace App\Policies;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PhotoPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('photos.view');
    }

    public function view(User $user, Photo $neighborhood): bool
    {
        return $user->hasPermissionTo('photos.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('photos.create');
    }

    public function update(User $user, Photo $neighborhood): bool
    {
        return $user->hasPermissionTo('photos.edit');
    }

    public function delete(User $user, Photo $neighborhood): bool
    {
        return $user->hasPermissionTo('photos.delete');
    }

}
