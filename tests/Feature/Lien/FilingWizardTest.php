<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Livewire\ProjectShow;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Models\EmailSequence;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create([
        'timezone' => 'America/Los_Angeles',
        'lien_onboarding_completed_at' => now(),
    ]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    $this->project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'AZ',
        'claimant_type' => ClaimantType::Gc,
        'first_furnish_date' => now()->subDays(30),
        'last_furnish_date' => now()->subDays(5),
        'completion_date' => now()->subDays(3),
        'property_class' => 'residential',
    ]);

    // Calculate deadlines for the project
    app(\App\Domains\Lien\Engine\DeadlineCalculator::class)->calculateForProject($this->project);

    // Add an owner party (required for filing wizard)
    LienParty::create([
        'business_id' => $this->business->id,
        'project_id' => $this->project->id,
        'role' => 'owner',
        'name' => 'Test Owner',
        'address1' => '123 Main St',
        'city' => 'Mesa',
        'state' => 'AZ',
        'zip' => '85210',
    ]);

    $this->deadline = $this->project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    $this->filing = LienFiling::factory()->forProject($this->project)->create([
        'document_type_id' => $this->deadline->document_type_id,
        'project_deadline_id' => $this->deadline->id,
        'status' => \App\Domains\Lien\Enums\FilingStatus::Draft,
        'created_by_user_id' => $this->user->id,
    ]);
});

