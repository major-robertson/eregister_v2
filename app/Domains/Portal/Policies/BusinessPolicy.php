<?php

namespace App\Domains\Portal\Policies;

use App\Domains\Business\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    /**
     * Determine if the user can view the business
     */
    public function view(User $user, Business $business): bool
    {
        return $user->belongsToBusiness($business);
    }

    /**
     * Determine if the user can update the business
     */
    public function update(User $user, Business $business): bool
    {
        $role = $user->businesses()->find($business->id)?->pivot->role;

        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can manage billing for the business
     */
    public function billing(User $user, Business $business): bool
    {
        return $user->businesses()->find($business->id)?->pivot->role === 'owner';
    }

    /**
     * Determine if the user can delete the business
     */
    public function delete(User $user, Business $business): bool
    {
        return $user->businesses()->find($business->id)?->pivot->role === 'owner';
    }
}
