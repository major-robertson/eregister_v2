<?php

namespace App\Domains\Business\Livewire;

use App\Domains\Business\Models\Business;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class OnboardingWizard extends Component
{
    public Business $business;

    public int $step = 1;

    // Step 1: Business name
    public string $legalName = '';

    // Step 2: Address (JSON structure for Google Maps compatibility)
    public array $businessAddress = [
        'line1' => '',
        'line2' => '',
        'city' => '',
        'state' => '',
        'zip' => '',
    ];

    public function mount(): void
    {
        $business = Auth::user()->currentBusiness();

        if (! $business) {
            $this->redirect(route('portal.select-business'));

            return;
        }

        Gate::authorize('update', $business);

        $this->business = $business;

        // Load existing data
        $this->legalName = $business->legal_name ?? $business->name ?? '';

        // Load existing address from JSON or initialize empty
        $existingAddress = $business->business_address ?? [];
        $this->businessAddress = [
            'line1' => $existingAddress['line1'] ?? '',
            'line2' => $existingAddress['line2'] ?? '',
            'city' => $existingAddress['city'] ?? '',
            'state' => $existingAddress['state'] ?? '',
            'zip' => $existingAddress['zip'] ?? '',
        ];

        // If legal name is already set, go to step 2
        if ($this->legalName) {
            $this->step = 2;
        }
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'legalName' => ['required', 'string', 'min:2', 'max:120'],
            ]);

            $this->business->update([
                'name' => $this->legalName,
                'legal_name' => $this->legalName,
            ]);

            $this->step = 2;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function complete(): mixed
    {
        $this->validate([
            'businessAddress.line1' => ['required', 'string', 'max:100'],
            'businessAddress.line2' => ['nullable', 'string', 'max:100'],
            'businessAddress.city' => ['required', 'string', 'max:50'],
            'businessAddress.state' => ['required', 'string', 'size:2'],
            'businessAddress.zip' => ['required', 'string', 'max:10'],
        ], [], [
            'businessAddress.line1' => 'street address',
            'businessAddress.city' => 'city',
            'businessAddress.state' => 'state',
            'businessAddress.zip' => 'ZIP code',
        ]);

        // Filter out empty line2 to keep JSON clean
        $addressData = array_filter($this->businessAddress, fn ($value, $key) => $key !== 'line2' || $value !== '', ARRAY_FILTER_USE_BOTH);

        $this->business->update([
            'business_address' => $addressData,
        ]);

        $this->business->completeOnboarding();

        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.business.onboarding-wizard', [
            'states' => config('states'),
        ])->layout('layouts.app', ['title' => 'Business Setup']);
    }
}
