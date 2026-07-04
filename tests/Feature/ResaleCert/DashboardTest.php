<?php

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Models\ResaleProfile;
use App\Domains\ResaleCert\Models\ResaleVendor;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::create([
        'name' => 'Resale Test Co',
        'legal_name' => 'Resale Test Co LLC',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);
});

it('redirects guests to login', function () {
    $this->get(route('resale-cert.dashboard'))->assertRedirect(route('login'));
});

it('redirects authenticated users without a business to the selector', function () {
    $loneUser = User::factory()->create();

    $this->actingAs($loneUser)
        ->get(route('resale-cert.dashboard'))
        ->assertRedirect(route('portal.select-business'));
});

it('shows the pricing card when the business has no subscription', function () {
    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    $this->get(route('resale-cert.dashboard'))
        ->assertSuccessful()
        ->assertSee('$297')
        ->assertSee('Subscribe Now');
});

it('shows the onboarding prompt when subscribed but profile incomplete', function () {
    subscribeToResaleCerts($this->business);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    $this->get(route('resale-cert.dashboard'))
        ->assertSuccessful()
        ->assertSee('Set up your resale profile')
        ->assertSee('Get Started');
});

it('shows stats and lists once subscribed with a complete profile', function () {
    subscribeToResaleCerts($this->business);
    ResaleProfile::factory()->create(['business_id' => $this->business->id]);

    $vendor = ResaleVendor::factory()->create(['business_id' => $this->business->id]);
    ResaleCertificate::factory()->create([
        'business_id' => $this->business->id,
        'resale_vendor_id' => $vendor->id,
        'state_code' => 'TX',
    ]);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    $this->get(route('resale-cert.dashboard'))
        ->assertSuccessful()
        ->assertSee('Generate Certificate')
        ->assertSee('Recent Certificates')
        ->assertSee('Texas');
});

it('does not leak certificates from other businesses', function () {
    subscribeToResaleCerts($this->business);
    ResaleProfile::factory()->create(['business_id' => $this->business->id]);

    $otherBusiness = Business::create(['name' => 'Other Co', 'onboarding_completed_at' => now()]);
    $otherVendor = ResaleVendor::factory()->create([
        'business_id' => $otherBusiness->id,
        'legal_name' => 'Secret Other Vendor',
    ]);
    ResaleCertificate::factory()->create([
        'business_id' => $otherBusiness->id,
        'resale_vendor_id' => $otherVendor->id,
        'state_code' => 'CA',
    ]);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    $this->get(route('resale-cert.dashboard'))
        ->assertSuccessful()
        ->assertDontSee('Secret Other Vendor');
});

it('renders inside the unified portal shell', function () {
    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    $this->get(route('resale-cert.dashboard'))
        ->assertSuccessful()
        ->assertSee('Resale Certificates')
        ->assertSee('Switch business');
});
