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
     * Determine if the user can start checkout for the application.
     * The application must be unlocked (not already paid/submitted), owned
     * by the user's business, and have every selected state completed.
     */
    public function checkout(User $user, FormApplication $application): bool
    {
        if ($application->isLocked()) {
            return false;
        }

        if (! $user->belongsToBusiness($application->business)) {
            return false;
        }

        return $application->allStatesComplete();
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
