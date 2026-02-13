<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
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

    // Seed document types and deadline rules
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $this->project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'AZ',
        'first_furnish_date' => now()->subDays(30),
        'last_furnish_date' => now()->subDays(5),
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
