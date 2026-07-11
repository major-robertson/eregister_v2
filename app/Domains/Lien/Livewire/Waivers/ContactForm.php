<?php

namespace App\Domains\Lien\Livewire\Waivers;

use App\Domains\Lien\Models\LienContact;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Create/edit a waiver contact. Bound to the create route (no parameter) or the
 * edit route ({contact}); tenant isolation comes from the model's global
 * business scope during route binding. Field set + validation mirror the
 * wizard's inline add-contact modal so the two entry points stay consistent.
 */
class ContactForm extends Component
{
    public ?LienContact $contact = null;

    public string $company_name = '';

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $phone = '';

    public string $address_line1 = '';

    public string $address_line2 = '';

    public string $city = '';

    public string $state = '';

    public string $postal_code = '';

    public function mount(?LienContact $contact = null): void
    {
        if ($contact?->exists) {
            $this->contact = $contact;
            $this->company_name = $contact->company_name ?? '';
            $this->first_name = $contact->first_name ?? '';
            $this->last_name = $contact->last_name ?? '';
            $this->email = $contact->email ?? '';
            $this->phone = $contact->phone ?? '';
            $this->address_line1 = $contact->address_line1 ?? '';
            $this->address_line2 = $contact->address_line2 ?? '';
            $this->city = $contact->city ?? '';
            $this->state = $contact->state ?? '';
            $this->postal_code = $contact->postal_code ?? '';
        }
    }

    public function save(): void
    {
        // A contact needs a company OR a person's name — not both. No field is
        // individually required; the error surfaces on the company field.
        $validated = $this->validate([
            'company_name' => ['nullable', 'required_without_all:first_name,last_name', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'size:2'],
            'postal_code' => ['nullable', 'string', 'max:10'],
        ], [
            'company_name.required_without_all' => 'Enter a company name or a first/last name.',
        ]);

        // The form binds blank strings; store real nulls so a company-less or
        // name-less contact reads cleanly.
        $validated = array_map(fn ($value) => $value === '' ? null : $value, $validated);
        $validated['state'] = $validated['state'] ? strtoupper($validated['state']) : null;

        if ($this->contact) {
            $this->contact->update($validated);
            session()->flash('success', 'Contact updated.');
            $this->redirect(route('lien.waivers.contacts.index'), navigate: true);

            return;
        }

        // business_id auto-fills from the BelongsToBusiness creating hook.
        LienContact::create([
            ...$validated,
            'created_by_user_id' => Auth::id(),
        ]);

        session()->flash('success', 'Contact added.');
        $this->redirect(route('lien.waivers.contacts.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.lien.waivers.contact-form')
            ->layout('components.layouts.portal', [
                'title' => $this->contact ? 'Edit Contact' : 'Add Contact',
            ]);
    }
}
