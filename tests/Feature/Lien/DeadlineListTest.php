<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Livewire\DeadlineList;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create([
        'timezone' => 'America/Los_Angeles',
        'lien_onboarding_completed_at' => now(),
    ]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    // Set current business in session
    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    // Seed required data
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);
});

it('can render the deadlines list component', function () {
    Livewire::test(DeadlineList::class)
        ->assertSuccessful()
        ->assertSee('Deadlines');
});

it('shows empty state when no deadlines exist', function () {
    Livewire::test(DeadlineList::class)
        ->assertSee('No deadlines')
        ->assertSee('View Projects');
});

it('displays deadlines in the table', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Test Project Alpha',
    ]);

    $documentType = LienDocumentType::where('slug', 'prelim_notice')->first();

    LienProjectDeadline::factory()->create([
        'business_id' => $this->business->id,
        'project_id' => $project->id,
        'document_type_id' => $documentType->id,
        'due_date' => today()->addDays(10),
        'status' => DeadlineStatus::Pending,
    ]);

    Livewire::test(DeadlineList::class)
        ->assertSee('Test Project Alpha')
        ->assertSee($documentType->name);
});

it('can filter deadlines by status', function () {
    $pendingProject = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Pending Project',
    ]);

    $completedProject = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Completed Project',
    ]);

    $documentType = LienDocumentType::where('slug', 'prelim_notice')->first();

    LienProjectDeadline::factory()->create([
        'business_id' => $this->business->id,
        'project_id' => $pendingProject->id,
        'document_type_id' => $documentType->id,
        'due_date' => today()->addDays(10),
        'status' => DeadlineStatus::Pending,
    ]);

    LienProjectDeadline::factory()->create([
        'business_id' => $this->business->id,
        'project_id' => $completedProject->id,
        'document_type_id' => $documentType->id,
        'due_date' => today()->subDays(5),
        'status' => DeadlineStatus::Completed,
    ]);

    Livewire::test(DeadlineList::class)
        ->set('statusFilter', DeadlineStatus::Pending->value)
        ->assertSee('Pending Project')
        ->assertDontSee('Completed Project');
});

it('can search deadlines by project name', function () {
    $project1 = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Alpha Construction',
    ]);

    $project2 = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Beta Building',
    ]);

    $documentType = LienDocumentType::where('slug', 'prelim_notice')->first();

    LienProjectDeadline::factory()->create([
        'business_id' => $this->business->id,
        'project_id' => $project1->id,
        'document_type_id' => $documentType->id,
        'due_date' => today()->addDays(10),
        'status' => DeadlineStatus::Pending,
    ]);

    LienProjectDeadline::factory()->create([
        'business_id' => $this->business->id,
        'project_id' => $project2->id,
        'document_type_id' => $documentType->id,
        'due_date' => today()->addDays(15),
        'status' => DeadlineStatus::Pending,
    ]);

    Livewire::test(DeadlineList::class)
        ->set('search', 'Alpha')
        ->assertSee('Alpha Construction')
        ->assertDontSee('Beta Building');
});

it('shows overdue styling for past due deadlines', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Overdue Project',
    ]);

    $documentType = LienDocumentType::where('slug', 'prelim_notice')->first();

    LienProjectDeadline::factory()->create([
        'business_id' => $this->business->id,
        'project_id' => $project->id,
        'document_type_id' => $documentType->id,
        'due_date' => today()->subDays(3),
        'status' => DeadlineStatus::Pending,
    ]);

    Livewire::test(DeadlineList::class)
        ->assertSee('overdue');
});

it('does not show deadlines from other businesses', function () {
    $otherBusiness = Business::factory()->create();
    $otherProject = LienProject::factory()->forBusiness($otherBusiness)->create([
        'name' => 'Other Business Project',
    ]);

    $documentType = LienDocumentType::where('slug', 'prelim_notice')->first();

    LienProjectDeadline::factory()->create([
        'business_id' => $otherBusiness->id,
        'project_id' => $otherProject->id,
        'document_type_id' => $documentType->id,
        'due_date' => today()->addDays(10),
        'status' => DeadlineStatus::Pending,
    ]);

    Livewire::test(DeadlineList::class)
        ->assertDontSee('Other Business Project');
});
