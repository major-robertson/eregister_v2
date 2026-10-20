<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Livewire\PartyManager;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

beforeEach(function () {
    // Tuesday, October 20, 2026: the Kansas lien deadline below is December 14.
    $this->travelTo(CarbonImmutable::parse('2026-10-20 12:00:00', 'America/New_York')->utc());
    Storage::fake('s3');
    LienDocumentRegistry::flush();

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create(['lien_onboarding_completed_at' => now()]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

afterEach(fn () => LienDocumentRegistry::flush());

if (! function_exists('partiesProject')) {
    /** A Kansas project with a known jobsite address and no "who hired you" answer. */
    function partiesProject(Business $business, ClaimantType $type, array $overrides = []): LienProject
    {
        $project = LienProject::factory()->forBusiness($business)->create(array_merge([
            'jobsite_address1' => '100 Jobsite Rd',
            'jobsite_address2' => null,
            'jobsite_city' => 'Wichita',
            'jobsite_state' => 'KS',
            'jobsite_county' => 'Sedgwick',
            'jobsite_zip' => '67202',
            'claimant_type' => $type,
            'hired_by' => null,
            'property_class' => 'residential',
            'first_furnish_date' => '2026-07-01',
            'last_furnish_date' => '2026-08-14',
            'completion_date' => '2026-08-14',
            // Liens and releases need a legal description or parcel number (EREG-136).
            'apn' => '087-123-04-0-11-02-005.00',
        ], $overrides));

        app(DeadlineCalculator::class)->calculateForProject($project);

        return $project;
    }

    function partiesParty(LienProject $project, string $role, array $attributes = []): LienParty
    {
        return LienParty::create(array_merge([
            'business_id' => $project->business_id,
            'project_id' => $project->id,
            'role' => $role,
            'name' => ucfirst($role).' Name',
            'address1' => '500 Builder Blvd',
            'city' => 'Topeka',
            'state' => 'KS',
            'zip' => '66603',
        ], $attributes));
    }

    /** The wizard on a full-service mechanics lien draft for the project. */
    function partiesWizard(LienProject $project): Testable
    {
        $deadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->latest('id')
            ->firstOrFail();

        LienFiling::factory()->forProject($project)->create([
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'status' => FilingStatus::Draft,
            'service_level' => 'full_service',
            'created_by_user_id' => auth()->id(),
        ]);

        return Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline]);
    }
}

describe('Parties step: the party that hired the claimant', function () {
    it('needs a full mailing address before the step passes', function (ClaimantType $type, string $hiringRole, array $otherRoles) {
        $project = partiesProject($this->business, $type);

        if ($hiringRole !== 'owner') {
            partiesParty($project, 'owner');
        }

        // Parties that did not hire the claimant can go without an address here.
        foreach ($otherRoles as $role) {
            partiesParty($project, $role, ['address1' => null, 'zip' => null]);
        }

        $hiring = partiesParty($project, $hiringRole, ['name' => 'Hiring Co', 'address1' => null, 'zip' => null]);
        $phrase = PartyRole::from($hiringRole)->phrase();

        $component = partiesWizard($project)
            ->call('nextStep')
            ->call('nextStep')
            ->assertHasErrors('party_'.$hiring->id)
            ->assertSet('step', 2);

        expect($component->errors()->get('party_'.$hiring->id))->toBe([
            "Hiring Co ({$phrase}) hired you, so we need their full mailing address. Add their street address and ZIP code.",
        ]);

        $component->assertSee("Hiring Co ({$phrase}) hired you");

        $hiring->update(['address1' => '500 Builder Blvd', 'zip' => '66603']);

        $component->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 3);
    })->with([
        'general contractor: the owner' => [ClaimantType::Gc, 'owner', []],
        'supplier to the owner: the owner' => [ClaimantType::SupplierToOwner, 'owner', []],
        'subcontractor: the general contractor' => [ClaimantType::Subcontractor, 'gc', []],
        'supplier to a contractor: the general contractor' => [ClaimantType::SupplierToContractor, 'gc', []],
        'sub-subcontractor: the subcontractor' => [ClaimantType::SubSubContractor, 'subcontractor', ['gc']],
        'supplier to a subcontractor: the subcontractor' => [ClaimantType::SupplierToSubcontractor, 'subcontractor', ['gc']],
    ]);

    it('needs a full mailing address for a customer party too', function () {
        $project = partiesProject($this->business, ClaimantType::Gc);
        partiesParty($project, 'owner');
        $customer = partiesParty($project, 'customer', ['name' => 'Jane Customer', 'city' => null]);

        $component = partiesWizard($project)
            ->call('nextStep')
            ->call('nextStep')
            ->assertSet('step', 2);

        expect($component->errors()->get('party_'.$customer->id))->toBe([
            'Jane Customer (customer) hired you, so we need their full mailing address. Add their city.',
        ]);
    });

    it('fills in who hired the claimant from the hiring party, and keeps an answer already given', function () {
        $project = partiesProject($this->business, ClaimantType::Subcontractor);
        partiesParty($project, 'owner');
        $gc = partiesParty($project, 'gc');

        partiesWizard($project)->call('nextStep')->call('nextStep')->assertSet('step', 3);

        $project->refresh();
        expect($project->hired_by)->toBe('direct_contractor')
            ->and($project->hiringParty()?->id)->toBe($gc->id)
            ->and($project->customerParty()?->id)->toBe($gc->id);

        // A customer party, when there is one, is the customer party.
        $customer = partiesParty($project, 'customer');
        expect($project->customerParty()?->id)->toBe($customer->id);

        $other = partiesProject($this->business, ClaimantType::Gc, ['hired_by' => 'owner']);
        partiesParty($other, 'owner');
        partiesWizard($other)->call('nextStep')->call('nextStep')->assertSet('step', 3);

        expect($other->fresh()->hired_by)->toBe('owner');
    });
});

