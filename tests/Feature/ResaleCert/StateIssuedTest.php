<?php

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Livewire\CertificateWizard;
use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Models\ResaleProfile;
use App\Domains\ResaleCert\Models\ResaleTaxRegistration;
use App\Domains\ResaleCert\Models\ResaleVendor;
use App\Domains\ResaleCert\Pdf\StateCertificateFactory;
use App\Domains\ResaleCert\Services\MinimumFormsService;
use App\Models\User;
use App\Models\UserSignature;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/*
 * States that issue the resale certificate themselves (config
 * 'state_issued'): FL, LA, MS, DC have no generator; ME, NM, WA lock the
 * state for buyers registered there; TN stays selectable with a note.
 */

beforeEach(function () {
    Storage::fake(config('resale_cert.disk'));

    $this->user = User::factory()->create(['first_name' => 'Pat', 'last_name' => 'Owner']);
    $this->business = Business::create([
        'name' => 'State Issued Co',
        'legal_name' => 'State Issued Co LLC',
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
    ResaleProfile::factory()->create(['business_id' => $this->business->id, 'mtc_enabled' => false]);
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

    $this->registerIn = function (string $state, string $taxId = '55566677788'): void {
        ResaleTaxRegistration::factory()->create([
            'business_id' => $this->business->id,
            'state_code' => $state,
            'tax_id' => $taxId,
        ]);
    };

    $this->option = fn ($component, string $code) => collect($component->instance()->stateOptions)->firstWhere('code', $code);
});

it('locks Florida for a business registered there, with the state guidance and link', function () {
    ($this->registerIn)('FL');

    $component = Livewire::test(CertificateWizard::class);
    $florida = ($this->option)($component, 'FL');
    $guidance = config('resale_cert.states.FL.state_issued.guidance');

    expect($florida['selectable'])->toBeFalse()
        ->and($florida['registered'])->toBeTrue()
        ->and($florida['reason'])->toBe($guidance)
        ->and($florida['guidance'])->toBe($guidance);

    $component->assertSee($guidance)
        ->assertSee('https://floridarevenue.com/taxes/printcertificate')
        ->set('vendorId', (string) $this->vendor->id)
        ->set('selectedStates', ['FL'])
        ->call('continueToReview')
        ->assertHasErrors(['selectedStates'])
        ->assertSet('step', 1);
});

it('locks Maine for a business registered there', function () {
    ($this->registerIn)('ME');

    $component = Livewire::test(CertificateWizard::class);
    $maine = ($this->option)($component, 'ME');

    expect($maine['selectable'])->toBeFalse()
        ->and($maine['reason'])->toBe(config('resale_cert.states.ME.state_issued.guidance'));

    $component->assertSee(config('resale_cert.states.ME.state_issued.guidance'))
        ->set('vendorId', (string) $this->vendor->id)
        ->set('selectedStates', ['ME'])
        ->call('continueToReview')
        ->assertHasErrors(['selectedStates']);
});

it('lets a buyer registered only in Texas cover Maine with the MTC form', function () {
    $this->business->resaleProfile->update(['mtc_enabled' => true]);

    $component = Livewire::test(CertificateWizard::class);

    expect(($this->option)($component, 'ME')['selectable'])->toBeTrue()
        ->and(($this->option)($component, 'ME')['guidance'])->toBeNull();

    $component->set('vendorId', (string) $this->vendor->id)
        ->set('selectedStates', ['ME'])
        ->call('continueToReview')
        ->assertHasNoErrors()
        ->assertSet('step', 2);

    $minimum = $component->get('minimumForms');

    expect($minimum)->toHaveCount(1)
        ->and($minimum[0]['state_code'])->toBe('MTC')
        ->and($minimum[0]['covers_states'])->toBe(['ME']);
});

it('keeps Tennessee selectable for a registered buyer and generates it on the SST form', function () {
    ($this->registerIn)('TN', '1000123456');

    $component = Livewire::test(CertificateWizard::class);
    $tennessee = ($this->option)($component, 'TN');

    expect($tennessee['selectable'])->toBeTrue()
        ->and($tennessee['guidance'])->toBe(config('resale_cert.states.TN.state_issued.guidance'))
        ->and(app(StateCertificateFactory::class)->getTemplatePath('TN'))->toEndWith('/sst.pdf');

    $component->assertSee(config('resale_cert.states.TN.state_issued.guidance'))
        ->assertSee('https://tntap.tn.gov/eservices/')
        ->set('vendorId', (string) $this->vendor->id)
        ->set('selectedStates', ['TN'])
        ->call('continueToReview')
        ->assertHasNoErrors()
        ->assertSet('step', 2)
        // Generate the Tennessee form itself rather than the SST uniform one.
        ->set('checkedForms.SST', false)
        ->set('checkedForms.TN', true)
        ->call('generate')
        ->assertHasNoErrors()
        ->assertRedirect(route('resale-cert.certificates.index'));

    $certificate = ResaleCertificate::withoutGlobalScopes()->where('business_id', $this->business->id)->sole();

    expect($certificate->state_code)->toBe('TN')
        ->and($certificate->business_snapshot['tax_id'])->toBe('1000123456')
        ->and($certificate->pdf_path)->not->toBeNull();

    $bytes = Storage::disk(config('resale_cert.disk'))->get($certificate->pdf_path);
    expect(str_starts_with($bytes, '%PDF'))->toBeTrue();
});

it('never offers an individual form for a state without a generator', function () {
    $states = ['FL', 'LA', 'MS', 'DC'];
    $service = app(MinimumFormsService::class);

    foreach ([false, true] as $mtcEnabled) {
        $this->business->resaleProfile->update(['mtc_enabled' => $mtcEnabled]);
        $result = $service->calculateMinimumForms($states, $this->business->resaleProfile->fresh());

        $individual = collect([...$result['minimum'], ...$result['optional']])
            ->where('type', 'individual')
            ->pluck('state_code')
            ->all();

        expect(array_intersect($individual, $states))->toBe([]);
    }

    // Without MTC only the SST form (MS) covers anything; FL, LA, DC stay uncovered.
    $this->business->resaleProfile->update(['mtc_enabled' => false]);
    $result = $service->calculateMinimumForms($states, $this->business->resaleProfile->fresh());
    $covered = collect($result['minimum'])->pluck('covers_states')->flatten()->all();

    expect($covered)->toBe(['MS']);
});

it('hides Louisiana from an unregistered buyer until MTC is enabled', function () {
    // LA accepts out-of-state ids, but with no generator only a uniform form can cover it.
    expect(($this->option)(Livewire::test(CertificateWizard::class), 'LA'))->toBeNull();

    $this->business->resaleProfile->update(['mtc_enabled' => true]);

    expect(($this->option)(Livewire::test(CertificateWizard::class), 'LA')['selectable'])->toBeTrue();
});
