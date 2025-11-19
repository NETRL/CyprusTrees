<?php

namespace App\Policies;

use App\Models\MaintenanceEvent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MaintenanceEventPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('maintenanceEvents.view');
    }

    public function view(User $user, MaintenanceEvent $maintenanceEvent): bool
    {
        return $user->hasPermissionTo('maintenanceEvents.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('maintenanceEvents.create');
    }

    public function update(User $user, MaintenanceEvent $maintenanceEvent): bool
    {
        return $user->hasPermissionTo('maintenanceEvents.edit');
    }

    public function delete(User $user, MaintenanceEvent $maintenanceEvent): bool
    {
        return $user->hasPermissionTo('maintenanceEvents.delete');
    }

}
