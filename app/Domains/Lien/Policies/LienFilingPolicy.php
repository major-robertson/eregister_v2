<?php

namespace App\Domains\Lien\Policies;

use App\Domains\Lien\Models\LienFiling;
use App\Models\User;

class LienFilingPolicy
{
    /**
     * Determine if the user can view any filings.
     */
    public function viewAny(User $user): bool
    {
        // Admin users can view all filings
        if ($this->hasAdminPermission($user, 'lien.view')) {
            return true;
        }

        return $user->currentBusiness() !== null;
    }

    /**
     * Determine if the user can view the filing.
     */
    public function view(User $user, LienFiling $filing): bool
    {
        // Admin users can view any filing
        if ($this->hasAdminPermission($user, 'lien.view')) {
            return true;
        }

        return $this->belongsToBusiness($user, $filing);
    }

    /**
     * Determine if the user can create filings.
     */
    public function create(User $user): bool
    {
        return $user->currentBusiness() !== null;
    }

    /**
     * Determine if the user can update the filing.
     */
    public function update(User $user, LienFiling $filing): bool
    {
        // Admin users can update any filing
        if ($this->hasAdminPermission($user, 'lien.update')) {
            return true;
        }

        // Regular users can only update if belongs to business and not yet paid
        return $this->belongsToBusiness($user, $filing) && ! $filing->isPaid();
    }

    /**
     * Determine if the user can delete the filing.
     */
    public function delete(User $user, LienFiling $filing): bool
    {
        // Can only delete drafts
        return $this->belongsToBusiness($user, $filing)
            && $filing->status->value === 'draft';
    }

    /**
     * Determine if the user can download the filing.
     */
    public function download(User $user, LienFiling $filing): bool
    {
        return $this->belongsToBusiness($user, $filing) && $filing->isPaid();
    }

    /**
     * Determine if the user can checkout the filing.
     */
    public function checkout(User $user, LienFiling $filing): bool
    {
        return $this->belongsToBusiness($user, $filing)
            && $filing->status->value === 'awaiting_payment';
    }

    /**
     * Determine if the user can change the filing status (admin only).
     */
    public function changeStatus(User $user, LienFiling $filing): bool
    {
        return $this->hasAdminPermission($user, 'lien.change_status');
    }

    /**
     * Check if the filing belongs to the user's current business.
     */
    private function belongsToBusiness(User $user, LienFiling $filing): bool
    {
        $business = $user->currentBusiness();

        return $business && $filing->business_id === $business->id;
    }

    /**
     * Check if the user has an admin permission.
     * Note: Admin role bypasses via Gate::before, this is for explicit permission checks.
     */
    private function hasAdminPermission(User $user, string $permission): bool
    {
        return $user->hasPermissionTo($permission);
    }
}
