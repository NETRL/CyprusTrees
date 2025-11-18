<?php

namespace App\Policies;

use App\Models\PlantingEvent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlantingEventPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('plantingEvents.view');
    }

    public function view(User $user, PlantingEvent $plantingEvent): bool
    {
        return $user->hasPermissionTo('plantingEvents.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('plantingEvents.create');
    }

    public function update(User $user, PlantingEvent $plantingEvent): bool
    {
        return $user->hasPermissionTo('plantingEvents.edit');
    }

    public function delete(User $user, PlantingEvent $plantingEvent): bool
    {
        return $user->hasPermissionTo('plantingEvents.delete');
    }

}