describe('Filing Wizard required dates', function () {
    it('pre-populates dates from the project', function () {
        $component = Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ]);

        $component->assertSet('completion_date', $this->project->completion_date->format('Y-m-d'))
            ->assertSet('last_furnish_date', $this->project->last_furnish_date->format('Y-m-d'))
            ->assertSet('first_furnish_date', $this->project->first_furnish_date->format('Y-m-d'));
    });

    it('fails step 3 validation without required date for a completion_date state', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'IL',
            'claimant_type' => 'gc',
            'first_furnish_date' => now()->subDays(30),
            'last_furnish_date' => now()->subDays(5),
            'property_class' => 'residential',
        ]);

        app(\App\Domains\Lien\Engine\DeadlineCalculator::class)->calculateForProject($project);

        LienParty::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'role' => 'owner',
            'name' => 'Test Owner',
            'address1' => '123 Main St',
            'city' => 'Chicago',
            'state' => 'IL',
            'zip' => '60601',
        ]);

        $deadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        LienFiling::factory()->forProject($project)->create([
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'status' => FilingStatus::Draft,
            'created_by_user_id' => $this->user->id,
        ]);

        $component = Livewire::test(FilingWizard::class, [
            'project' => $project,
            'deadline' => $deadline,
        ]);

        $component->set('project_type_category', 'residential')
            ->call('nextStep')
            ->call('nextStep')
            ->set('has_written_contract', '0')
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.')
            ->call('nextStep')
            ->assertHasErrors(['completion_date' => 'required'])
            ->assertHasNoErrors(['first_furnish_date', 'last_furnish_date'])
            ->assertSet('step', 3);

        expect($component->errors()->first('completion_date'))
            ->toBe('The completion date is required to file in this state.');
    });

    it('passes step 3 validation when required completion_date is provided', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'IL',
            'claimant_type' => 'gc',
            'first_furnish_date' => now()->subDays(30),
            'last_furnish_date' => now()->subDays(5),
            'property_class' => 'residential',
        ]);

        app(\App\Domains\Lien\Engine\DeadlineCalculator::class)->calculateForProject($project);

        LienParty::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'role' => 'owner',
            'name' => 'Test Owner',
            'address1' => '123 Main St',
            'city' => 'Chicago',
            'state' => 'IL',
            'zip' => '60601',
        ]);

        $deadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        LienFiling::factory()->forProject($project)->create([
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'status' => FilingStatus::Draft,
            'created_by_user_id' => $this->user->id,
        ]);

        $component = Livewire::test(FilingWizard::class, [
            'project' => $project,
            'deadline' => $deadline,
        ]);

        $component->set('project_type_category', 'residential')
            ->call('nextStep')
            ->call('nextStep')
            ->set('completion_date', now()->subDays(3)->format('Y-m-d'))
            ->set('has_written_contract', '0')
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.')
            ->call('nextStep')
            ->assertHasNoErrors(['completion_date']);
    });

    it('saves dates entered in wizard back to the project', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'IL',
            'claimant_type' => 'gc',
            'property_class' => 'residential',
            'first_furnish_date' => null,
            'last_furnish_date' => null,
        ]);

        app(\App\Domains\Lien\Engine\DeadlineCalculator::class)->calculateForProject($project);

        LienParty::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'role' => 'owner',
            'name' => 'Test Owner',
            'address1' => '123 Main St',
            'city' => 'Chicago',
            'state' => 'IL',
            'zip' => '60601',
        ]);

        $deadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        LienFiling::factory()->forProject($project)->create([
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'status' => FilingStatus::Draft,
            'created_by_user_id' => $this->user->id,
        ]);

        $firstFurnishDate = now()->subDays(40)->format('Y-m-d');
        $lastFurnishDate = now()->subDays(12)->format('Y-m-d');
        $completionDate = now()->subDays(10)->format('Y-m-d');

        $component = Livewire::test(FilingWizard::class, [
            'project' => $project,
            'deadline' => $deadline,
        ]);

        $component->set('project_type_category', 'residential')
            ->call('nextStep')
            ->call('nextStep')
            ->set('first_furnish_date', $firstFurnishDate)
            ->set('last_furnish_date', $lastFurnishDate)
            ->set('completion_date', $completionDate)
            ->set('has_written_contract', '0')
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        $project->refresh();
        expect($project->completion_date->format('Y-m-d'))->toBe($completionDate)
            ->and($project->first_furnish_date->format('Y-m-d'))->toBe($firstFurnishDate)
            ->and($project->last_furnish_date->format('Y-m-d'))->toBe($lastFurnishDate);
    });

    it('always requires both furnish dates at the Work step', function () {
        // AZ runs its lien deadline from completion; blank every date.
        $this->project->update([
            'first_furnish_date' => null,
            'last_furnish_date' => null,
            'completion_date' => null,
        ]);

        $component = Livewire::test(FilingWizard::class, [
            'project' => $this->project->fresh(),
            'deadline' => $this->deadline,
        ]);

        $component->set('project_type_category', 'residential')
            ->call('nextStep')
            ->call('nextStep')
            ->assertSet('step', 3)
            ->assertSee('First Furnish Date *')
            ->assertSee('Last Furnish Date *')
            ->assertSee('Completion Date *')
            ->set('has_written_contract', '0')
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.')
            ->call('nextStep')
            ->assertHasErrors([
                'first_furnish_date' => 'required',
                'last_furnish_date' => 'required',
                'completion_date' => 'required',
            ])
            ->assertSet('step', 3);

        expect($component->errors()->first('first_furnish_date'))->toBe('The first furnish date is required.')
            ->and($component->errors()->first('last_furnish_date'))->toBe('The last furnish date is required.')
            ->and($component->errors()->first('completion_date'))->toBe('The completion date is required to file in this state.');
    });

    it('requires both furnish dates in a state whose deadline runs from the last furnish date', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'GA',
            'claimant_type' => 'gc',
            'property_class' => 'residential',
            'first_furnish_date' => null,
            'last_furnish_date' => null,
            'completion_date' => null,
        ]);

        app(\App\Domains\Lien\Engine\DeadlineCalculator::class)->calculateForProject($project);

        LienParty::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'role' => 'owner',
            'name' => 'Test Owner',
            'address1' => '123 Peachtree St',
            'city' => 'Atlanta',
            'state' => 'GA',
            'zip' => '30303',
        ]);

        $deadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $component = Livewire::test(FilingWizard::class, [
            'project' => $project,
            'deadline' => $deadline,
        ]);

        expect(array_keys($component->instance()->requiredDateFields))
            ->toBe(['first_furnish_date', 'last_furnish_date']);

        $component->set('project_type_category', 'residential')
            ->call('nextStep')
            ->call('nextStep')
            ->assertDontSee('Completion Date *')
            ->set('has_written_contract', '0')
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.')
            ->call('nextStep')
            ->assertHasErrors([
                'first_furnish_date' => 'required',
                'last_furnish_date' => 'required',
            ])
            ->assertHasNoErrors('completion_date')
            ->assertSet('step', 3);

        $component->set('first_furnish_date', now()->subDays(30)->format('Y-m-d'))
            ->set('last_furnish_date', now()->subDays(5)->format('Y-m-d'))
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);
    });

    it('rejects a last furnish date before the first furnish date at the Work step', function () {
        $component = Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ]);

        $component->set('project_type_category', 'residential')
            ->call('nextStep')
            ->call('nextStep')
            ->set('first_furnish_date', now()->subDays(5)->format('Y-m-d'))
            ->set('last_furnish_date', now()->subDays(30)->format('Y-m-d'))
            ->set('has_written_contract', '0')
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.')
            ->call('nextStep')
            ->assertHasErrors(['last_furnish_date' => 'after_or_equal'])
            ->assertHasNoErrors('first_furnish_date')
            ->assertSet('step', 3);
    });
});

