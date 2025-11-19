<?php

namespace App\Policies;

use App\Models\MaintenanceType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MaintenanceTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('maintenanceTypes.view');
    }

    public function view(User $user, MaintenanceType $maintenanceType): bool
    {
        return $user->hasPermissionTo('maintenanceTypes.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('maintenanceTypes.create');
    }

    public function update(User $user, MaintenanceType $maintenanceType): bool
    {
        return $user->hasPermissionTo('maintenanceTypes.edit');
    }

    public function delete(User $user, MaintenanceType $maintenanceType): bool
    {
        return $user->hasPermissionTo('maintenanceTypes.delete');
    }

}
