<?php

namespace App\Domains\ResaleCert\Livewire;

use App\Domains\ResaleCert\Livewire\Concerns\ResolvesResaleContext;
use App\Domains\ResaleCert\Models\ResaleVendor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Create/edit a vendor. Bound to the create route (no parameter) or the
 * edit route ({vendor}); tenant isolation comes from the model's global
 * business scope during route binding.
 */
class VendorForm extends Component
{
    use ResolvesResaleContext;

    public ?ResaleVendor $vendor = null;

    public string $legal_name = '';

    public string $address_line1 = '';

    public string $address_line2 = '';

    public string $city = '';

    public string $state = '';

    public string $postal_code = '';

    public string $country = 'US';

    public string $contact_name = '';

    public string $contact_email = '';

    public string $contact_phone = '';

    public function mount(?ResaleVendor $vendor = null): void
    {
        if (! $this->resolveBusiness() || ! $this->requireCompleteProfile()) {
            return;
        }

        if ($vendor?->exists) {
            $this->vendor = $vendor;
            $this->legal_name = $vendor->legal_name;
            $this->address_line1 = $vendor->address_line1;
            $this->address_line2 = $vendor->address_line2 ?? '';
            $this->city = $vendor->city;
            $this->state = $vendor->state;
            $this->postal_code = $vendor->postal_code;
            $this->country = $vendor->country;
            $this->contact_name = $vendor->contact_name ?? '';
            $this->contact_email = $vendor->contact_email ?? '';
            $this->contact_phone = $vendor->contact_phone ?? '';
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'legal_name' => ['required', 'string', 'max:255'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'postal_code' => ['required', 'string', 'regex:/^\d{5}(-\d{4})?$/'],
            'country' => ['required', 'string', 'size:2'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'regex:/^(\(\d{3}\) \d{3}-\d{4}|\d{10})$/'],
        ], [
            'postal_code.regex' => 'Enter a 5-digit ZIP code (or ZIP+4, e.g. 12345-6789).',
            'contact_phone.regex' => 'Enter a 10-digit phone number, e.g. (555) 123-4567.',
        ]);

        if ($this->vendor) {
            $this->vendor->update($validated);
            session()->flash('success', 'Vendor updated.');
            $this->redirect(route('resale-cert.vendors.show', $this->vendor), navigate: true);

            return;
        }

        $vendor = ResaleVendor::create([
            ...$validated,
            'business_id' => $this->business->id,
            'created_by_user_id' => Auth::id(),
        ]);

        session()->flash('success', 'Vendor created. You can now generate certificates for it.');
        $this->redirect(route('resale-cert.vendors.show', $vendor), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.resale-cert.vendor-form')
            ->layout('components.layouts.portal', [
                'title' => $this->vendor ? 'Edit Vendor' : 'Add Vendor',
            ]);
    }
}