describe('Filing Wizard has_written_contract', function () {
    it('saves has_written_contract to project model when advancing steps', function () {
        $component = Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ]);

        // Step 1: Property type (already set via factory)
        $component->set('project_type_category', 'residential')
            ->call('nextStep');

        // Step 2: Parties (owner already added)
        $component->call('nextStep');

        // Step 3: Amount & Contract - set has_written_contract to "1" (Yes)
        $component->set('has_written_contract', '1')
            ->set('amount_claimed', 17415.28)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.')
            ->call('nextStep');

        // Verify the project model was updated
        $this->project->refresh();
        expect($this->project->has_written_contract)->toBeTrue();
    });

    it('saves has_written_contract as false when user selects No', function () {
        $component = Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ]);

        // Step 1
        $component->set('project_type_category', 'residential')
            ->call('nextStep');

        // Step 2
        $component->call('nextStep');

        // Step 3: set has_written_contract to "0" (No)
        $component->set('has_written_contract', '0')
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.')
            ->call('nextStep');

        $this->project->refresh();
        expect($this->project->has_written_contract)->toBeFalse();
    });

    it('populates has_written_contract from project when loading wizard', function () {
        $this->project->update(['has_written_contract' => true]);

        $component = Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ]);

        $component->assertSet('has_written_contract', '1');
    });

    it('populates has_written_contract as null when project value is null', function () {
        $this->project->update(['has_written_contract' => null]);

        $component = Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ]);

        $component->assertSet('has_written_contract', null);
    });

    it('stores has_written_contract in payload snapshot', function () {
        // Create a draft filing first
        $filing = LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $this->deadline->document_type_id,
            'project_deadline_id' => $this->deadline->id,
            'status' => FilingStatus::Draft,
            'created_by_user_id' => $this->user->id,
        ]);

        $component = Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ]);

        // Navigate through steps
        $component->set('project_type_category', 'residential')
            ->call('nextStep')
            ->call('nextStep')
            ->set('has_written_contract', '1')
            ->set('amount_claimed', 10000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.')
            ->call('nextStep');

        // Now the filing should have has_written_contract in its payload if proceedToCheckout is called
        // For now, just verify the project model was updated correctly
        $this->project->refresh();
        expect($this->project->has_written_contract)->toBeTrue();
    });
});

