<?php

use App\Domains\Business\Models\Business;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public ?Business $business = null;

    // Entity fields
    public string $legal_name = '';
    public string $entity_type = '';
    public string $state_of_incorporation = '';
    public string $phone = '';
    public string $contractor_license_number = '';

    // Signer info
    public string $signer_first_name = '';
    public string $signer_last_name = '';
    public string $signer_title = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->business = Auth::user()->currentBusiness();

        if (! $this->business) {
            return;
        }

        $this->legal_name = $this->business->legal_name ?? '';
        $this->entity_type = $this->business->entity_type ?? '';
        $this->state_of_incorporation = $this->business->state_of_incorporation ?? '';
        $this->phone = $this->business->phone ?? '';
        $this->contractor_license_number = $this->business->contractor_license_number ?? '';

        // Load signer info from responsible_people
        $user = Auth::user();
        $responsiblePerson = $this->business->getResponsiblePersonForUser($user->id);

        if ($responsiblePerson && !empty($responsiblePerson['name'])) {
            // Try to split existing name into first/last
            $nameParts = explode(' ', $responsiblePerson['name'], 2);
            $this->signer_first_name = $nameParts[0] ?? '';
            $this->signer_last_name = $nameParts[1] ?? '';
            $this->signer_title = $responsiblePerson['title'] ?? '';
        } else {
            $this->signer_first_name = $user->first_name ?? '';
            $this->signer_last_name = $user->last_name ?? '';
            $this->signer_title = '';
        }
    }

    /**
     * Update the business information.
     */
    public function updateBusinessInfo(): void
    {
        $rules = [
            'legal_name' => ['required', 'string', 'max:255'],
            'entity_type' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:20'],
            'contractor_license_number' => ['nullable', 'string', 'max:50'],
            'signer_first_name' => ['required', 'string', 'max:50'],
            'signer_last_name' => ['required', 'string', 'max:50'],
            'signer_title' => ['required', 'string', 'max:100'],
        ];

        if ($this->requiresStateOfIncorporation()) {
            $rules['state_of_incorporation'] = ['required', 'string', 'size:2'];
        }

        $this->validate($rules);

        $updateData = [
            'legal_name' => $this->legal_name,
            'entity_type' => $this->entity_type,
            'phone' => $this->phone,
            'contractor_license_number' => $this->contractor_license_number ?: null,
        ];

        if ($this->requiresStateOfIncorporation()) {
            $updateData['state_of_incorporation'] = $this->state_of_incorporation;
        } else {
            $updateData['state_of_incorporation'] = null;
        }

        $this->business->update($updateData);

        // Update signer info
        $fullName = trim($this->signer_first_name . ' ' . $this->signer_last_name);
        $this->business->setResponsiblePersonForUser(
            Auth::id(),
            $fullName,
            $this->signer_title,
            canSignLiens: true
        );

        $this->dispatch('business-updated');
    }

    public function getEntityTypes(): array
    {
        return [
            'sole_proprietorship' => 'Sole Proprietorship',
            'llc' => 'Limited Liability Company (LLC)',
            'corporation' => 'C Corporation',
            'partnership' => 'Partnership',
            's_corp' => 'S Corporation',
        ];
    }

    public function getStates(): array
    {
        return config('states');
    }

    public function requiresStateOfIncorporation(): bool
    {
        return in_array($this->entity_type, ['llc', 'corporation', 's_corp'], true);
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
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Business Settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Business')" :subheading="__('Manage your business and lien signer information')">
        @if($business)
            <form wire:submit="updateBusinessInfo" class="my-6 w-full space-y-6">
                <flux:heading size="sm">{{ __('Business Details') }}</flux:heading>

                <flux:input wire:model="legal_name" :label="__('Legal Business Name')" type="text" required />

                <flux:select wire:model.live="entity_type" :label="__('Entity Type')" required>
                    <option value="">Select entity type...</option>
                    @foreach($this->getEntityTypes() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>

                @if($this->requiresStateOfIncorporation())
                    <flux:select wire:model="state_of_incorporation" :label="__('State of Incorporation')" required>
                        <option value="">Select state...</option>
                        @foreach($this->getStates() as $code => $name)
                            <option value="{{ $code }}">{{ $code }} - {{ $name }}</option>
                        @endforeach
                    </flux:select>
                @endif

                <div
                    x-data="{
                        phone: @js($this->formatPhoneForDisplay($phone)),
                        formatPhone() {
                            let digits = this.phone.replace(/\D/g, '').substring(0, 10);
                            if (digits.length === 0) {
                                this.phone = '';
                            } else if (digits.length <= 3) {
                                this.phone = '(' + digits;
                            } else if (digits.length <= 6) {
                                this.phone = '(' + digits.substring(0, 3) + ') ' + digits.substring(3);
                            } else {
                                this.phone = '(' + digits.substring(0, 3) + ') ' + digits.substring(3, 6) + '-' + digits.substring(6);
                            }
                            $wire.set('phone', digits);
                        }
                    }"
                >
                    <flux:field>
                        <flux:label>{{ __('Business Phone') }}</flux:label>
                        <flux:input type="tel" x-model="phone" x-on:input="formatPhone()" required />
                    </flux:field>
                </div>

                <flux:input wire:model="contractor_license_number" :label="__('Contractor License Number')" type="text" :description="__('Optional - required for liens in some states')" />

                <flux:separator />

                <flux:heading size="sm">{{ __('Person Signing Lien Documents') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 mb-4">
                    {{ __('This person will be listed as the authorized signer on all lien filings.') }}
                </flux:text>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="signer_first_name" :label="__('First Name')" type="text" required />
                    <flux:input wire:model="signer_last_name" :label="__('Last Name')" type="text" required />
                </div>

                <flux:input wire:model="signer_title" :label="__('Title')" type="text" required placeholder="President, Owner, Manager, etc." />

                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-end">
                        <flux:button variant="primary" type="submit" class="w-full">
                            {{ __('Save') }}
                        </flux:button>
                    </div>

                    <x-action-message class="me-3" on="business-updated">
                        {{ __('Saved.') }}
                    </x-action-message>
                </div>
            </form>
        @else
            <div class="my-6">
                <flux:callout color="amber" icon="exclamation-triangle">
                    {{ __('No business selected. Please select a business to manage settings.') }}
                </flux:callout>
            </div>
        @endif
    </x-pages::settings.layout>
</section>
