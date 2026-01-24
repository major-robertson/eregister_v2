<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Business\Models\Business;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class LienOnboarding extends Component
{
    public Business $business;

    public int $step = 1;

    public int $totalSteps = 2;

    /**
     * Whether this is a continuous flow from business onboarding (from liens signup, first business).
     * Determines if we show 4 unified dots or 2 standalone dots.
     */
    public bool $isContinuousFlow = false;

    /**
     * Offset for unified progress display across business + lien onboarding.
     * 2 for continuous flow (steps 3-4 of 4), 0 for standalone (steps 1-2 of 2).
     */
    public int $stepOffset = 0;

    // Step 1: Business contact
    public string $phone = '';

    public string $contractorLicenseNumber = '';

    // Step 2: Signer info (for responsible_people)
    public string $signerFirstName = '';

    public string $signerLastName = '';

    public string $signerTitle = '';

    public function mount(): void
    {
        $business = Auth::user()->currentBusiness();

        if (! $business) {
            $this->redirect(route('portal.select-business'));

            return;
        }

        Gate::authorize('update', $business);

        $this->business = $business;

        // Pre-fill from existing business data
        $this->phone = $business->phone ?? '';
        $this->contractorLicenseNumber = $business->contractor_license_number ?? '';

        // Pre-fill signer info from responsible_people or user
        $user = Auth::user();

        // Determine if this is continuous flow (from liens signup, first business)
        $this->isContinuousFlow = $user->signup_landing_path === '/liens'
            && $user->businesses()->count() === 1;
        $this->stepOffset = $this->isContinuousFlow ? 2 : 0;
        $responsiblePerson = $business->getResponsiblePersonForUser($user->id);

        if ($responsiblePerson && ! empty($responsiblePerson['name'])) {
            // Try to split existing name into first/last
            $nameParts = explode(' ', $responsiblePerson['name'], 2);
            $this->signerFirstName = $nameParts[0] ?? '';
            $this->signerLastName = $nameParts[1] ?? '';
            $this->signerTitle = $responsiblePerson['title'] ?? '';
        } else {
            $this->signerFirstName = $user->first_name ?? '';
            $this->signerLastName = $user->last_name ?? '';
            $this->signerTitle = '';
        }
    }

    public function nextStep(): void
    {
        $this->validateStep();

        if ($this->step < $this->totalSteps) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    protected function validateStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'phone' => ['required', 'string', 'max:20'],
                'contractorLicenseNumber' => ['nullable', 'string', 'max:50'],
            ], [], [
                'phone' => 'phone number',
                'contractorLicenseNumber' => 'contractor license number',
            ]);
        } elseif ($this->step === 2) {
            $this->validate([
                'signerFirstName' => ['required', 'string', 'max:50'],
                'signerLastName' => ['required', 'string', 'max:50'],
                'signerTitle' => ['required', 'string', 'max:100'],
            ], [], [
                'signerFirstName' => 'first name',
                'signerLastName' => 'last name',
                'signerTitle' => 'title',
            ]);
        }
    }

    public function complete(): mixed
    {
        $this->validateStep();

        // Save all data
        $this->business->update([
            'phone' => $this->phone,
            'contractor_license_number' => $this->contractorLicenseNumber ?: null,
        ]);

        // Update responsible person for current user
        $fullName = trim($this->signerFirstName.' '.$this->signerLastName);
        $this->business->setResponsiblePersonForUser(
            Auth::id(),
            $fullName,
            $this->signerTitle,
            canSignLiens: true
        );

        $this->business->completeLienOnboarding();

        return $this->redirect(route('lien.projects.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.lien.lien-onboarding', [
            'states' => config('states'),
        ])->layout('layouts.minimal', ['title' => 'Lien Setup']);
    }

    /**
     * Format a digits-only phone number for display.
     */
    public function formatPhoneForDisplay(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) === 0) {
            return '';
        }

        if (strlen($digits) <= 3) {
            return '('.$digits;
        }

        if (strlen($digits) <= 6) {
            return '('.substr($digits, 0, 3).') '.substr($digits, 3);
        }

        return '('.substr($digits, 0, 3).') '.substr($digits, 3, 3).'-'.substr($digits, 6, 4);
    }
}