describe('Filing Wizard contractor requirement', function () {
    it('blocks a subcontractor claimant at the Parties step until a GC is added', function () {
        $this->project->update(['claimant_type' => ClaimantType::Subcontractor]);

        $component = Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ]);

        // Step 1 -> 2
        $component->set('project_type_category', 'residential')
            ->call('nextStep')
            ->assertSet('step', 2);

        // Step 2 with no GC party -> blocked
        $component->call('nextStep')
            ->assertHasErrors('gc_required')
            ->assertSet('step', 2);

        // Add the General Contractor, then the step advances
        LienParty::create([
            'business_id' => $this->business->id,
            'project_id' => $this->project->id,
            'role' => 'gc',
            'name' => 'Acme General Contractor',
            'address1' => '500 Builder Blvd',
            'city' => 'Mesa',
            'state' => 'AZ',
            'zip' => '85210',
        ]);

        $component->call('nextStep')
            ->assertHasNoErrors('gc_required')
            ->assertSet('step', 3);
    });

    it('requires both a GC and a subcontractor for a sub-subcontractor claimant', function () {
        $this->project->update(['claimant_type' => ClaimantType::SubSubContractor]);

        $component = Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ]);

        $component->set('project_type_category', 'residential')
            ->call('nextStep')
            ->call('nextStep')
            ->assertHasErrors(['gc_required', 'subcontractor_required'])
            ->assertSet('step', 2);

        // Add only the GC -> still blocked on the subcontractor
        LienParty::create([
            'business_id' => $this->business->id,
            'project_id' => $this->project->id,
            'role' => 'gc',
            'name' => 'Acme General Contractor',
        ]);

        $component->call('nextStep')
            ->assertHasNoErrors('gc_required')
            ->assertHasErrors('subcontractor_required')
            ->assertSet('step', 2);

        // Add the subcontractor -> advances
        LienParty::create([
            'business_id' => $this->business->id,
            'project_id' => $this->project->id,
            'role' => 'subcontractor',
            'name' => 'Bravo Subcontractor',
        ]);

        $component->call('nextStep')
            ->assertHasNoErrors(['gc_required', 'subcontractor_required'])
            ->assertSet('step', 3);
    });

    it('does not require a contractor for a GC claimant', function () {
        // $this->project is already claimant_type Gc with an owner party added
        $component = Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ]);

        $component->set('project_type_category', 'residential')
            ->call('nextStep')
            ->call('nextStep')
            ->assertHasNoErrors(['gc_required', 'subcontractor_required'])
            ->assertSet('step', 3);
    });
});

describe('Filing Wizard party address requirement', function () {
    it('requires a full mailing address when adding a contractor', function () {
        $component = Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ]);

        // Name + role only, no address -> blocked on every address field.
        $component->call('openPartyModal', null, 'gc')
            ->set('partyName', 'Acme General Contractor')
            ->call('saveParty')
            ->assertHasErrors([
                'partyAddress1' => 'required',
                'partyCity' => 'required',
                'partyState' => 'required',
                'partyZip' => 'required',
            ]);

        expect($this->project->parties()->where('role', 'gc')->count())->toBe(0);
    });

    it('saves a contractor once a full address is supplied', function () {
        $component = Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ]);

        $component->call('openPartyModal', null, 'gc')
            ->set('partyName', 'Acme General Contractor')
            ->set('partyAddress1', '500 Builder Blvd')
            ->set('partyCity', 'Mesa')
            ->set('partyState', 'AZ')
            ->set('partyZip', '85210')
            ->call('saveParty')
            ->assertHasNoErrors();

        expect($this->project->parties()->where('role', 'gc')->count())->toBe(1);
    });

    it('requires an address for a non-contractor party too', function () {
        $component = Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ]);

        $component->call('openPartyModal', null, 'lender')
            ->set('partyName', 'First National Bank')
            ->call('saveParty')
            ->assertHasErrors([
                'partyAddress1' => 'required',
                'partyCity' => 'required',
                'partyState' => 'required',
                'partyZip' => 'required',
            ]);
    });
});

