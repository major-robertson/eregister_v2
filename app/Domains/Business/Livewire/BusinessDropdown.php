<?php

namespace App\Domains\Business\Livewire;

use App\Domains\Business\Models\Business;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BusinessDropdown extends Component
{
    public ?Business $currentBusiness = null;

    /** @var Collection<int, Business> */
    public Collection $businesses;

    public function mount(): void
    {
        $this->loadBusinesses();
    }

    public function loadBusinesses(): void
    {
        $user = Auth::user();
        $this->businesses = $user->businesses()->orderBy('name')->get();
        $this->currentBusiness = $user->currentBusiness();
    }

    public function switchBusiness(int $businessId): mixed
    {
        $business = Auth::user()->businesses()->find($businessId);

        if (! $business) {
            $this->addError('business', 'Business not found.');

            return null;
        }

        session(['current_business_id' => $business->id]);

        if ($business->isOnboardingComplete()) {
            return $this->redirect(route('dashboard'), navigate: true);
        }

        return $this->redirect(route('portal.onboarding'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.business.business-dropdown');
    }
}
