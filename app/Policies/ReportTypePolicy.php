<?php

namespace App\Policies;

use App\Models\ReportType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('reportTypes.view');
    }

    public function view(User $user, ReportType $reportType): bool
    {
        return $user->hasPermissionTo('reportTypes.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('reportTypes.create');
    }

    public function update(User $user, ReportType $reportType): bool
    {
        return $user->hasPermissionTo('reportTypes.edit');
    }

    public function delete(User $user, ReportType $reportType): bool
    {
        return $user->hasPermissionTo('reportTypes.delete');
    }

}
