<?php

use App\Domains\Business\Models\Business;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Mail\FilingActionReminder;
use App\Models\EmailSequence;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create();
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->project = LienProject::factory()->forBusiness($this->business)->create();
    $this->docType = LienDocumentType::first();

    $this->filing = LienFiling::factory()->forProject($this->project)->create([
        'document_type_id' => $this->docType->id,
        'status' => FilingStatus::Paid,
        'paid_at' => now(),
        'created_by_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);
});

it('creates a reminder sequence when filing enters a waiting status', function (FilingStatus $waitingStatus) {
    $this->filing->transitionTo($waitingStatus);

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_type', $this->filing->getMorphClass())
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    expect($sequence)->not->toBeNull();
    expect($sequence->trigger_status)->toBe($waitingStatus->value);
    expect($sequence->user_id)->toBe($this->user->id);
    expect($sequence->business_id)->toBe($this->business->id);
    expect($sequence->next_send_at)->not->toBeNull();
    expect($sequence->suppressed_at)->toBeNull();
})->with([
    'awaiting_client' => [FilingStatus::AwaitingClient],
    'awaiting_esign' => [FilingStatus::AwaitingEsign],
    'awaiting_notary' => [FilingStatus::AwaitingNotary],
]);

it('suppresses the reminder sequence when filing leaves a waiting status', function () {
    $this->filing->transitionTo(FilingStatus::AwaitingClient);

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    expect($sequence->suppressed_at)->toBeNull();

    $this->filing->transitionTo(FilingStatus::ReadyToFile);

    $sequence->refresh();
    expect($sequence->suppressed_at)->not->toBeNull();
    expect($sequence->suppression_reason)->toBe('status_changed');
});

it('replaces the old sequence on waiting-to-waiting transitions', function () {
    $this->filing->transitionTo(FilingStatus::AwaitingNotary);

    $oldSequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    $oldId = $oldSequence->id;
    expect($oldSequence->trigger_status)->toBe('awaiting_notary');

    $this->filing->transitionTo(FilingStatus::AwaitingClient);

    expect(EmailSequence::find($oldId))->toBeNull();

    $newSequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    expect($newSequence)->not->toBeNull();
    expect($newSequence->id)->not->toBe($oldId);
    expect($newSequence->trigger_status)->toBe('awaiting_client');
    expect($newSequence->suppressed_at)->toBeNull();
});

it('cleans up SentEmail records when replacing a sequence', function () {
    $this->filing->transitionTo(FilingStatus::AwaitingNotary);

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    SentEmail::create([
        'user_id' => $this->user->id,
        'email_type' => 'filing_action_reminder_step_1',
        'emailable_type' => $sequence->getMorphClass(),
        'emailable_id' => $sequence->id,
        'scheduled_at' => now(),
        'sent_at' => now(),
    ]);

    expect(SentEmail::where('emailable_id', $sequence->id)->count())->toBe(1);

    $this->filing->transitionTo(FilingStatus::AwaitingClient);

    expect(SentEmail::where('emailable_id', $sequence->id)->count())->toBe(0);
});

it('does not create a sequence when transitioning to a non-waiting status', function () {
    $this->filing->transitionTo(FilingStatus::InFulfillment);

    expect(
        EmailSequence::where('sequence_type', 'filing_action_reminder')
            ->where('sequenceable_id', $this->filing->id)
            ->exists()
    )->toBeFalse();
});

it('does not create a sequence when leaving a non-waiting status', function () {
    $this->filing->update(['status' => FilingStatus::InFulfillment]);

    $this->filing->transitionTo(FilingStatus::Mailed);

    expect(
        EmailSequence::where('sequence_type', 'filing_action_reminder')
            ->where('sequenceable_id', $this->filing->id)
            ->exists()
    )->toBeFalse();
});

it('shouldSuppress returns null when filing status matches trigger_status', function () {
    $this->filing->transitionTo(FilingStatus::AwaitingClient);

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    expect($sequence->shouldSuppress())->toBeNull();
});

it('shouldSuppress returns status_changed when filing moves to non-waiting status', function () {
    $this->filing->transitionTo(FilingStatus::AwaitingClient);

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    $this->filing->update(['status' => FilingStatus::InFulfillment]);
    $sequence->sequenceable->refresh();

    expect($sequence->shouldSuppress())->toBe('status_changed');
});

it('shouldSuppress returns status_changed when filing status differs from trigger_status', function () {
    $this->filing->transitionTo(FilingStatus::AwaitingClient);

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    $this->filing->update(['status' => FilingStatus::AwaitingEsign]);
    $sequence->sequenceable->refresh();

    expect($sequence->shouldSuppress())->toBe('status_changed');
});

it('shouldSuppress returns sequenceable_deleted when filing is deleted', function () {
    $this->filing->transitionTo(FilingStatus::AwaitingClient);

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    $this->filing->forceDelete();
    $sequence->unsetRelation('sequenceable');

    expect($sequence->shouldSuppress())->toBe('sequenceable_deleted');
});

it('has the correct delay schedule', function () {
    $config = (new EmailSequence(['sequence_type' => 'filing_action_reminder']))->config();

    expect($config['steps'])->toBe(5);
    expect($config['delays'])->toBe([2880, 4320, 10080, 10080, 10080]);
});

it('sets the first reminder 2 days out', function () {
    $this->travelTo(now()->startOfMinute());

    $this->filing->transitionTo(FilingStatus::AwaitingClient);

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    $expectedAt = now()->addMinutes(2880);

    expect($sequence->next_send_at->diffInSeconds($expectedAt))->toBeLessThan(5);
});

