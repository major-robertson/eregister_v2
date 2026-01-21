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

    public int $totalSteps = 3;

    // Step 1: Entity info
    public string $entityType = '';

    public string $stateOfIncorporation = '';

    // Step 2: Business contact
    public string $phone = '';

    public string $contractorLicenseNumber = '';

    // Step 3: Signer info (for responsible_people)
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
        $this->entityType = $business->entity_type ?? '';
        $this->stateOfIncorporation = $business->state_of_incorporation ?? '';
        $this->phone = $business->phone ?? '';
        $this->contractorLicenseNumber = $business->contractor_license_number ?? '';

        // Pre-fill signer info from responsible_people or user
        $user = Auth::user();
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
            $rules = [
                'entityType' => ['required', 'string', 'max:50'],
            ];

            // Only require state of incorporation for entity types that need it
            if ($this->requiresStateOfIncorporation()) {
                $rules['stateOfIncorporation'] = ['required', 'string', 'size:2'];
            }

            $this->validate($rules, [], [
                'entityType' => 'entity type',
                'stateOfIncorporation' => 'state of incorporation',
            ]);
        } elseif ($this->step === 2) {
            $this->validate([
                'phone' => ['required', 'string', 'max:20'],
                'contractorLicenseNumber' => ['nullable', 'string', 'max:50'],
            ], [], [
                'phone' => 'phone number',
                'contractorLicenseNumber' => 'contractor license number',
            ]);
        } elseif ($this->step === 3) {
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
        $updateData = [
            'entity_type' => $this->entityType,
            'phone' => $this->phone,
            'contractor_license_number' => $this->contractorLicenseNumber ?: null,
        ];

        // Only save state of incorporation for entity types that need it
        if ($this->requiresStateOfIncorporation()) {
            $updateData['state_of_incorporation'] = $this->stateOfIncorporation;
        } else {
            $updateData['state_of_incorporation'] = null;
        }

        $this->business->update($updateData);

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
            'entityTypes' => $this->getEntityTypes(),
        ])->layout('layouts.minimal', ['title' => 'Lien Setup']);
    }

    protected function getEntityTypes(): array
    {
        return [
            'sole_proprietorship' => 'Sole Proprietorship',
            'llc' => 'Limited Liability Company (LLC)',
            'corporation' => 'C Corporation',
            'partnership' => 'Partnership',
            's_corp' => 'S Corporation',
        ];
    }

    public function requiresStateOfIncorporation(): bool
    {
        return in_array($this->entityType, ['llc', 'corporation', 's_corp'], true);
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
