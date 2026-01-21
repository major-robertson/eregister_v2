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

    // Address (JSON structure for Google Maps compatibility)
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

        // Ensure legal_name is set from the business name
        if (! $business->legal_name && $business->name) {
            $business->update(['legal_name' => $business->name]);
        }

        // Load existing address from JSON or initialize empty
        $existingAddress = $business->business_address ?? [];
        $this->businessAddress = [
            'line1' => $existingAddress['line1'] ?? '',
            'line2' => $existingAddress['line2'] ?? '',
            'city' => $existingAddress['city'] ?? '',
            'state' => $existingAddress['state'] ?? '',
            'zip' => $existingAddress['zip'] ?? '',
        ];
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

        // Redirect to liens portal if user signed up from the liens landing page
        $redirectRoute = Auth::user()->signup_landing_path === '/liens'
            ? route('lien.projects.index')
            : route('dashboard');

        return $this->redirect($redirectRoute, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.business.onboarding-wizard', [
            'states' => config('states'),
        ])->layout('layouts.minimal', ['title' => 'Business Setup']);
    }
}
