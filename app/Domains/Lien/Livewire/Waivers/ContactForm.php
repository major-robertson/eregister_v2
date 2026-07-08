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

    public string $contact_name = '';

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
            $this->company_name = $contact->company_name;
            $this->contact_name = $contact->contact_name ?? '';
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
        $validated = $this->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'size:2'],
            'postal_code' => ['nullable', 'string', 'max:10'],
        ]);

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