describe('Parties step: the owner of record', function () {
    it('asks for the owner of record, not the tenant or the manager', function () {
        $project = partiesProject($this->business, ClaimantType::Gc);

        partiesWizard($project)
            ->call('nextStep')
            ->assertSee('Property owner (owner of record)')
            ->assertSee('The person or company on the deed. Not the tenant, the property manager or the HOA.')
            ->assertSee('Is the property leased to someone else?');
    });

    it('offers the tenant and property manager roles in the party form', function () {
        $project = partiesProject($this->business, ClaimantType::Gc);

        partiesWizard($project)
            ->call('nextStep')
            ->call('openPartyModal', null, 'tenant')
            ->assertSee('Tenant / Lessee')
            ->assertSee('Property manager')
            ->assertSee('Optional. For our records only.');

        expect(PartyRole::Tenant->label())->toBe('Tenant / Lessee')
            ->and(PartyRole::PropertyManager->label())->toBe('Property manager')
            ->and(PartyRole::Tenant->isInformational())->toBeTrue()
            ->and(PartyRole::PropertyManager->isInformational())->toBeTrue()
            ->and(PartyRole::Owner->isInformational())->toBeFalse();
    });

    it('never counts a tenant or a property manager as the owner', function () {
        $project = partiesProject($this->business, ClaimantType::Gc);
        partiesParty($project, 'tenant');
        partiesParty($project, 'property_manager');

        partiesWizard($project)
            ->call('nextStep')
            ->call('nextStep')
            ->assertHasErrors('owner_required')
            ->assertSet('step', 2);

        expect($project->ownerParty())->toBeNull();
    });

    it('offers to add the tenant when the property is leased, and keeps the answer', function () {
        $project = partiesProject($this->business, ClaimantType::Gc);
        partiesParty($project, 'owner');

        $component = partiesWizard($project)
            ->call('nextStep')
            ->assertDontSee('Add tenant')
            ->set('property_leased', 'yes')
            ->assertSee('Add tenant')
            ->call('openPartyModal', null, 'tenant')
            ->assertSet('partyRole', 'tenant')
            ->set('partyName', 'Corner Cafe LLC')
            ->set('partyAddress1', '100 Jobsite Rd')
            ->set('partyCity', 'Wichita')
            ->set('partyState', 'KS')
            ->set('partyZip', '67202')
            ->call('saveParty')
            ->assertHasNoErrors()
            ->assertDontSee('Add tenant')
            ->call('nextStep')
            ->assertSet('step', 3);

        $project->refresh();
        expect($project->property_leased)->toBeTrue()
            ->and($project->parties()->where('role', 'tenant')->count())->toBe(1)
            ->and($project->ownerParty()->name)->toBe('Owner Name');

        // Coming back, the answer is still there.
        partiesWizard($project)->assertSet('property_leased', 'yes');

        $component->call('previousStep')->set('property_leased', 'no')->call('nextStep');
        expect($project->fresh()->property_leased)->toBeFalse();
    });

    it('keeps tenants and property managers off the recipients and the demand letters', function () {
        $project = partiesProject($this->business, ClaimantType::Gc);
        $owner = partiesParty($project, 'owner');
        $customer = partiesParty($project, 'customer');
        partiesParty($project, 'tenant');
        partiesParty($project, 'property_manager');

        $component = partiesWizard($project)
            ->call('nextStep')
            ->call('nextStep')
            ->assertSet('step', 3)
            ->set('has_written_contract', '1')
            ->fill(['base_contract_amount' => '5000', 'credits_deductions' => '0', 'payments_received' => '0'])
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.')
            ->call('nextStep')
            ->assertSet('step', 4)
            ->set('disclaimerAccepted', true)
            ->set('claimAttested', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors();

        $filing = LienFiling::where('project_id', $project->id)->latest('id')->first();
        $component->assertRedirect(route('lien.filings.checkout', $filing));

        expect($filing->recipients()->pluck('party_id')->sort()->values()->all())
            ->toBe(collect([$owner->id, $customer->id])->sort()->values()->all())
            ->and($project->fresh()->nonClaimantParties()->pluck('id')->sort()->values()->all())
            ->toBe(collect([$owner->id, $customer->id])->sort()->values()->all());
    });
});

describe('Party address checks', function () {
    it('warns about a ZIP code from another state and saves once the customer confirms it', function () {
        $project = partiesProject($this->business, ClaimantType::Subcontractor);

        $component = partiesWizard($project)
            ->call('nextStep')
            ->call('openPartyModal', null, 'gc')
            ->set('partyName', 'Acme Builders')
            ->set('partyAddress1', '500 Builder Blvd')
            ->set('partyCity', 'Topeka')
            ->set('partyState', 'KS')
            ->set('partyZip', '85210')
            ->call('saveParty')
            ->assertHasErrors('partyAddressConfirmed')
            ->assertHasNoErrors('partyZip')
            ->assertSee('ZIP code 85210 is in Arizona, not Kansas. Check the ZIP code and the state.')
            ->assertSee('This address is correct as typed');

        expect($component->errors()->first('partyAddressConfirmed'))
            ->toBe('Fix the ZIP code or the state. If the address is right, tick the box.')
            ->and($project->parties()->count())->toBe(0);

        $component->set('partyAddressConfirmed', true)
            ->call('saveParty')
            ->assertHasNoErrors();

        $gc = $project->gcParty();
        expect($gc->zip)->toBe('85210')
            ->and($gc->address_confirmed_by_customer)->toBeTrue();
    });

    it('saves a fixed ZIP code without the tick or the confirmation', function () {
        $project = partiesProject($this->business, ClaimantType::Subcontractor);

        partiesWizard($project)
            ->call('nextStep')
            ->call('openPartyModal', null, 'gc')
            ->set('partyName', 'Acme Builders')
            ->set('partyAddress1', '500 Builder Blvd')
            ->set('partyCity', 'Topeka')
            ->set('partyState', 'KS')
            ->set('partyZip', '85210')
            ->call('saveParty')
            ->assertHasErrors('partyAddressConfirmed')
            ->set('partyZip', '66603')
            ->call('saveParty')
            ->assertHasNoErrors();

        expect($project->gcParty()->address_confirmed_by_customer)->toBeNull();
    });

    it('passes a ZIP code whose prefix is not in the table without a warning', function () {
        $project = partiesProject($this->business, ClaimantType::Subcontractor);

        partiesWizard($project)
            ->call('nextStep')
            ->call('openPartyModal', null, 'gc')
            ->set('partyName', 'Acme Builders')
            ->set('partyAddress1', '500 Builder Blvd')
            ->set('partyCity', 'Topeka')
            ->set('partyState', 'KS')
            ->set('partyZip', '26900')
            ->assertDontSee('This address is correct as typed')
            ->call('saveParty')
            ->assertHasNoErrors();

        $gc = $project->gcParty();
        expect($gc->zip)->toBe('26900')
            ->and($gc->address_confirmed_by_customer)->toBeNull();
    });

    it('needs the tick before a general contractor can use the jobsite address', function () {
        $project = partiesProject($this->business, ClaimantType::Subcontractor);

        $component = partiesWizard($project)
            ->call('nextStep')
            ->call('openPartyModal', null, 'gc')
            ->set('partyName', 'Acme Builders')
            ->set('partyAddress1', '100 Jobsite Road')
            ->set('partyCity', 'Wichita')
            ->set('partyState', 'KS')
            ->set('partyZip', '67202')
            ->call('saveParty')
            ->assertHasErrors('partySameAsJobsiteConfirmed')
            ->assertSee('This is really their mailing address');

        expect($component->errors()->first('partySameAsJobsiteConfirmed'))
            ->toBe('This is the jobsite address. Enter their mailing address. If this really is their mailing address, tick the box.')
            ->and($project->parties()->count())->toBe(0);

        $component->set('partySameAsJobsiteConfirmed', true)
            ->call('saveParty')
            ->assertHasNoErrors();

        expect($project->gcParty()->address_same_as_jobsite_confirmed)->toBeTrue();
    });

    it('lets the owner use the jobsite address without the tick when someone else hired the claimant', function () {
        $project = partiesProject($this->business, ClaimantType::Subcontractor);

        partiesWizard($project)
            ->call('nextStep')
            ->call('openPartyModal')
            ->set('partyName', 'Pat Owner')
            ->call('useJobsiteAddress')
            ->assertDontSee('This is really their mailing address')
            ->call('saveParty')
            ->assertHasNoErrors();

        expect($project->ownerParty()->address_same_as_jobsite_confirmed)->toBeFalse();
    });

    it('lets an owner who hired the claimant use the jobsite address without the tick', function () {
        $project = partiesProject($this->business, ClaimantType::Gc);

        partiesWizard($project)
            ->call('nextStep')
            ->call('openPartyModal')
            ->set('partyName', 'Pat Owner')
            ->call('useJobsiteAddress')
            ->assertDontSee('This is really their mailing address')
            ->call('saveParty')
            ->assertHasNoErrors()
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 3);

        expect($project->ownerParty()->address_same_as_jobsite_confirmed)->toBeFalse();
    });

    it('needs the tick for a subcontractor at the jobsite address', function () {
        $project = partiesProject($this->business, ClaimantType::SubSubContractor);

        partiesWizard($project)
            ->call('nextStep')
            ->call('openPartyModal', null, 'subcontractor')
            ->set('partyName', 'Bravo Subs')
            ->set('partyAddress1', '100 Jobsite Rd')
            ->set('partyCity', 'Wichita')
            ->set('partyState', 'KS')
            ->set('partyZip', '67202')
            ->call('saveParty')
            ->assertHasErrors('partySameAsJobsiteConfirmed')
            ->set('partySameAsJobsiteConfirmed', true)
            ->call('saveParty')
            ->assertHasNoErrors();

        expect($project->subcontractorParty()->address_same_as_jobsite_confirmed)->toBeTrue();
    });

    it('blocks the step for a general contractor saved at the jobsite address without the tick', function () {
        $project = partiesProject($this->business, ClaimantType::Subcontractor);
        partiesParty($project, 'owner');
        $gc = partiesParty($project, 'gc', [
            'name' => 'Acme Builders',
            'address1' => '100 Jobsite Rd',
            'city' => 'Wichita',
            'zip' => '67202',
        ]);

        $component = partiesWizard($project)
            ->call('nextStep')
            ->call('nextStep')
            ->assertSet('step', 2);

        expect($component->errors()->get('party_'.$gc->id))->toBe([
            'The address for Acme Builders (general contractor) is the jobsite address. Edit it to their mailing address. If the jobsite really is their mailing address, edit the party and tick the box.',
        ]);

        $gc->update(['address_same_as_jobsite_confirmed' => true]);

        $component->call('nextStep')->assertHasNoErrors()->assertSet('step', 3);
    });

    it('warns about an address with no street number without blocking', function () {
        $project = partiesProject($this->business, ClaimantType::Subcontractor);
        partiesParty($project, 'owner');
        partiesParty($project, 'gc', ['address1' => 'Builder Blvd']);

        partiesWizard($project)
            ->call('nextStep')
            ->assertSee('This address has no street number. Check it before you continue.')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 3);
    });

    it('fills the party address from a Places pick', function () {
        $project = partiesProject($this->business, ClaimantType::Subcontractor);

        partiesWizard($project)
            ->call('nextStep')
            ->call('openPartyModal', null, 'gc')
            ->assertSee("Can't find the address? Type it in.", false)
            ->call('updatePartyAddressFromAutocomplete', [
                'line1' => '500 Builder Blvd',
                'city' => 'Topeka',
                'state' => 'KS',
                'zip' => '66603',
                'county' => 'Shawnee',
            ])
            ->assertSet('partyAddress1', '500 Builder Blvd')
            ->assertSet('partyCity', 'Topeka')
            ->assertSet('partyState', 'KS')
            ->assertSet('partyZip', '66603');
    });

    it('applies the ZIP and jobsite checks on the project party form too', function () {
        $project = partiesProject($this->business, ClaimantType::Subcontractor);

        $component = Livewire::test(PartyManager::class, ['project' => $project])
            ->call('openModal')
            ->set('role', 'gc')
            ->set('name', 'Acme Builders')
            ->set('address1', '100 Jobsite Rd')
            ->set('city', 'Wichita')
            ->set('state', 'KS')
            ->set('zip', '85210')
            ->call('save')
            ->assertHasErrors('addressConfirmed')
            ->assertHasNoErrors('zip')
            ->assertSee('ZIP code 85210 is in Arizona, not Kansas. Check the ZIP code and the state.')
            ->assertSee('This address is correct as typed');

        expect($project->parties()->count())->toBe(0);

        $component->set('zip', '67202')
            ->call('save')
            ->assertHasErrors('sameAsJobsiteConfirmed')
            ->set('sameAsJobsiteConfirmed', true)
            ->call('save')
            ->assertHasNoErrors();

        $gc = $project->gcParty();
        expect($gc->address_same_as_jobsite_confirmed)->toBeTrue()
            ->and($gc->address_confirmed_by_customer)->toBeNull();

        // A mismatched ZIP code saves once ticked, and the tick is kept.
        $component->call('openModal', $gc->id)
            ->set('zip', '85210')
            ->set('addressConfirmed', true)
            ->call('save')
            ->assertHasNoErrors();

        expect($gc->fresh()->address_confirmed_by_customer)->toBeTrue();
    });
});

