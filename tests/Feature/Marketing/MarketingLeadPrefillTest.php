<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Livewire\LienOnboarding;
use App\Domains\Lien\Livewire\ProjectForm;
use App\Domains\Marketing\Enums\CampaignStatus;
use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingLead;
use App\Domains\Marketing\Models\MarketingTrackingLink;
use App\Http\Middleware\ActivateMarketingLeadContext;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->campaign = MarketingCampaign::create([
        'name' => 'Liens Campaign',
        'landing_key' => 'liens',
        'status' => CampaignStatus::Active,
    ]);

    $this->lead = MarketingLead::create([
        'business_name' => 'Prefill Contracting LLC',
        'contact_name' => 'John Doe',
        'phone' => '5025551234',
        'mailing_address' => '100 Main St',
        'mailing_address_2' => 'Suite 200',
        'mailing_city' => 'Louisville',
        'mailing_state' => 'KY',
        'mailing_zip' => '40258',
        'property_address' => '456 Oak Street',
        'property_city' => 'Lexington',
        'property_state' => 'KY',
        'property_zip' => '40507',
        'permit_or_external_id' => 'PERMIT-001',
    ]);

    $this->vanityLink = MarketingTrackingLink::where('token', $this->lead->slug)->first();
    $this->vanityLink->update(['campaign_id' => $this->campaign->id]);
});

// ─── Cookie Tests ──────────────────────────────────────────────────────────

it('sets lead_ref cookie on marketing landing page', function () {
    $response = $this->get("/go/{$this->lead->slug}");

    $response->assertStatus(200);
    $response->assertCookie('lead_ref', $this->lead->public_id);
});

it('sets lead_ref cookie with correct flags', function () {
    $response = $this->get("/go/{$this->lead->slug}");

    $cookie = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === 'lead_ref');

    expect($cookie)->not->toBeNull();
    expect($cookie->isHttpOnly())->toBeTrue();
    expect($cookie->getSameSite())->toBe('lax');
});

it('appends lead query param to CTA links when lead is present', function () {
    $response = $this->get("/go/{$this->lead->slug}");

    $response->assertStatus(200);
    $response->assertSee('lead='.$this->lead->public_id);
});

// ─── Middleware: ActivateMarketingLeadContext ──────────────────────────────

it('activates lead context from query param', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $business = Business::factory()->create();
    $business->users()->attach($user, ['role' => 'owner']);
    session(['current_business_id' => $business->id]);

    $this->get(route('portal.select-business', ['lead' => $this->lead->public_id]));

    expect(session('active_marketing_lead_id'))->toBe($this->lead->id);
    expect(session('active_marketing_lead_set_at'))->not->toBeNull();
});

it('activates lead context from cookie when no query param', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $business = Business::factory()->create();
    $business->users()->attach($user, ['role' => 'owner']);
    session(['current_business_id' => $business->id]);

    $this->withCookie('lead_ref', $this->lead->public_id)
        ->get(route('portal.select-business'));

    expect(session('active_marketing_lead_id'))->toBe($this->lead->id);
});

it('does not re-resolve when session is already set and not expired', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $business = Business::factory()->create();
    $business->users()->attach($user, ['role' => 'owner']);
    session(['current_business_id' => $business->id]);

    // Set session manually with a valid timestamp
    session([
        'active_marketing_lead_id' => $this->lead->id,
        'active_marketing_lead_set_at' => now()->toISOString(),
    ]);

    // Create a second lead
    $otherLead = MarketingLead::create([
        'business_name' => 'Other Company',
        'mailing_address' => '999 Other St',
        'mailing_city' => 'Other City',
        'mailing_state' => 'OH',
        'mailing_zip' => '44444',
    ]);

    // Visit with a different lead's query param -- should NOT overwrite
    $this->get(route('portal.select-business', ['lead' => $otherLead->public_id]));

    expect(session('active_marketing_lead_id'))->toBe($this->lead->id);
});

