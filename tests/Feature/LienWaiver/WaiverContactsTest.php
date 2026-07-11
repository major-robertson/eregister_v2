<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Livewire\Waivers\ContactForm;
use App\Domains\Lien\Livewire\Waivers\ContactList;
use App\Domains\Lien\Models\LienContact;
use App\Domains\Lien\Models\LienWaiver;
use App\Models\User;
use Livewire\Livewire;

if (! function_exists('waiverContactsActingBusiness')) {
    function waiverContactsActingBusiness(): Business
    {
        $user = User::factory()->create();
        $business = Business::factory()->create([
            'onboarding_completed_at' => now(),
            'lien_onboarding_completed_at' => now(),
        ]);
        $business->users()->attach($user, ['role' => 'owner']);

        test()->actingAs($user);
        session(['current_business_id' => $business->id]);

        return $business;
    }
}

beforeEach(function () {
    $this->business = waiverContactsActingBusiness();
});

describe('contact directory', function () {
    it('lists the current business contacts and filters by search', function () {
        LienContact::factory()->forBusiness($this->business)->create(['company_name' => 'Acme General Contractors']);
        LienContact::factory()->forBusiness($this->business)->create(['company_name' => 'Zenith Concrete Supply']);

        Livewire::test(ContactList::class)
            ->assertSee('Acme General Contractors')
            ->assertSee('Zenith Concrete Supply')
            ->set('search', 'Zenith')
            ->assertSee('Zenith Concrete Supply')
            ->assertDontSee('Acme General Contractors');
    });

    it('creates a contact scoped to the current business', function () {
        Livewire::test(ContactForm::class)
            ->set('company_name', 'New Sub LLC')
            ->set('first_name', 'Pat')
            ->set('last_name', 'Jones')
            ->set('email', 'pat@newsub.test')
            ->set('state', 'tx')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.waivers.contacts.index'));

        $contact = LienContact::where('company_name', 'New Sub LLC')->firstOrFail();
        expect($contact->business_id)->toBe($this->business->id);
        expect($contact->created_by_user_id)->toBe(auth()->id());
        expect($contact->first_name)->toBe('Pat');
        expect($contact->last_name)->toBe('Jones');
        // State is normalized to uppercase.
        expect($contact->state)->toBe('TX');
    });

    it('requires a company name or a person name, not both', function () {
        // Nothing filled: errors on company.
        Livewire::test(ContactForm::class)
            ->call('save')
            ->assertHasErrors(['company_name' => 'required_without_all']);

        // A first name alone is enough — no company required.
        Livewire::test(ContactForm::class)
            ->set('first_name', 'Solo')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.waivers.contacts.index'));

        expect(LienContact::where('first_name', 'Solo')->value('company_name'))->toBeNull();
    });

    it('edits an existing contact', function () {
        $contact = LienContact::factory()->forBusiness($this->business)->create(['company_name' => 'Old Name']);

        Livewire::test(ContactForm::class, ['contact' => $contact])
            ->assertSet('company_name', 'Old Name')
            ->set('company_name', 'Updated Name')
            ->call('save')
            ->assertHasNoErrors();

        expect($contact->fresh()->company_name)->toBe('Updated Name');
    });

    it('deletes a contact with no waivers', function () {
        $contact = LienContact::factory()->forBusiness($this->business)->create();

        Livewire::test(ContactList::class)
            ->call('deleteContact', $contact->id);

        expect(LienContact::find($contact->id))->toBeNull();
    });

    it('refuses to delete a contact still used on a waiver', function () {
        $contact = LienContact::factory()->forBusiness($this->business)->create();
        LienWaiver::factory()->forBusiness($this->business)->create(['lien_contact_id' => $contact->id]);

        Livewire::test(ContactList::class)
            ->call('deleteContact', $contact->id);

        expect(LienContact::find($contact->id))->not->toBeNull();
    });
});

describe('tenant isolation', function () {
    it('404s when opening another business contact for edit', function () {
        $otherBusiness = Business::factory()->create();
        $foreign = LienContact::factory()->forBusiness($otherBusiness)->create();

        $this->get(route('lien.waivers.contacts.edit', $foreign))->assertNotFound();
    });

    it('does not list another business contacts', function () {
        $otherBusiness = Business::factory()->create();
        LienContact::factory()->forBusiness($otherBusiness)->create(['company_name' => 'Foreign Vendor Inc']);

        Livewire::test(ContactList::class)->assertDontSee('Foreign Vendor Inc');
    });
});