it('renders the mailable with correct content per status', function (FilingStatus $status, string $expectedHeadline) {
    $this->filing->transitionTo($status);

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    $mailable = new FilingActionReminder($sequence, 1);

    expect($mailable->headline)->toBe($expectedHeadline);
    expect($mailable->ctaUrl)->toContain($this->filing->public_id);
})->with([
    'awaiting_client' => [FilingStatus::AwaitingClient, 'We need information from you'],
    'awaiting_esign' => [FilingStatus::AwaitingEsign, 'Please sign your document'],
    'awaiting_notary' => [FilingStatus::AwaitingNotary, 'Your document needs notarization'],
]);

it('escalates the subject line with step number', function (int $step, string $expectedPrefix) {
    $this->filing->transitionTo(FilingStatus::AwaitingClient);

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    $mailable = new FilingActionReminder($sequence, $step);
    $envelope = $mailable->envelope();

    expect($envelope->subject)->toStartWith($expectedPrefix);
})->with([
    'step 1 — action required' => [1, 'Action required'],
    'step 2 — action required' => [2, 'Action required'],
    'step 3 — reminder' => [3, 'Reminder'],
    'step 4 — urgent' => [4, 'Urgent'],
    'step 5 — urgent' => [5, 'Urgent'],
]);

it('only creates one active sequence per filing', function () {
    $this->filing->transitionTo(FilingStatus::AwaitingNotary);
    $this->filing->transitionTo(FilingStatus::AwaitingClient);

    $count = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->count();

    expect($count)->toBe(1);
});

/** An e-sign session awaiting the filing creator's signature, fabricated directly (no PDFs). */
function esignReminderRequest(LienFiling $filing, User $signer, DateTimeInterface $expiresAt): SignatureRequest
{
    return SignatureRequest::create([
        'signable_type' => $filing->getMorphClass(),
        'signable_id' => $filing->id,
        'business_id' => $filing->business_id,
        'signer_user_id' => $signer->id,
        'document_signing_policy_key' => 'demand_letter',
        'status' => SignatureRequestStatus::AwaitingSignature,
        'signer_name_snapshot' => $signer->name,
        'signer_email_snapshot' => $signer->email,
        'invited_at' => now(),
        'expires_at' => $expiresAt,
    ]);
}

it('fits awaiting-esign reminders inside the signing link lifetime', function () {
    $esign = (new EmailSequence(['sequence_type' => 'filing_action_reminder', 'trigger_status' => 'awaiting_esign']))->config();
    $client = (new EmailSequence(['sequence_type' => 'filing_action_reminder', 'trigger_status' => 'awaiting_client']))->config();

    expect($esign['steps'])->toBe(5);
    expect($esign['delays'])->toBe([2880, 4320, 4320, 4320, 2880]); // days 2, 5, 8, 11, 13
    expect(array_sum($esign['delays']))->toBeLessThan(config('esign.signing.invitation_link_ttl_days') * 24 * 60);

    // Statuses without an expiring link keep the longer cadence.
    expect($client['delays'])->toBe([2880, 4320, 10080, 10080, 10080]);
});

it('sends every e-sign reminder before the link expires, warning on day 13', function () {
    Mail::fake();
    $this->travelTo(now()->startOfMinute());

    $request = esignReminderRequest($this->filing, $this->user, now()->addDays(config('esign.signing.invitation_link_ttl_days')));
    $this->filing->transitionTo(FilingStatus::AwaitingEsign);

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    $sentOnDay = [];

    for ($i = 0; $i < 10 && $sequence->refresh()->next_send_at !== null; $i++) {
        $this->travelTo($sequence->next_send_at);
        $this->artisan('email:process-sequences')->assertSuccessful();
        $sentOnDay[] = (int) $request->invited_at->diffInDays(now());
    }

    expect($sentOnDay)->toBe([2, 5, 8, 11, 13]);
    expect(now()->lessThan($request->expires_at))->toBeTrue();

    expect(Mail::queued(FilingActionReminder::class)->pluck('headline')->all())->toBe([
        'Please sign your document',
        'Please sign your document',
        'Please sign your document',
        'Please sign your document',
        'Your signing link expires in 24 hours',
    ]);
});

it('does not warn about expiry when the signing link has been renewed', function () {
    esignReminderRequest($this->filing, $this->user, now()->addDays(10));
    $this->filing->transitionTo(FilingStatus::AwaitingEsign);

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    expect((new FilingActionReminder($sequence, 5))->headline)->toBe('Please sign your document');
});

it('puts a call-to-action button in the email', function (FilingStatus $status, string $label) {
    $this->filing->transitionTo($status);

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    $mailable = new FilingActionReminder($sequence, 1);

    $mailable->assertSeeInHtml($label);
    $mailable->assertSeeInHtml($mailable->ctaUrl);
})->with([
    'awaiting_client' => [FilingStatus::AwaitingClient, 'Provide Information'],
    'awaiting_esign' => [FilingStatus::AwaitingEsign, 'Sign Now'],
    'awaiting_notary' => [FilingStatus::AwaitingNotary, 'View Filing Details'],
]);

it('points the e-sign reminder button straight at the signing page', function () {
    $request = esignReminderRequest($this->filing, $this->user, now()->addDays(14));
    $this->filing->transitionTo(FilingStatus::AwaitingEsign);

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'filing_action_reminder')
        ->where('sequenceable_id', $this->filing->id)
        ->first();

    $mailable = new FilingActionReminder($sequence, 1);

    expect($mailable->ctaUrl)->toContain('/esign/'.$request->public_id);
    $mailable->assertSeeInHtml($mailable->ctaUrl);
});
