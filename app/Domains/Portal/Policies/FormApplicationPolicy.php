<?php

namespace App\Domains\Portal\Policies;

use App\Domains\Forms\Models\FormApplication;
use App\Models\User;

class FormApplicationPolicy
{
    /**
     * Determine if the user can view the application
     */
    public function view(User $user, FormApplication $application): bool
    {
        return $user->belongsToBusiness($application->business);
    }

    /**
     * Determine if the user can update the application
     */
    public function update(User $user, FormApplication $application): bool
    {
        if ($application->isLocked()) {
            return false;
        }

        return $user->belongsToBusiness($application->business);
    }

    /**
     * Determine if the user can delete the application
     */
    public function delete(User $user, FormApplication $application): bool
    {
        if ($application->isLocked()) {
            return false;
        }

        $role = $user->businesses()->find($application->business_id)?->pivot->role;

        return in_array($role, ['owner', 'admin']);
    }
}
