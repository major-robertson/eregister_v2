<?php

namespace App\Domains\ResaleCert\Livewire\Concerns;

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Models\ResaleProfile;
use Illuminate\Support\Facades\Auth;

/**
 * Shared mount-time plumbing for resale-cert pages: resolve the current
 * business (redirect to the switcher when none) and optionally require the
 * resale onboarding to be finished before the page is usable.
 */
trait ResolvesResaleContext
{
    public Business $business;

    protected function resolveBusiness(): bool
    {
        $business = Auth::user()?->currentBusiness();

        if (! $business) {
            $this->redirect(route('portal.select-business'));

            return false;
        }

        $this->business = $business;

        return true;
    }

    protected function requireCompleteProfile(): bool
    {
        if (! $this->business->resaleProfile?->isComplete()) {
            session()->flash('info', 'Finish setting up your resale profile first.');
            $this->redirect(route('resale-cert.onboarding'));

            return false;
        }

        return true;
    }

    protected function resaleProfile(): ?ResaleProfile
    {
        return $this->business->resaleProfile;
    }
}
