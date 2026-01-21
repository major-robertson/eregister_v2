<?php

namespace App\Domains\Business\Livewire;

use App\Domains\Business\Models\Business;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BusinessSwitcher extends Component
{
    public ?int $selectedBusinessId = null;

    /** @var \Illuminate\Database\Eloquent\Collection<int, Business> */
    public $businesses;

    public string $newBusinessName = '';

    public function mount(): void
    {
        $this->loadBusinesses();
        $this->selectedBusinessId = session('current_business_id');
    }

    public function loadBusinesses(): void
    {
        $this->businesses = Auth::user()->businesses()->orderBy('name')->get();
    }

    public function selectBusiness(int $businessId): mixed
    {
        $business = Auth::user()->businesses()->find($businessId);

        if (! $business) {
            $this->addError('business', 'Business not found.');

            return null;
        }

        session(['current_business_id' => $business->id]);
        $this->selectedBusinessId = $business->id;

        if ($business->isOnboardingComplete()) {
            return $this->redirect(route('dashboard'), navigate: true);
        }

        return $this->redirect(route('portal.onboarding'), navigate: true);
    }

    public function createBusiness(): mixed
    {
        $this->validate([
            'newBusinessName' => ['required', 'string', 'min:2', 'max:120'],
        ]);

        $business = Business::create([
            'name' => $this->newBusinessName,
        ]);

        Auth::user()->businesses()->attach($business->id, ['role' => 'owner']);

        session(['current_business_id' => $business->id]);

        return $this->redirect(route('portal.onboarding'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.business.business-switcher')
            ->layout('layouts.minimal', ['title' => 'Get Started']);
    }
}
