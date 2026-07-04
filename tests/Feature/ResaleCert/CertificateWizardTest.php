<?php

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Livewire\CertificateWizard;
use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Models\ResaleProfile;
use App\Domains\ResaleCert\Models\ResaleTaxRegistration;
use App\Domains\ResaleCert\Models\ResaleVendor;
use App\Domains\ResaleCert\Services\MinimumFormsService;
use App\Models\User;
use App\Models\UserSignature;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake(config('resale_cert.disk'));

    $this->user = User::factory()->create(['first_name' => 'Pat', 'last_name' => 'Owner']);
    $this->business = Business::create([
        'name' => 'Wizard Test Co',
        'legal_name' => 'Wizard Test Co LLC',
        'business_address' => [
            'line1' => '100 Congress Ave',
            'city' => 'Austin',
            'state' => 'TX',
            'zip' => '78701',
        ],
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);
    $this->business->setResponsiblePersonForUser($this->user->id, 'Pat Owner', 'Owner');

    subscribeToResaleCerts($this->business);
    ResaleProfile::factory()->create(['business_id' => $this->business->id]);
    ResaleTaxRegistration::factory()->homeState()->create([
        'business_id' => $this->business->id,
        'state_code' => 'TX',
        'tax_id' => '11122233344',
    ]);

    $this->vendor = ResaleVendor::factory()->create(['business_id' => $this->business->id]);

    UserSignature::create([
        'user_id' => $this->user->id,
        'image_path' => 'resale-certificates/signatures/test.png',
        'is_current' => true,
        'agreed_to_terms' => true,
        'agreed_at' => now(),
    ]);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

it('prefers SST over MTC over individual forms', function () {
    $profile = $this->business->resaleProfile;
    $profile->update(['mtc_enabled' => true]);

    // WA accepts MTC only (per seeder: accepts_sst=false, accepts_mtc=true);
    // OH accepts both (SST wins); TX accepts neither uniform.
    $result = app(MinimumFormsService::class)->calculateMinimumForms(['OH', 'WA', 'TX'], $profile->fresh());

    $minimumCodes = array_column($result['minimum'], 'state_code');

    expect($minimumCodes)->toContain('SST')
        ->toContain('MTC')
        ->toContain('TX');

    $sst = collect($result['minimum'])->firstWhere('state_code', 'SST');
    $mtc = collect($result['minimum'])->firstWhere('state_code', 'MTC');

    expect($sst['covers_states'])->toContain('OH')
        ->and($mtc['covers_states'])->toContain('WA')
        ->and($mtc['covers_states'])->not->toContain('OH');
});

it('skips MTC when the business has not opted in', function () {
    $result = app(MinimumFormsService::class)
        ->calculateMinimumForms(['WA'], $this->business->resaleProfile);

    $minimumCodes = array_column($result['minimum'], 'state_code');

    // WA accepts SST=false in old data? (WA accepts_mtc only) -> individual form
    expect($minimumCodes)->not->toContain('MTC');
});

it('generates certificates with frozen snapshots and PDFs', function () {
    Livewire::test(CertificateWizard::class)
        ->set('vendorId', (string) $this->vendor->id)
        ->set('selectedStates', ['TX'])
        ->call('continueToReview')
        ->assertHasNoErrors()
        ->assertSet('step', 2)
        ->call('generate')
        ->assertHasNoErrors()
        ->assertRedirect(route('resale-cert.certificates.index'));

    $certificate = ResaleCertificate::withoutGlobalScopes()->where('business_id', $this->business->id)->first();

    expect($certificate)->not->toBeNull()
        ->and($certificate->state_code)->toBe('TX')
        ->and($certificate->business_snapshot['legal_name'])->toBe('Wizard Test Co LLC')
        ->and($certificate->business_snapshot['tax_id'])->toBe('11122233344')
        ->and($certificate->business_snapshot['tax_id_source_state'])->toBe('TX')
        ->and($certificate->business_snapshot['signer_title'])->toBe('Owner')
        ->and($certificate->vendor_snapshot['legal_name'])->toBe($this->vendor->legal_name)
        ->and($certificate->expiration_date->format('Y-m-d'))->toBe(now()->endOfYear()->format('Y-m-d'))
        ->and($certificate->pdf_path)->not->toBeNull();

    Storage::disk(config('resale_cert.disk'))->assertExists($certificate->pdf_path);

    $bytes = Storage::disk(config('resale_cert.disk'))->get($certificate->pdf_path);
    expect(str_starts_with($bytes, '%PDF'))->toBeTrue();
});

it('uses the home-state tax id as fallback for out-of-state forms', function () {
    // CO accepts out-of-state ids; the business is only registered in TX.
    Livewire::test(CertificateWizard::class)
        ->set('vendorId', (string) $this->vendor->id)
        ->set('selectedStates', ['CO'])
        ->call('continueToReview')
        ->call('generate')
        ->assertHasNoErrors();

    $certificate = ResaleCertificate::withoutGlobalScopes()
        ->where('business_id', $this->business->id)
        ->where('state_code', 'CO')
        ->first();

    expect($certificate->business_snapshot['tax_id'])->toBe('11122233344')
        ->and($certificate->business_snapshot['tax_id_source_state'])->toBe('TX');
});

it('blocks generation when unchecking forms leaves states uncovered', function () {
    $component = Livewire::test(CertificateWizard::class)
        ->set('vendorId', (string) $this->vendor->id)
        ->set('selectedStates', ['TX'])
        ->call('continueToReview')
        ->set('checkedForms.TX', false)
        ->call('generate')
        ->assertHasErrors(['generate']);

    expect(ResaleCertificate::withoutGlobalScopes()->where('business_id', $this->business->id)->count())->toBe(0);
});

it('blocks generation without a signature', function () {
    UserSignature::where('user_id', $this->user->id)->delete();

    Livewire::test(CertificateWizard::class)
        ->set('vendorId', (string) $this->vendor->id)
        ->set('selectedStates', ['TX'])
        ->call('continueToReview')
        ->call('generate')
        ->assertHasErrors(['generate']);
});

it('rejects states that are not selectable for this business', function () {
    // CA does not accept out-of-state ids or uniform forms; business is
    // registered only in TX, so CA is locked.
    Livewire::test(CertificateWizard::class)
        ->set('vendorId', (string) $this->vendor->id)
        ->set('selectedStates', ['CA'])
        ->call('continueToReview')
        ->assertHasErrors(['selectedStates']);
});
