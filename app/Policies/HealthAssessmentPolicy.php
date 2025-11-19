<?php

namespace App\Policies;

use App\Models\HealthAssessment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HealthAssessmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('healthAssessments.view');
    }

    public function view(User $user, HealthAssessment $healthAssessment): bool
    {
        return $user->hasPermissionTo('healthAssessments.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('healthAssessments.create');
    }

    public function update(User $user, HealthAssessment $healthAssessment): bool
    {
        return $user->hasPermissionTo('healthAssessments.edit');
    }

    public function delete(User $user, HealthAssessment $healthAssessment): bool
    {
        return $user->hasPermissionTo('healthAssessments.delete');
    }

}