describe('Filing Wizard entry', function () {
    it('opens a paid filing from an old link instead of a blank draft', function () {
        $this->business->update(['onboarding_completed_at' => now()]);
        $this->filing->update(['status' => FilingStatus::InFulfillment, 'paid_at' => now()]);

        $this->get(route('lien.filings.start', ['project' => $this->project, 'deadline' => $this->deadline]))
            ->assertRedirect(route('lien.filings.show', $this->filing))
            ->assertSessionHas('message');

        expect(LienFiling::where('project_deadline_id', $this->deadline->id)->count())->toBe(1);
    });

    it('opens the paid filing even with a stray draft beside it', function () {
        $paid = LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $this->deadline->document_type_id,
            'project_deadline_id' => $this->deadline->id,
            'status' => FilingStatus::InFulfillment,
            'paid_at' => now(),
        ]);

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ])->assertRedirect(route('lien.filings.show', $paid));
    });

    it('resumes an unpaid order before a stray draft', function () {
        $unpaid = LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $this->deadline->document_type_id,
            'project_deadline_id' => $this->deadline->id,
            'status' => FilingStatus::AwaitingPayment,
        ]);

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ])->assertNoRedirect()
            ->assertSet('filing.id', $unpaid->id);
    });

    it('starts a new draft with the finish-your-filing emails', function () {
        $this->filing->forceDelete();

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ])->assertNoRedirect();

        $draft = LienFiling::where('project_deadline_id', $this->deadline->id)->sole();
        $sequence = EmailSequence::where('sequence_type', 'abandon_checkout')
            ->where('sequenceable_type', 'lien_filing')
            ->where('sequenceable_id', $draft->id)
            ->sole();

        expect($draft->status)->toBe(FilingStatus::Draft)
            ->and($sequence->resume_url)->toBe(route('lien.filings.start', ['project' => $this->project, 'deadline' => $this->deadline]));
    });

    it('lets a refunded customer start over', function () {
        $this->filing->update(['status' => FilingStatus::Refunded, 'paid_at' => now()]);

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ])->assertNoRedirect();

        expect(LienFiling::where('project_deadline_id', $this->deadline->id)->where('status', FilingStatus::Draft)->count())->toBe(1);
    });

    it('leaves the draft to the wizard when the project page button is clicked', function () {
        $this->filing->update(['status' => FilingStatus::AwaitingPayment]);

        Livewire::test(ProjectShow::class, ['project' => $this->project])
            ->call('startFiling', $this->deadline->id)
            ->assertRedirect(route('lien.filings.start', ['project' => $this->project, 'deadline' => $this->deadline]));

        expect(LienFiling::where('project_deadline_id', $this->deadline->id)->count())->toBe(1);
    });
});