it('clears and re-resolves when session timestamp is older than 14 days', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $business = Business::factory()->create();
    $business->users()->attach($user, ['role' => 'owner']);
    session(['current_business_id' => $business->id]);

    $otherLead = MarketingLead::create([
        'business_name' => 'Fresh Company',
        'mailing_address' => '888 Fresh St',
        'mailing_city' => 'Fresh City',
        'mailing_state' => 'CA',
        'mailing_zip' => '90210',
    ]);

    // Set session with an expired timestamp (15 days ago)
    session([
        'active_marketing_lead_id' => $this->lead->id,
        'active_marketing_lead_set_at' => now()->subDays(15)->toISOString(),
    ]);

    // Visit with a different lead's query param -- should re-resolve because expired
    $this->get(route('portal.select-business', ['lead' => $otherLead->public_id]));

    expect(session('active_marketing_lead_id'))->toBe($otherLead->id);
});

it('does not activate lead context on non-onboarding routes', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $business = Business::factory()->create();
    $business->users()->attach($user, ['role' => 'owner']);
    $business->completeOnboarding();
    $business->completeLienOnboarding();
    session(['current_business_id' => $business->id]);

    // Visit the dashboard with a lead query param -- middleware should NOT be applied
    $this->get(route('dashboard', ['lead' => $this->lead->public_id]));

    expect(session('active_marketing_lead_id'))->toBeNull();
});

// ─── Attribution Persistence ──────────────────────────────────────────────

