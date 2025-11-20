<?php

namespace App\Policies;

use App\Models\CitizenReport;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CitizenReportPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('citizenReports.view');
    }

    public function view(User $user, CitizenReport $citizenReport): bool
    {
        return $user->hasPermissionTo('citizenReports.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('citizenReports.create');
    }

    public function update(User $user, CitizenReport $citizenReport): bool
    {
        return $user->hasPermissionTo('citizenReports.edit');
    }

    public function delete(User $user, CitizenReport $citizenReport): bool
    {
        return $user->hasPermissionTo('citizenReports.delete');
    }

}