describe('Admin filing page', function () {
    it('shows the role labels and the jobsite confirmation on the party cards', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view', 'lien.update');

        $project = partiesProject($this->business, ClaimantType::Subcontractor);
        $gc = partiesParty($project, 'gc', [
            'address1' => '100 Jobsite Rd',
            'city' => 'Wichita',
            'zip' => '67202',
            'address_same_as_jobsite_confirmed' => true,
        ]);
        partiesParty($project, 'tenant', ['name' => 'Corner Cafe LLC']);

        $filing = LienFiling::factory()->forProject($project)->paid()->create([
            'parties_snapshot_json' => [$gc->toSnapshot()],
        ]);

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('General Contractor / Direct Contractor')
            ->assertSee('Tenant / Lessee')
            ->assertSee('Jobsite address confirmed as mailing address');
    });

    it('warns about a ZIP code from another state on the admin party form but never blocks', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view', 'lien.update');

        $project = partiesProject($this->business, ClaimantType::Gc);
        $filing = LienFiling::factory()->forProject($project)->paid()->create();

        $this->actingAs($admin);

        $warning = 'ZIP code 90002 is in California, not Kansas. Check the ZIP code and the state.';

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('addParty')
            ->set('partyForm.role', PartyRole::Owner->value)
            ->set('partyForm.name', 'New Owner')
            ->set('partyForm.address1', '5 Elm St')
            ->set('partyForm.city', 'Topeka')
            ->set('partyForm.state', 'KS')
            ->set('partyForm.zip', '90002')
            ->assertSee($warning)
            ->call('saveParty')
            ->assertHasNoErrors()
            ->assertSee($warning);

        expect($project->ownerParty()->zip)->toBe('90002');
    });
});
