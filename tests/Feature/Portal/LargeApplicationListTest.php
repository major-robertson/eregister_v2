<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/*
 * Regression coverage for the Aug 2026 customer 500s: an application
 * covering ~all states carries a multi-MB definition_snapshot, and MySQL
 * refuses to filesort rows that exceed sort_buffer_size (error 1038
 * "Out of sort memory"). Every list surface that sorts form_applications
 * must go through FormApplication::scopeForList() so the heavy JSON
 * columns stay out of the sort.
 */

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::create([
        'name' => 'Everywhere Seller Inc',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);

    // The selector's NOMAD states are excluded; everything else is fair game.
    $states = array_values(array_diff(array_keys(config('states')), ['NH', 'OR', 'MT', 'DE']));

    // The padding stands in for a real merged 46-state snapshot (~2 MB in
    // production) while staying quick to insert: large enough to overflow
    // a default 256 KB sort_buffer_size if it ever enters a filesort.
    $this->application = FormApplication::create([
        'business_id' => $this->business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => $states,
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'definition_snapshot' => ['padding' => str_repeat('x', 500_000)],
        'created_by_user_id' => $this->user->id,
    ]);

    foreach (array_slice($states, 0, 3) as $state) {
        FormApplicationState::create([
            'form_application_id' => $this->application->id,
            'state_code' => $state,
            'status' => 'pending',
            'data' => [],
        ]);
    }

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

it('renders the portal dashboard with an all-states draft', function () {
    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('46 state(s)');
});

it('renders the sales tax dashboard with an all-states draft', function () {
    $this->get(route('sales-tax.dashboard'))
        ->assertSuccessful()
        ->assertSee('46 states');
});

it('renders the registration start page when an all-states draft exists', function () {
    $this->get(route('sales-tax.registrations.start'))
        ->assertSuccessful();
});

it('never sorts form_applications rows with a bare select *', function () {
    $sortedStarQueries = [];

    DB::listen(function ($query) use (&$sortedStarQueries) {
        $sql = strtolower($query->sql);

        if (str_contains($sql, 'from `form_applications`')
            && str_contains($sql, 'order by')
            && str_starts_with($sql, 'select *')) {
            $sortedStarQueries[] = $query->sql;
        }
    });

    $this->get(route('dashboard'))->assertSuccessful();
    $this->get(route('sales-tax.dashboard'))->assertSuccessful();
    $this->get(route('sales-tax.registrations.start'))->assertSuccessful();

    expect($sortedStarQueries)->toBe([]);
});