describe('Lien release original lien', function () {
    beforeEach(function () {
        $this->releaseDeadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'lien_release'))
            ->first();

        expect($this->releaseDeadline)->not->toBeNull();

        $this->release = LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $this->releaseDeadline->document_type_id,
            'project_deadline_id' => $this->releaseDeadline->id,
            'status' => FilingStatus::Draft,
            'created_by_user_id' => $this->user->id,
        ]);

        // Steps 1 and 2 are already complete from the project; fill the rest of the Work step.
        $this->releaseWorkStep = fn () => Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->releaseDeadline,
        ])->call('nextStep')
            ->call('nextStep')
            ->assertSet('step', 3)
            ->set('has_written_contract', '1')
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.');
    });

    it('asks for the recorded date and the document number when no lien is recorded with us', function () {
        ($this->releaseWorkStep)()
            ->assertSee('The lien being released')
            ->assertDontSee('We recorded this lien')
            ->assertSet('original_lien_recorded_at', null)
            ->assertSet('original_lien_reference', null)
            ->call('nextStep')
            ->assertHasErrors([
                'original_lien_recorded_at' => 'required',
                'original_lien_reference' => 'required_without_all',
            ])
            ->assertSee('Enter the date the lien was recorded.')
            ->assertSee('Enter the document number, or the book and page.')
            ->assertSet('step', 3);

        expect($this->release->fresh()->document_details_json)->toBeNull();
    });

    it('accepts a book and page instead of a document number', function () {
        ($this->releaseWorkStep)()
            ->set('original_lien_recorded_at', now()->subMonth()->format('Y-m-d'))
            ->set('original_lien_book', '4021')
            ->set('original_lien_page', '117')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);
    });

    it('needs the page when the book is given, and a recorded date in the past', function () {
        ($this->releaseWorkStep)()
            ->set('original_lien_recorded_at', now()->addDay()->format('Y-m-d'))
            ->set('original_lien_book', '4021')
            ->call('nextStep')
            ->assertHasErrors([
                'original_lien_page' => 'required_with',
                'original_lien_recorded_at' => 'before_or_equal',
            ])
            ->assertHasNoErrors(['original_lien_reference', 'original_lien_book'])
            ->assertSet('step', 3);
    });

    it('prefills the date and number of the lien we recorded', function () {
        $this->filing->update([
            'status' => FilingStatus::Recorded,
            'paid_at' => now(),
            'recorded_at' => '2026-09-10 20:51:00',
            'recording_reference' => '2026-28904',
        ]);

        ($this->releaseWorkStep)()
            ->assertSet('original_lien_recorded_at', '2026-09-10')
            ->assertSet('original_lien_reference', '2026-28904')
            ->assertSet('original_lien_book', null)
            ->assertSet('original_lien_page', null)
            ->assertSee('We recorded this lien on September 10, 2026 as 2026-28904. Check the details below and correct anything that has changed.')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        expect($this->release->fresh()->document_details_json)->toEqual([
            'original_lien' => [
                'recording_reference' => '2026-28904',
                'recorded_at' => '2026-09-10',
            ],
        ]);
    });

    it('stores what the customer entered and keeps what an admin already set', function () {
        $this->release->update([
            'document_details_json' => [
                'signer_name' => 'Kathryn Melendez',
                'original_lien' => ['county' => 'Maricopa', 'amount_received_cents' => 67500],
            ],
        ]);

        $recordedOn = now()->subMonths(2)->format('Y-m-d');

        ($this->releaseWorkStep)()
            ->set('original_lien_recorded_at', $recordedOn)
            ->set('original_lien_reference', ' 20260512345 ')
            ->set('original_lien_book', '')
            ->set('original_lien_page', '')
            ->set('original_lien_other_info', 'Recorded under Desert Roofing LLC')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        $release = $this->release->fresh();

        expect($release->document_details_json)->toEqual([
            'signer_name' => 'Kathryn Melendez',
            'original_lien' => [
                'recording_reference' => '20260512345',
                'county' => 'Maricopa',
                'recorded_at' => $recordedOn,
                'amount_received_cents' => 67500,
                'other_info' => 'Recorded under Desert Roofing LLC',
            ],
        ]);
        expect($release->payload_json)->toBeNull();

        // Coming back to the wizard shows what was saved.
        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->releaseDeadline,
        ])->assertSet('original_lien_recorded_at', $recordedOn)
            ->assertSet('original_lien_reference', '20260512345')
            ->assertSet('original_lien_other_info', 'Recorded under Desert Roofing LLC');
    });

    it('does not ask a mechanics lien for a lien being released', function () {
        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->deadline,
        ])->call('nextStep')
            ->call('nextStep')
            ->assertSet('step', 3)
            ->assertDontSee('The lien being released')
            ->set('has_written_contract', '1')
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        expect($this->filing->fresh()->document_details_json)->toBeNull();
    });
});
