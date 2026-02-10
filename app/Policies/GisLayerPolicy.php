<?php

namespace App\Policies;

use App\Models\GisLayer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GisLayerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('gisLayers.view');
    }

    public function view(User $user, GisLayer $gisLayer): bool
    {
        return $user->hasPermissionTo('gisLayers.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('gisLayers.create');
    }

    public function update(User $user, GisLayer $gisLayer): bool
    {
        return $user->hasPermissionTo('gisLayers.edit');
    }

    public function delete(User $user, GisLayer $gisLayer): bool
    {
        return $user->hasPermissionTo('gisLayers.delete');
    }

}
