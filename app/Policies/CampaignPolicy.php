<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampaignPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('campaigns.view');
    }

    public function view(User $user, Campaign $campaign): bool
    {
        return $user->hasPermissionTo('campaigns.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('campaigns.create');
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $user->hasPermissionTo('campaigns.edit');
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->hasPermissionTo('campaigns.delete');
    }

}