it('persists attributed_marketing_lead_id on user registration', function () {
    // Set the session as middleware would
    session([
        'active_marketing_lead_id' => $this->lead->id,
        'active_marketing_lead_set_at' => now()->toISOString(),
    ]);

    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test-attribution@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $user = User::where('email', 'test-attribution@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->attributed_marketing_lead_id)->toBe($this->lead->id);
    expect($user->attributed_at)->not->toBeNull();
});

it('does not set attribution when no active lead context', function () {
    $response = $this->post('/register', [
        'first_name' => 'No',
        'last_name' => 'Lead',
        'email' => 'no-lead@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $user = User::where('email', 'no-lead@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->attributed_marketing_lead_id)->toBeNull();
    expect($user->attributed_at)->toBeNull();
});

it('does not clear active_marketing_lead_id on registration', function () {
    session([
        'active_marketing_lead_id' => $this->lead->id,
        'active_marketing_lead_set_at' => now()->toISOString(),
    ]);

    $this->post('/register', [
        'first_name' => 'Keep',
        'last_name' => 'Session',
        'email' => 'keep-session@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    expect(session('active_marketing_lead_id'))->toBe($this->lead->id);
});

// ─── BusinessSwitcher Pre-fill ────────────────────────────────────────────

it('pre-fills business name in BusinessSwitcher when user has no businesses', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    session([
        'active_marketing_lead_id' => $this->lead->id,
        'active_marketing_lead_set_at' => now()->toISOString(),
    ]);

    Livewire::test(\App\Domains\Business\Livewire\BusinessSwitcher::class)
        ->assertSet('newBusinessName', 'Prefill Contracting LLC');
});

it('does not pre-fill business name when user already has businesses', function () {
    $user = User::factory()->create();
    $business = Business::factory()->create();
    $business->users()->attach($user, ['role' => 'owner']);
    $this->actingAs($user);

    session([
        'active_marketing_lead_id' => $this->lead->id,
        'active_marketing_lead_set_at' => now()->toISOString(),
    ]);

    Livewire::test(\App\Domains\Business\Livewire\BusinessSwitcher::class)
        ->assertSet('newBusinessName', '');
});

// ─── OnboardingWizard Pre-fill ────────────────────────────────────────────

it('pre-fills business address in OnboardingWizard from marketing lead', function () {
    $user = User::factory()->create();
    $business = Business::factory()->create([
        'business_address' => null,
    ]);
    $business->users()->attach($user, ['role' => 'owner']);
    $this->actingAs($user);
    session(['current_business_id' => $business->id]);

    session([
        'active_marketing_lead_id' => $this->lead->id,
        'active_marketing_lead_set_at' => now()->toISOString(),
    ]);

    Livewire::test(\App\Domains\Business\Livewire\OnboardingWizard::class)
        ->assertSet('businessAddress.line1', '100 Main St')
        ->assertSet('businessAddress.line2', 'Suite 200')
        ->assertSet('businessAddress.city', 'Louisville')
        ->assertSet('businessAddress.state', 'KY')
        ->assertSet('businessAddress.zip', '40258');
});

it('does not overwrite existing business address in OnboardingWizard', function () {
    $user = User::factory()->create();
    $business = Business::factory()->create([
        'business_address' => [
            'line1' => '999 Existing St',
            'city' => 'Existing City',
            'state' => 'OH',
            'zip' => '44444',
        ],
    ]);
    $business->users()->attach($user, ['role' => 'owner']);
    $this->actingAs($user);
    session(['current_business_id' => $business->id]);

    session([
        'active_marketing_lead_id' => $this->lead->id,
        'active_marketing_lead_set_at' => now()->toISOString(),
    ]);

    Livewire::test(\App\Domains\Business\Livewire\OnboardingWizard::class)
        ->assertSet('businessAddress.line1', '999 Existing St')
        ->assertSet('businessAddress.city', 'Existing City');
});

// ─── LienOnboarding Pre-fill ──────────────────────────────────────────────

it('pre-fills phone in LienOnboarding from marketing lead', function () {
    $user = User::factory()->create();
    $business = Business::factory()->create();
    $business->users()->attach($user, ['role' => 'owner']);
    $business->completeOnboarding();
    $this->actingAs($user);
    session(['current_business_id' => $business->id]);

    session([
        'active_marketing_lead_id' => $this->lead->id,
        'active_marketing_lead_set_at' => now()->toISOString(),
    ]);

    Livewire::test(LienOnboarding::class)
        ->assertSet('phone', '5025551234');
});

it('does not overwrite existing phone in LienOnboarding', function () {
    $user = User::factory()->create();
    $business = Business::factory()->create([
        'phone' => '1112223333',
    ]);
    $business->users()->attach($user, ['role' => 'owner']);
    $business->completeOnboarding();
    $this->actingAs($user);
    session(['current_business_id' => $business->id]);

    session([
        'active_marketing_lead_id' => $this->lead->id,
        'active_marketing_lead_set_at' => now()->toISOString(),
    ]);

    Livewire::test(LienOnboarding::class)
        ->assertSet('phone', '1112223333');
});

// ─── ProjectForm Pre-fill ─────────────────────────────────────────────────

it('pre-fills jobsite address in ProjectForm from marketing lead', function () {
    $user = User::factory()->create();
    $business = Business::factory()->create([
        'lien_onboarding_completed_at' => now(),
    ]);
    $business->users()->attach($user, ['role' => 'owner']);
    $this->actingAs($user);
    session(['current_business_id' => $business->id]);

    session([
        'active_marketing_lead_id' => $this->lead->id,
        'active_marketing_lead_set_at' => now()->toISOString(),
    ]);

    Livewire::test(ProjectForm::class)
        ->assertSet('jobsite_address1', '456 Oak Street')
        ->assertSet('jobsite_city', 'Lexington')
        ->assertSet('jobsite_state', 'KY')
        ->assertSet('jobsite_zip', '40507')
        ->assertSet('job_number', 'PERMIT-001')
        ->assertSet('name', '456 Oak Street, Lexington');
});

it('does not pre-fill ProjectForm when editing an existing project', function () {
    $user = User::factory()->create();
    $business = Business::factory()->create([
        'lien_onboarding_completed_at' => now(),
    ]);
    $business->users()->attach($user, ['role' => 'owner']);
    $this->actingAs($user);
    session(['current_business_id' => $business->id]);

    session([
        'active_marketing_lead_id' => $this->lead->id,
        'active_marketing_lead_set_at' => now()->toISOString(),
    ]);

    $project = \App\Domains\Lien\Models\LienProject::factory()->forBusiness($business)->create([
        'name' => 'Existing Project',
        'jobsite_address1' => '789 Existing Ave',
        'jobsite_city' => 'Existing City',
        'jobsite_state' => 'OH',
    ]);

    Livewire::test(ProjectForm::class, ['project' => $project])
        ->assertSet('jobsite_address1', '789 Existing Ave')
        ->assertSet('jobsite_city', 'Existing City')
        ->assertSet('name', 'Existing Project');
});

// ─── Critical Regression: Returning users are NOT pre-filled ──────────────

it('does not pre-fill ProjectForm for returning user with attributed_marketing_lead_id but no active session', function () {
    $user = User::factory()->create([
        'attributed_marketing_lead_id' => $this->lead->id,
        'attributed_at' => now()->subMonths(3),
    ]);
    $business = Business::factory()->create([
        'lien_onboarding_completed_at' => now(),
    ]);
    $business->users()->attach($user, ['role' => 'owner']);
    $this->actingAs($user);
    session(['current_business_id' => $business->id]);

    // No active_marketing_lead_id in session -- returning user months later

    Livewire::test(ProjectForm::class)
        ->assertSet('jobsite_address1', null)
        ->assertSet('jobsite_city', null)
        ->assertSet('job_number', null)
        ->assertSet('name', '');
});

// ─── Context Cleared After First Project ──────────────────────────────────

it('clears active lead session keys and sets completion flag after first project is created', function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $user = User::factory()->create();
    $business = Business::factory()->create([
        'lien_onboarding_completed_at' => now(),
    ]);
    $business->users()->attach($user, ['role' => 'owner']);
    $this->actingAs($user);
    session(['current_business_id' => $business->id]);

    session([
        'active_marketing_lead_id' => $this->lead->id,
        'active_marketing_lead_set_at' => now()->toISOString(),
    ]);

    // Create project through the form
    Livewire::test(ProjectForm::class)
        ->assertSet('jobsite_address1', '456 Oak Street')
        ->set('name', 'Test Project')
        ->set('claimant_type', 'subcontractor')
        ->call('nextStep');

    // After first step, a project is created and context should be cleared
    expect(session('active_marketing_lead_id'))->toBeNull();
    expect(session('active_marketing_lead_set_at'))->toBeNull();
    expect(session('marketing_lead_prefill_completed'))->toBeTrue();
});

it('does not pre-fill second project after context is cleared', function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $user = User::factory()->create();
    $business = Business::factory()->create([
        'lien_onboarding_completed_at' => now(),
    ]);
    $business->users()->attach($user, ['role' => 'owner']);
    $this->actingAs($user);
    session(['current_business_id' => $business->id]);

    session([
        'active_marketing_lead_id' => $this->lead->id,
        'active_marketing_lead_set_at' => now()->toISOString(),
    ]);

    // Create first project
    Livewire::test(ProjectForm::class)
        ->set('name', 'First Project')
        ->set('claimant_type', 'subcontractor')
        ->call('nextStep');

    // Session should be cleared now. Second project should have empty fields.
    Livewire::test(ProjectForm::class)
        ->assertSet('jobsite_address1', null)
        ->assertSet('jobsite_city', null)
        ->assertSet('job_number', null)
        ->assertSet('name', '');
});

it('does not pre-fill second project even when lead_ref cookie is still present', function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $user = User::factory()->create();
    $business = Business::factory()->create([
        'lien_onboarding_completed_at' => now(),
    ]);
    $business->users()->attach($user, ['role' => 'owner']);
    $this->actingAs($user);
    session(['current_business_id' => $business->id]);

    session([
        'active_marketing_lead_id' => $this->lead->id,
        'active_marketing_lead_set_at' => now()->toISOString(),
    ]);

    // Create first project (clears session, sets flag, queues cookie forget)
    Livewire::test(ProjectForm::class)
        ->set('name', 'First Project')
        ->set('claimant_type', 'subcontractor')
        ->call('nextStep');

    // Simulate the cookie still being present (e.g. new browser session,
    // or the queued forget hasn't taken effect yet). Hit the route with
    // the marketing.lead middleware to attempt re-activation from cookie.
    $this->withCookie('lead_ref', $this->lead->public_id)
        ->get(route('lien.projects.create'));

    // Middleware should NOT re-activate because the completion flag is set
    expect(session('active_marketing_lead_id'))->toBeNull();

    // And even if it somehow did, the exists() guard prevents pre-fill
    Livewire::test(ProjectForm::class)
        ->assertSet('jobsite_address1', null)
        ->assertSet('jobsite_city', null)
        ->assertSet('job_number', null)
        ->assertSet('name', '');
});

it('middleware does not re-activate from cookie when prefill completion flag is set', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $business = Business::factory()->create();
    $business->users()->attach($user, ['role' => 'owner']);
    session(['current_business_id' => $business->id]);

    // Simulate post-onboarding state: completion flag is set, no active lead
    session(['marketing_lead_prefill_completed' => true]);

    // Visit with a cookie -- middleware should skip re-activation
    $this->withCookie('lead_ref', $this->lead->public_id)
        ->get(route('portal.select-business'));

    expect(session('active_marketing_lead_id'))->toBeNull();
});
