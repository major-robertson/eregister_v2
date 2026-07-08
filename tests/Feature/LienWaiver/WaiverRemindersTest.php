<?php

use App\Domains\Business\Models\Business;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Esign\LienWaiverSignable;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Models\LienWaiverNotificationLog;
use App\Mail\WaiverSignatureReminder;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

/**
 * A guest signing session awaiting signature, invited N days ago, fabricated
 * directly (no PDF rendering; the reminder command never touches documents).
 */
function waiverReminderRequest(array $requestOverrides = []): SignatureRequest
{
    $business = Business::factory()->create();
    $creator = User::factory()->create(['email_verified_at' => now()]);
    $project = LienProject::factory()->forBusiness($business)->inState('CO')->create();

    $waiver = LienWaiver::factory()->forProject($project)->collect()->create([
        'created_by_user_id' => $creator->id,
        'status' => WaiverStatus::AwaitingSignature,
        'sent_at' => now()->subDays(4),
    ]);

    return SignatureRequest::create(array_merge([
        'signable_type' => 'lien_waiver',
        'signable_id' => $waiver->id,
        'business_id' => $waiver->business_id,
        'signer_user_id' => null,
        'document_signing_policy_key' => LienWaiverSignable::DOCUMENT_TYPE,
        'status' => SignatureRequestStatus::AwaitingSignature,
        'signer_name_snapshot' => $waiver->signer_name,
        'signer_email_snapshot' => $waiver->signer_email,
        'invited_at' => now()->subDays(4),
        'expires_at' => now()->addDays(10),
    ], $requestOverrides));
}

function waiverReminderLogs(SignatureRequest $request): Collection
{
    return LienWaiverNotificationLog::withoutGlobalScopes()
        ->where('lien_waiver_id', $request->signable_id)
        ->where('type', 'signature_reminder')
        ->orderBy('interval_days')
        ->get();
}

it('sends the day-3 reminder once a request has waited 4 days, logging it with the business id', function () {
    $request = waiverReminderRequest();

    $this->artisan('lien:send-waiver-reminders')
        ->expectsOutputToContain('Sent 1')
        ->assertSuccessful();

    $logs = waiverReminderLogs($request);
    expect($logs)->toHaveCount(1);

    $log = $logs->first();
    expect($log->interval_days)->toBe(3);
    expect($log->business_id)->toBe($request->business_id);
    expect($log->sent_at)->not->toBeNull();

    Mail::assertQueued(WaiverSignatureReminder::class, 1);
    Mail::assertQueued(WaiverSignatureReminder::class,
        fn (WaiverSignatureReminder $mail) => $mail->hasTo($request->signer_email_snapshot) && $mail->daysWaiting === 4);
});

it('does not duplicate a reminder when the command re-runs', function () {
    $request = waiverReminderRequest();

    $this->artisan('lien:send-waiver-reminders')->assertSuccessful();
    $this->artisan('lien:send-waiver-reminders')
        ->expectsOutputToContain('Sent 0')
        ->assertSuccessful();

    expect(waiverReminderLogs($request))->toHaveCount(1);
    Mail::assertQueued(WaiverSignatureReminder::class, 1);
});

it('fires the day-7 reminder exactly once at 8 days without repeating day-3', function () {
    $request = waiverReminderRequest();

    $this->artisan('lien:send-waiver-reminders')->assertSuccessful();

    $this->travel(4)->days(); // now 8 days since the invitation

    $this->artisan('lien:send-waiver-reminders')->assertSuccessful();
    $this->artisan('lien:send-waiver-reminders')->assertSuccessful();

    $logs = waiverReminderLogs($request);
    expect($logs)->toHaveCount(2);
    expect($logs->pluck('interval_days')->all())->toBe([3, 7]);

    Mail::assertQueued(WaiverSignatureReminder::class, 2);
    Mail::assertQueued(WaiverSignatureReminder::class,
        fn (WaiverSignatureReminder $mail) => $mail->daysWaiting === 8);
});

it('skips expired requests', function () {
    $request = waiverReminderRequest([
        'invited_at' => now()->subDays(5),
        'expires_at' => now()->subDay(),
    ]);

    $this->artisan('lien:send-waiver-reminders')
        ->expectsOutputToContain('Sent 0')
        ->assertSuccessful();

    expect(waiverReminderLogs($request))->toHaveCount(0);
    Mail::assertNotQueued(WaiverSignatureReminder::class);
});

it('skips completed requests', function () {
    $request = waiverReminderRequest([
        'status' => SignatureRequestStatus::Completed,
        'completed_at' => now()->subDay(),
    ]);

    $this->artisan('lien:send-waiver-reminders')
        ->expectsOutputToContain('Sent 0')
        ->assertSuccessful();

    expect(waiverReminderLogs($request))->toHaveCount(0);
    Mail::assertNotQueued(WaiverSignatureReminder::class);
});
