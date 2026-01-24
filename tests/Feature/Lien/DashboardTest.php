<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\Dashboard;
use App\Domains\Lien\Models\LienFiling;
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

it('can view the dashboard', function () {
    $this->get(route('lien.dashboard'))
        ->assertSuccessful()
        ->assertSee('Lien Dashboard');
});

it('shows continue where you left off for draft filings', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Test Project Alpha',
    ]);

    $filing = LienFiling::factory()->forProject($project)->draft()->create([
        'updated_at' => now(),
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('Continue where you left off')
        ->assertSee('Test Project Alpha');
});

it('hides continue block when no draft filings exist', function () {
    Livewire::test(Dashboard::class)
        ->assertDontSee('Continue where you left off');
});

it('shows overdue deadlines count', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create();

    // Create an overdue deadline
    LienProjectDeadline::factory()->create([
        'business_id' => $this->business->id,
        'project_id' => $project->id,
        'due_date' => today()->subDays(5),
        'status' => DeadlineStatus::Pending,
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('Overdue Deadlines');
});

it('shows upcoming deadlines within 7 days', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Upcoming Deadline Project',
    ]);

    // Create an upcoming deadline
    LienProjectDeadline::factory()->create([
        'business_id' => $this->business->id,
        'project_id' => $project->id,
        'due_date' => today()->addDays(3),
        'status' => DeadlineStatus::Pending,
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('Due in 7 Days');
});

it('shows pending payments count', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Payment Pending Project',
    ]);

    LienFiling::factory()->forProject($project)->create([
        'status' => FilingStatus::AwaitingPayment,
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('Pending Payments');
});

it('shows missing information count', function () {
    // Create project with missing dates
    LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Incomplete Project',
        'first_furnish_date' => null,
        'last_furnish_date' => null,
        'jobsite_county_google' => null,
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('Missing Info');
});

it('shows quick action buttons', function () {
    Livewire::test(Dashboard::class)
        ->assertSee('New Project')
        ->assertSee('File a Document');
});

it('shows recent activity section', function () {
    Livewire::test(Dashboard::class)
        ->assertSee('Recent Activity');
});

it('does not show data from other businesses', function () {
    // Create another business with projects
    $otherBusiness = Business::factory()->create();
    $otherProject = LienProject::factory()->forBusiness($otherBusiness)->create([
        'name' => 'Other Business Project',
    ]);

    LienFiling::factory()->forProject($otherProject)->draft()->create();

    // Should not see the other business's data
    Livewire::test(Dashboard::class)
        ->assertDontSee('Other Business Project');
});
