<?php

use App\Domains\Lien\Waivers\WaiverStateRegistry;

it('renders the lien waiver landing page with pricing and free-tier copy', function () {
    $this->get('/liens/lien-waivers')
        ->assertSuccessful()
        ->assertSee('Lien Waiver Generator')
        // Pro pricing pulled from config/lien_waivers.php.
        ->assertSee('$99')
        ->assertSee('$990')
        // Free-tier allowance pulled from config.
        ->assertSee('3 waivers per month')
        // The 50-state directory grid is present.
        ->assertSee('Lien waiver forms by state')
        ->assertSee('Alabama')
        ->assertSee('Wyoming');
});

it('renders the dedicated lien waiver pricing page and cross-links lien filing pricing', function () {
    $this->get('/liens/lien-waivers/pricing')
        ->assertSuccessful()
        ->assertSee('Lien Waiver Pricing')
        ->assertSee('$99')
        ->assertSee('$990')
        // Cross-reference to the lien filing pricing page.
        ->assertSee('See lien filing pricing')
        ->assertSee(route('liens.pricing'));
});

it('cross-links lien waiver pricing from the lien filing pricing page', function () {
    $this->get('/liens/pricing')
        ->assertSuccessful()
        ->assertSee('Sending or collecting lien waivers?')
        ->assertSee(route('liens.lien-waivers.pricing'));
});

it('renders the Texas state page with its statutory mention', function () {
    $this->get('/liens/lien-waivers/tx')
        ->assertSuccessful()
        ->assertSee('Texas')
        ->assertSee('Tex. Prop. Code')
        ->assertSee('Conditional Waiver and Release on Progress Payment');
});

it('404s unknown state codes and full state names', function () {
    $this->get('/liens/lien-waivers/zz')->assertNotFound();
    $this->get('/liens/lien-waivers/texas')->assertNotFound();
});

it('301-redirects non-lowercase state codes to the canonical URL', function () {
    $this->get('/liens/lien-waivers/TX')
        ->assertStatus(301)
        ->assertRedirect(route('liens.lien-waivers.state', ['state' => 'tx']));
});

it('renders the advisory note for a nonstatutory state', function () {
    // North Carolina has no statutory form but carries an advance-waiver advisory.
    expect(WaiverStateRegistry::for('NC')['compliance_standard'])->toBe('generic');

    $this->get('/liens/lien-waivers/nc')
        ->assertSuccessful()
        ->assertSee('North Carolina')
        ->assertSee('Advance waivers')
        ->assertSee('N.C. Gen. Stat.');
});

it('surfaces Georgia execution rules: witness, no e-sign, and the 90-day rule', function () {
    $this->get('/liens/lien-waivers/ga')
        ->assertSuccessful()
        ->assertSee('90-day deemed-effective rule')
        ->assertSee('Affidavit of Nonpayment')
        // esign_allowed=false renders the state's explanation instead of the e-sign pitch.
        ->assertSee('have the claimant sign before a witness');
});

it('renders every one of the 50 state pages successfully', function () {
    foreach (array_keys(WaiverStateRegistry::STATE_NAMES) as $code) {
        $this->get('/liens/lien-waivers/'.strtolower($code))->assertSuccessful();
    }
});
