<?php

use App\Domains\Business\Models\Business;
use App\Domains\Esign\Models\EsignConsent;
use App\Domains\ResaleCert\Livewire\CertificateWizard;
use App\Domains\ResaleCert\Livewire\SignaturePad;
use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Models\ResaleProfile;
use App\Domains\ResaleCert\Models\ResaleSignatureEvent;
use App\Domains\ResaleCert\Models\ResaleTaxRegistration;
use App\Domains\ResaleCert\Models\ResaleVendor;
use App\Models\User;
use App\Models\UserSignature;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake(config('resale_cert.disk'));

    $this->user = User::factory()->create(['first_name' => 'Pat', 'last_name' => 'Signer']);
    $this->business = Business::create([
        'name' => 'Audit Test Co',
        'legal_name' => 'Audit Test Co LLC',
        'business_address' => ['line1' => '1 Main St', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78701'],
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);
    subscribeToResaleCerts($this->business);
    ResaleProfile::factory()->create(['business_id' => $this->business->id]);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

it('blocks signature adoption for unverified emails', function () {
    $this->user->forceFill(['email_verified_at' => null])->save();

    Livewire::test(SignaturePad::class)
        ->call('save', tinySignaturePng(), null, true)
        ->assertHasErrors(['signature']);

    expect(UserSignature::count())->toBe(0)
        ->and(ResaleSignatureEvent::count())->toBe(0);
});

it('lets unverified users into setup but gates generation behind verification', function () {
    $this->user->forceFill(['email_verified_at' => null])->save();

    // Onboarding renders only while the profile is incomplete.
    $this->business->resaleProfile->update(['completed_at' => null]);

    // Setup pages load (the signature step shows a verify-email prompt
    // in-context) — users are never dumped on a bare verify page first.
    $this->get(route('resale-cert.onboarding'))->assertSuccessful();

    $this->business->resaleProfile->update(['completed_at' => now()]);
    $this->get(route('resale-cert.settings'))->assertSuccessful();

    // Generation applies the signature, so it redirects to verification and
    // stashes the intended URL for the post-verification return.
    $this->get(route('resale-cert.certificates.create'))->assertRedirect(route('verification.notice'));

    expect(session('url.intended'))->toBe(route('resale-cert.certificates.create'));
});

it('records ESIGN consent and a chained adoption event when a signature is saved', function () {
    Livewire::test(SignaturePad::class)
        ->call('save', tinySignaturePng(), json_encode([[['x' => 1, 'y' => 2]]]), true)
        ->assertHasNoErrors()
        ->assertDispatched('resale-signature-saved');

    $consent = EsignConsent::query()
        ->where('user_id', $this->user->id)
        ->where('consent_scope', 'sales_tax_resale_certs')
        ->first();

    expect($consent)->not->toBeNull()
        ->and($consent->version)->toBe(config('esign.consent.version'))
        ->and($consent->disclosure_text)->toContain('Consent to Use Electronic Signatures')
        ->and($consent->consented_ip)->not->toBeNull();

    $signature = UserSignature::where('user_id', $this->user->id)->first();

    expect($signature->is_current)->toBeTrue()
        ->and($signature->created_ip)->not->toBeNull()
        ->and($signature->agreed_at)->not->toBeNull();

    $events = ResaleSignatureEvent::orderBy('id')->get();

    expect($events->pluck('event_type.value')->all())->toBe(['consent_accepted', 'signature_adopted'])
        ->and($events[0]->previous_event_hash)->toBeNull()
        ->and($events[1]->previous_event_hash)->toBe($events[0]->event_hash)
        ->and($events[1]->ip_address)->not->toBeNull()
        ->and($events[1]->meta('consent_id'))->toBe($consent->id)
        ->and($events[1]->meta('image_sha256'))->not->toBeNull()
        ->and($events[1]->meta('email_verified_at'))->not->toBeNull();
});

it('adopts a typed signature with font choice', function () {
    Livewire::test(SignaturePad::class)
        ->call('save', tinySignaturePng(), null, true, 'typed', 'Pat Signer', 'great-vibes')
        ->assertHasNoErrors();

    $signature = UserSignature::where('user_id', $this->user->id)->first();

    expect($signature->method)->toBe('typed')
        ->and($signature->typed_name)->toBe('Pat Signer')
        ->and($signature->typed_font)->toBe('great-vibes')
        ->and($signature->is_current)->toBeTrue();

    $adoption = ResaleSignatureEvent::where('event_type', 'signature_adopted')->first();

    expect($adoption->meta('method'))->toBe('typed')
        ->and($adoption->meta('typed_font'))->toBe('great-vibes');
});

it('rejects a typed signature with an unknown font', function () {
    Livewire::test(SignaturePad::class)
        ->call('save', tinySignaturePng(), null, true, 'typed', 'Pat Signer', 'comic-sans')
        ->assertHasErrors(['typed_font']);

    expect(UserSignature::count())->toBe(0);
});

it('reuses existing consent and links replacement adoptions', function () {
    Livewire::test(SignaturePad::class)->call('save', tinySignaturePng(), null, true);
    $firstSignatureId = UserSignature::latest('id')->value('id');

    Livewire::test(SignaturePad::class)->call('save', tinySignaturePng(), null, true);

    expect(EsignConsent::where('consent_scope', 'sales_tax_resale_certs')->count())->toBe(1)
        ->and(UserSignature::where('is_current', true)->count())->toBe(1);

    $lastAdoption = ResaleSignatureEvent::where('event_type', 'signature_adopted')->orderByDesc('id')->first();

    expect($lastAdoption->meta('replaced_signature_id'))->toBe($firstSignatureId);
});

it('audits generation with the pdf hash and downloads with actor and ip', function () {
    ResaleTaxRegistration::factory()->homeState()->create([
        'business_id' => $this->business->id,
        'state_code' => 'TX',
        'tax_id' => '11122233344',
    ]);
    $vendor = ResaleVendor::factory()->create(['business_id' => $this->business->id]);

    Livewire::test(SignaturePad::class)->call('save', tinySignaturePng(), null, true);

    Livewire::test(CertificateWizard::class)
        ->set('vendorId', (string) $vendor->id)
        ->set('selectedStates', ['TX'])
        ->call('continueToReview')
        ->call('generate')
        ->assertHasNoErrors();

    $certificate = ResaleCertificate::first();

    expect($certificate->pdf_sha256)->not->toBeNull();

    $generated = ResaleSignatureEvent::where('event_type', 'certificate_generated')->first();

    expect($generated)->not->toBeNull()
        ->and($generated->resale_certificate_id)->toBe($certificate->id)
        ->and($generated->actor_user_id)->toBe($this->user->id)
        ->and($generated->meta('pdf_sha256'))->toBe($certificate->pdf_sha256);

    $this->get(route('resale-cert.certificates.download', $certificate))->assertSuccessful();

    $download = ResaleSignatureEvent::where('event_type', 'certificate_downloaded')->first();

    expect($download)->not->toBeNull()
        ->and($download->actor_user_id)->toBe($this->user->id)
        ->and($download->ip_address)->not->toBeNull()
        ->and($download->meta('pdf_sha256'))->toBe($certificate->pdf_sha256);
});

it('audits certificate deletion', function () {
    $vendor = ResaleVendor::factory()->create(['business_id' => $this->business->id]);
    $certificate = ResaleCertificate::factory()->create([
        'business_id' => $this->business->id,
        'resale_vendor_id' => $vendor->id,
    ]);

    Livewire::test(\App\Domains\ResaleCert\Livewire\CertificateShow::class, ['certificate' => $certificate])
        ->call('deleteCertificate');

    $event = ResaleSignatureEvent::where('event_type', 'certificate_deleted')->first();

    expect($event)->not->toBeNull()
        ->and($event->resale_certificate_id)->toBe($certificate->id)
        ->and($event->actor_user_id)->toBe($this->user->id);
});

it('verifies an intact chain and detects tampering', function () {
    Livewire::test(SignaturePad::class)->call('save', tinySignaturePng(), null, true);

    $this->artisan('resale-cert:verify-chain')->assertExitCode(0);

    // Tamper via the query builder (the model guard blocks Eloquent updates).
    \Illuminate\Support\Facades\DB::table('resale_signature_events')
        ->where('id', ResaleSignatureEvent::orderByDesc('id')->value('id'))
        ->update(['ip_address' => '10.66.66.66']);

    $this->artisan('resale-cert:verify-chain')->assertExitCode(1);
});

it('refuses to update or delete audit rows through the model', function () {
    Livewire::test(SignaturePad::class)->call('save', tinySignaturePng(), null, true);

    $event = ResaleSignatureEvent::firstOrFail();

    expect(fn () => $event->update(['ip_address' => 'x']))->toThrow(RuntimeException::class)
        ->and(fn () => $event->delete())->toThrow(RuntimeException::class);
});
