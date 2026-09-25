<?php

namespace App\Domains\Lien\Policies;

use App\Domains\Lien\Models\LienProject;
use App\Models\User;

class LienProjectPolicy
{
    /**
     * Determine if the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        return $user->currentBusiness() !== null;
    }

    /**
     * Determine if the user can view the project.
     */
    public function view(User $user, LienProject $project): bool
    {
        return $this->belongsToBusiness($user, $project);
    }

    /**
     * Determine if the user can create projects.
     */
    public function create(User $user): bool
    {
        return $user->currentBusiness() !== null;
    }

    /**
     * Determine if the user can update the project.
     */
    public function update(User $user, LienProject $project): bool
    {
        return $this->belongsToBusiness($user, $project);
    }

    /**
     * Determine if the user can delete the project. Never while it has a paid
     * filing: deleting the project deletes its filings too.
     */
    public function delete(User $user, LienProject $project): bool
    {
        return $this->belongsToBusiness($user, $project) && ! $project->hasPaidFilings();
    }

    /**
     * Check if the project belongs to the user's current business.
     */
    private function belongsToBusiness(User $user, LienProject $project): bool
    {
        $business = $user->currentBusiness();

        return $business && $project->business_id === $business->id;
    }
}
