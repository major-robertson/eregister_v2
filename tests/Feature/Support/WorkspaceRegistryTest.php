<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use App\Support\Workspaces\Workspace;
use App\Support\Workspaces\WorkspaceRegistry;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::create([
        'name' => 'Registry Test Biz',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);
});

it('exposes the configured workspaces in order', function () {
    $registry = app(WorkspaceRegistry::class);

    $keys = array_keys($registry->all());

    expect($keys)->toContain('liens', 'sales_tax', 'formations');
});

it('returns hydrated Workspace value objects from find()', function () {
    $registry = app(WorkspaceRegistry::class);

    $sales = $registry->find('sales_tax');

    expect($sales)->toBeInstanceOf(Workspace::class)
        ->and($sales->slug)->toBe('sales-tax')
        ->and($sales->dashboardRoute)->toBe('sales-tax.dashboard')
        ->and($sales->formTypes)->toBe(['sales_tax_permit'])
        ->and($sales->primaryFormType())->toBe('sales_tax_permit')
        ->and($sales->badgeColor)->toBe('emerald')
        ->and($sales->enabled)->toBeTrue();
});

it('claimsFormType returns true for owned form types and false otherwise', function () {
    $registry = app(WorkspaceRegistry::class);

    $sales = $registry->find('sales_tax');
    $formations = $registry->find('formations');

    expect($sales->claimsFormType('sales_tax_permit'))->toBeTrue()
        ->and($sales->claimsFormType('llc'))->toBeFalse()
        ->and($formations->claimsFormType('llc'))->toBeTrue()
        ->and($formations->claimsFormType('sales_tax_permit'))->toBeFalse();
});

it('findByFormType returns the right workspace for each claimed form type', function () {
    $registry = app(WorkspaceRegistry::class);

    expect($registry->findByFormType('sales_tax_permit')?->key)->toBe('sales_tax');
    expect($registry->findByFormType('llc')?->key)->toBe('formations');
    expect($registry->findByFormType('unknown_type'))->toBeNull();
});

it('startRouteFor builds the URL with no param for sales-tax and a formType param for formations', function () {
    $registry = app(WorkspaceRegistry::class);

    expect($registry->find('sales_tax')->startRouteFor('sales_tax_permit'))
        ->toEndWith('/portal/sales-tax/registrations/start');

    expect($registry->find('formations')->startRouteFor('llc'))
        ->toEndWith('/portal/formations/start/llc');
});

it('startRouteFor returns null when the workspace does not claim the form type', function () {
    $registry = app(WorkspaceRegistry::class);

    expect($registry->find('sales_tax')->startRouteFor('llc'))->toBeNull();
    expect($registry->find('formations')->startRouteFor('sales_tax_permit'))->toBeNull();
    expect($registry->find('liens')->startRouteFor('llc'))->toBeNull();
});

it('applicationRouteFor refuses to build URLs for unclaimed form types', function () {
    $registry = app(WorkspaceRegistry::class);

    $llcApp = new \App\Domains\Forms\Models\FormApplication([
        'form_type' => 'llc',
    ]);
    $llcApp->id = 99;
    $llcApp->exists = true;

    // Sales tax workspace must refuse to build a URL for an LLC application.
    expect($registry->find('sales_tax')->applicationRouteFor($llcApp))->toBeNull();

    // Formations workspace builds it correctly.
    expect($registry->find('formations')->applicationRouteFor($llcApp))
        ->toContain('/portal/formations/applications/99');
});

it('applicationRouteFor merges extra params into the route as query string', function () {
    $registry = app(WorkspaceRegistry::class);

    $stApp = new \App\Domains\Forms\Models\FormApplication([
        'form_type' => 'sales_tax_permit',
    ]);
    $stApp->id = 42;
    $stApp->exists = true;

    expect($registry->find('sales_tax')->applicationRouteFor($stApp, ['checkout' => 'success']))
        ->toContain('/portal/sales-tax/registrations/42')
        ->toContain('checkout=success');
});

it('returns null for an unknown workspace key', function () {
    expect(app(WorkspaceRegistry::class)->find('nope'))->toBeNull();
});

it('filters disabled workspaces out of enabledFor()', function () {
    config()->set('workspaces.sales_tax.enabled', false);

    // Reset the singleton so the registry re-reads the new config.
    app()->forgetInstance(WorkspaceRegistry::class);

    $enabled = app(WorkspaceRegistry::class)->enabledFor($this->business);

    expect($enabled)->toHaveKey('liens')
        ->and($enabled)->not->toHaveKey('sales_tax');
});

it('reports cardState as Get Started when the business has no workspace data', function () {
    $registry = app(WorkspaceRegistry::class);

    $liens = $registry->find('liens');
    $sales = $registry->find('sales_tax');

    expect($liens->cardState($this->business)->ctaLabel)->toBe('Get Started')
        ->and($liens->cardState($this->business)->hasData)->toBeFalse()
        ->and($sales->cardState($this->business)->ctaLabel)->toBe('Get Started')
        ->and($sales->cardState($this->business)->hasData)->toBeFalse();
});

it('flips cardState to Open once data exists, scoped per workspace', function () {
    LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Test Project',
    ]);

    $registry = app(WorkspaceRegistry::class);

    $liens = $registry->find('liens');
    $sales = $registry->find('sales_tax');

    expect($liens->cardState($this->business)->ctaLabel)->toBe('Open')
        ->and($liens->cardState($this->business)->hasData)->toBeTrue()
        // Lien data must NOT light up the Sales Tax card.
        ->and($sales->cardState($this->business)->ctaLabel)->toBe('Get Started')
        ->and($sales->cardState($this->business)->hasData)->toBeFalse();

    FormApplication::create([
        'business_id' => $this->business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => ['CA'],
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $this->user->id,
    ]);

    $sales = app(WorkspaceRegistry::class)->find('sales_tax');

    expect($sales->cardState($this->business)->ctaLabel)->toBe('Open')
        ->and($sales->cardState($this->business)->hasData)->toBeTrue();
});

it('does not consider lien data for the sales tax workspace and vice versa', function () {
    $otherBusiness = Business::create([
        'name' => 'Isolated Biz',
        'onboarding_completed_at' => now(),
    ]);

    FormApplication::create([
        'business_id' => $otherBusiness->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => ['CA'],
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $this->user->id,
    ]);

    $sales = app(WorkspaceRegistry::class)->find('sales_tax');

    // The current business should still see Get Started despite another
    // business having a sales tax registration.
    expect($sales->cardState($this->business)->hasData)->toBeFalse();
});
