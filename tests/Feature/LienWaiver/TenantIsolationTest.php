<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Livewire\Waivers\WaiverDashboard;
use App\Domains\Lien\Livewire\Waivers\WaiverList;
use App\Domains\Lien\Livewire\Waivers\WaiverWizard;
use App\Domains\Lien\Models\LienContact;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->userA = User::factory()->create();
    $this->businessA = Business::factory()->create([
        'name' => 'Business A',
        'onboarding_completed_at' => now(),
        'lien_onboarding_completed_at' => now(),
    ]);
    $this->businessA->users()->attach($this->userA, ['role' => 'owner']);

    $this->userB = User::factory()->create();
    $this->businessB = Business::factory()->create([
        'name' => 'Business B',
        'onboarding_completed_at' => now(),
        'lien_onboarding_completed_at' => now(),
    ]);
    $this->businessB->users()->attach($this->userB, ['role' => 'owner']);

    $this->projectA = LienProject::factory()->forBusiness($this->businessA)->inState('TX')->create([
        'name' => 'Alpha Project',
        'wizard_completed_at' => now(),
    ]);
    $this->projectB = LienProject::factory()->forBusiness($this->businessB)->inState('TX')->create([
        'name' => 'Bravo Project',
        'wizard_completed_at' => now(),
    ]);

    $this->waiverA = LienWaiver::factory()->forProject($this->projectA)->generated()->create([
        'counterparty_company' => 'Alpha Counterparty Co',
    ]);
    $this->waiverB = LienWaiver::factory()->forProject($this->projectB)->generated()->create([
        'counterparty_company' => 'Bravo Counterparty Co',
    ]);

    // Act as business A for every test.
    $this->actingAs($this->userA);
    session(['current_business_id' => $this->businessA->id]);
});

it('404s another tenant\'s waiver page while serving your own', function () {
    $this->get(route('lien.waivers.show', $this->waiverA))
        ->assertSuccessful();

    $this->get(route('lien.waivers.show', $this->waiverB))
        ->assertNotFound();
});

it('404s another tenant\'s waiver download', function () {
    $this->waiverB->addMediaFromString('%PDF-1.4 fake')
        ->usingFileName('waiver.pdf')
        ->toMediaCollection('generated');

    $this->get(route('lien.waivers.download', $this->waiverB))
        ->assertNotFound();

    $this->get(route('lien.waivers.download', ['waiver' => $this->waiverB, 'copy' => 'signed']))
        ->assertNotFound();
});

it('scopes the waiver list to the current business', function () {
    Livewire::test(WaiverList::class)
        ->assertSee('Alpha Counterparty Co')
        ->assertDontSee('Bravo Counterparty Co');
});

it('scopes the dashboard counts to the current business', function () {
    // Two more open waivers for business B; they must not leak into A's tiles.
    LienWaiver::factory()->count(2)->forProject($this->projectB)->create();

    $component = Livewire::test(WaiverDashboard::class)
        ->assertSee('Alpha Counterparty Co')
        ->assertDontSee('Bravo Counterparty Co');

    // Only waiverA (Generated counts toward the drafts tile); B's rows don't leak.
    expect($component->viewData('draftCount'))->toBe(1);
    expect($component->viewData('savedThisMonth'))->toBe(1);
});

it('only lists the current business\'s projects in the wizard combobox', function () {
    $component = Livewire::test(WaiverWizard::class)
        ->call('selectDirection', 'provide')
        ->call('nextStep')
        ->assertSee('Alpha Project')
        ->assertDontSee('Bravo Project');

    $projects = $component->viewData('projects');
    expect($projects->contains('id', $this->projectA->id))->toBeTrue();
    expect($projects->contains('id', $this->projectB->id))->toBeFalse();
});

it('drops a cross-tenant ?project= deep link and rejects the id outright', function () {
    Livewire::withQueryParams(['project' => $this->projectB->public_id])
        ->test(WaiverWizard::class)
        ->assertSet('projectId', '');

    // Even if the id is forced in, step 2 refuses it.
    Livewire::test(WaiverWizard::class)
        ->call('selectDirection', 'provide')
        ->call('nextStep')
        ->set('projectId', $this->projectB->public_id)
        ->call('nextStep')
        ->assertHasErrors('projectId')
        ->assertSet('step', 2);
});

it('only exposes the current business\'s contacts in the wizard', function () {
    $contactA = LienContact::factory()->forBusiness($this->businessA)->create([
        'company_name' => 'Alpha Concrete Supply',
    ]);
    $contactB = LienContact::factory()->forBusiness($this->businessB)->create([
        'company_name' => 'Bravo Steel Supply',
    ]);

    $component = Livewire::test(WaiverWizard::class);

    $contacts = $component->viewData('contacts');
    expect($contacts->contains('id', $contactA->id))->toBeTrue();
    expect($contacts->contains('id', $contactB->id))->toBeFalse();

    // A forced cross-tenant contact id resolves to nothing (global scope).
    $component->set('contactId', (string) $contactB->id);
    expect($component->instance()->selectedContact())->toBeNull();

    $component->set('contactId', (string) $contactA->id);
    expect($component->instance()->selectedContact()?->id)->toBe($contactA->id);
});
