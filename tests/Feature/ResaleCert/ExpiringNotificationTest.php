<?php

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Models\ResaleCertificateNotification;
use App\Domains\ResaleCert\Models\ResaleVendor;
use App\Mail\ResaleCertificatesExpiring;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::create([
        'name' => 'Expiry Test Co',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);

    $this->vendor = ResaleVendor::factory()->create(['business_id' => $this->business->id]);
});

it('records notifications and queues a digest for expiring certificates', function () {
    Mail::fake();

    $certificate = ResaleCertificate::factory()->expiringSoon(20)->create([
        'business_id' => $this->business->id,
        'resale_vendor_id' => $this->vendor->id,
    ]);

    $this->artisan('resale-cert:check-expiring', ['--send-emails' => true])
        ->assertExitCode(0);

    $notification = ResaleCertificateNotification::withoutGlobalScope('business')
        ->where('resale_certificate_id', $certificate->id)
        ->first();

    expect($notification)->not->toBeNull()
        ->and($notification->type)->toBe('expiring_soon')
        ->and($notification->severity)->toBe('danger');

    Mail::assertQueued(ResaleCertificatesExpiring::class, function ($mail) {
        return $mail->business->id === $this->business->id
            && $mail->urgent->count() === 1;
    });
});

it('does not renotify the same certificate within seven days', function () {
    Mail::fake();

    ResaleCertificate::factory()->expiringSoon(20)->create([
        'business_id' => $this->business->id,
        'resale_vendor_id' => $this->vendor->id,
    ]);

    $this->artisan('resale-cert:check-expiring', ['--send-emails' => true]);
    $this->artisan('resale-cert:check-expiring', ['--send-emails' => true]);

    expect(ResaleCertificateNotification::withoutGlobalScope('business')->count())->toBe(1);

    Mail::assertQueuedCount(1);
});

it('ignores certificates that never expire or are outside the window', function () {
    Mail::fake();

    ResaleCertificate::factory()->create([
        'business_id' => $this->business->id,
        'resale_vendor_id' => $this->vendor->id,
        'expiration_date' => null,
    ]);
    ResaleCertificate::factory()->create([
        'business_id' => $this->business->id,
        'resale_vendor_id' => $this->vendor->id,
        'expiration_date' => now()->addDays(200)->toDateString(),
    ]);

    $this->artisan('resale-cert:check-expiring', ['--send-emails' => true]);

    expect(ResaleCertificateNotification::withoutGlobalScope('business')->count())->toBe(0);
    Mail::assertNothingQueued();
});
