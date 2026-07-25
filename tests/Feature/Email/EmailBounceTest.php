<?php

use App\Domains\Business\Models\Business;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\SignatureEvent;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Mail\WaiverInvitationBounced;
use App\Mail\WaiverSignatureReminder;
use App\Models\EmailSequence;
use App\Models\User;
use App\Support\Email\RecordEmailBounce;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Mail;

/**
 * A collect-direction waiver awaiting a guest counterparty signature,
 * with its active signature request.
 *
 * @return array{0: LienWaiver, 1: SignatureRequest}
 */
function bounceGuestWaiverRequest(Business $business, User $owner, string $signerEmail = 'counterparty@example.com'): array
{
    $project = LienProject::factory()->forBusiness($business)->inState('TX')->create([
        'wizard_completed_at' => now(),
    ]);

    $waiver = LienWaiver::factory()->forProject($project)->collect()->create([
        'status' => WaiverStatus::AwaitingSignature,
        'sent_at' => now(),
        'signer_email' => $signerEmail,
        'created_by_user_id' => $owner->id,
    ]);

    $request = SignatureRequest::create([
        'signable_type' => 'lien_waiver',
        'signable_id' => $waiver->id,
        'business_id' => $waiver->business_id,
        'signer_user_id' => null,
        'document_signing_policy_key' => 'lien_waiver',
        'status' => SignatureRequestStatus::AwaitingSignature,
        'signer_name_snapshot' => $waiver->counterparty_name,
        'signer_email_snapshot' => $signerEmail,
        'invited_at' => now(),
        'expires_at' => now()->addDays(14),
        'created_by_user_id' => $owner->id,
    ]);

    return [$waiver, $request];
}

describe('postmark webhook', function () {
    beforeEach(function () {
        config()->set('services.postmark.webhook_token', 'test-token');
    });

    function postmarkWebhook(array $payload): \Illuminate\Testing\TestResponse
    {
        return test()->postJson(route('webhooks.postmark', ['token' => 'test-token']), $payload);
    }

    it('flags the user on a hard bounce', function () {
        $user = User::factory()->create();

        postmarkWebhook([
            'RecordType' => 'Bounce',
            'Type' => 'HardBounce',
            'Email' => $user->email,
        ])->assertSuccessful();

        $user->refresh();
        expect($user->email_bounced_at)->not->toBeNull();
        expect($user->email_bounce_reason)->toBe('hard_bounce');
    });

    it('flags the user on a spam complaint', function () {
        $user = User::factory()->create();

        postmarkWebhook([
            'RecordType' => 'SpamComplaint',
            'Email' => $user->email,
        ])->assertSuccessful();

        expect($user->refresh()->email_bounce_reason)->toBe('spam_complaint');
    });

    it('flags the user on a bounce-driven suppression but not a manual one', function () {
        $bounced = User::factory()->create();
        $optedOut = User::factory()->create();

        postmarkWebhook([
            'RecordType' => 'SubscriptionChange',
            'Recipient' => $bounced->email,
            'SuppressSending' => true,
            'SuppressionReason' => 'HardBounce',
        ])->assertSuccessful();

        postmarkWebhook([
            'RecordType' => 'SubscriptionChange',
            'Recipient' => $optedOut->email,
            'SuppressSending' => true,
            'SuppressionReason' => 'ManualSuppression',
        ])->assertSuccessful();

        expect($bounced->refresh()->email_bounced_at)->not->toBeNull();
        expect($optedOut->refresh()->email_bounced_at)->toBeNull();
    });

    it('ignores soft bounces: Postmark retries those itself', function () {
        $user = User::factory()->create();

        postmarkWebhook([
            'RecordType' => 'Bounce',
            'Type' => 'Transient',
            'Email' => $user->email,
        ])->assertSuccessful();

        expect($user->refresh()->email_bounced_at)->toBeNull();
    });

    it('rejects a bad token', function () {
        $user = User::factory()->create();

        test()->postJson(route('webhooks.postmark', ['token' => 'wrong']), [
            'RecordType' => 'Bounce',
            'Type' => 'HardBounce',
            'Email' => $user->email,
        ])->assertUnauthorized();

        expect($user->refresh()->email_bounced_at)->toBeNull();
    });
});

describe('bounce flag behavior', function () {
    it('clears the flag when the user changes their email', function () {
        $user = User::factory()->create();
        RecordEmailBounce::record($user->email, 'hard_bounce');
        expect($user->refresh()->email_bounced_at)->not->toBeNull();

        $user->update(['email' => 'fresh-address@example.com']);

        $user->refresh();
        expect($user->email_bounced_at)->toBeNull();
        expect($user->email_bounce_reason)->toBeNull();
    });

    it('keeps the flag when unrelated attributes change', function () {
        $user = User::factory()->create();
        RecordEmailBounce::record($user->email, 'hard_bounce');

        $user->update(['first_name' => 'Renamed']);

        expect($user->refresh()->email_bounced_at)->not->toBeNull();
    });

    it('suppresses email sequences for bounced users', function () {
        $user = User::factory()->create(['email_bounced_at' => now()]);

        $sequence = new EmailSequence;
        $sequence->setRelation('user', $user);

        expect($sequence->shouldSuppress())->toBe('email_bounced');
    });
});

describe('queue failure fallback', function () {
    it('flags users named in a Postmark inactive-recipient rejection', function () {
        $user = User::factory()->create();

        event(new JobFailed('database', Mockery::mock(\Illuminate\Contracts\Queue\Job::class), new Exception(
            "Unable to send an email: You tried to send to recipient(s) that have been marked as inactive. Found inactive addresses: {$user->email}. Inactive recipients are ones that have generated a hard bounce, a spam complaint, or a manual suppression. (code 406)."
        )));

        $user->refresh();
        expect($user->email_bounced_at)->not->toBeNull();
        expect($user->email_bounce_reason)->toBe('postmark_inactive');
    });

    it('ignores unrelated failures', function () {
        $user = User::factory()->create();

        event(new JobFailed('database', Mockery::mock(\Illuminate\Contracts\Queue\Job::class), new Exception(
            'No query results for model [App\Models\User].'
        )));

        expect($user->refresh()->email_bounced_at)->toBeNull();
    });
});

describe('guest signer bounces', function () {
    beforeEach(function () {
        config()->set('services.postmark.webhook_token', 'test-token');
        Mail::fake();

        $this->owner = User::factory()->create();
        $this->business = Business::factory()->create([
            'onboarding_completed_at' => now(),
            'lien_onboarding_completed_at' => now(),
        ]);
        $this->business->users()->attach($this->owner, ['role' => 'owner']);
    });

    it('flags the request, logs an audit event, and emails the owner', function () {
        [$waiver, $request] = bounceGuestWaiverRequest($this->business, $this->owner);

        postmarkWebhook([
            'RecordType' => 'Bounce',
            'Type' => 'HardBounce',
            'Email' => 'counterparty@example.com',
        ])->assertSuccessful();

        expect($request->refresh()->invitation_bounced_at)->not->toBeNull();

        $event = SignatureEvent::query()
            ->where('signature_request_id', $request->id)
            ->where('event_type', SignatureEventType::InvitationBounced->value)
            ->first();
        expect($event)->not->toBeNull();
        expect($event->meta('email'))->toBe('counterparty@example.com');

        Mail::assertQueued(WaiverInvitationBounced::class,
            fn (WaiverInvitationBounced $mail) => $mail->hasTo($this->owner->email)
                && $mail->signerEmail === 'counterparty@example.com');
    });

    it('handles duplicate bounce reports once', function () {
        [, $request] = bounceGuestWaiverRequest($this->business, $this->owner);

        $payload = ['RecordType' => 'Bounce', 'Type' => 'HardBounce', 'Email' => 'counterparty@example.com'];
        postmarkWebhook($payload)->assertSuccessful();
        postmarkWebhook($payload)->assertSuccessful();

        expect(SignatureEvent::query()
            ->where('signature_request_id', $request->id)
            ->where('event_type', SignatureEventType::InvitationBounced->value)
            ->count())->toBe(1);

        Mail::assertQueuedCount(1);
    });

    it('does not email the owner when the bounced address is their own', function () {
        // Provide-direction shape: the owner is the signer. The portal
        // banner covers them; mailing the dead address would 406 again.
        [, $request] = bounceGuestWaiverRequest($this->business, $this->owner, $this->owner->email);

        postmarkWebhook([
            'RecordType' => 'Bounce',
            'Type' => 'HardBounce',
            'Email' => $this->owner->email,
        ])->assertSuccessful();

        expect($request->refresh()->invitation_bounced_at)->not->toBeNull();
        Mail::assertNotQueued(WaiverInvitationBounced::class);
    });

    it('skips reminders for bounced invitations', function () {
        [, $bounced] = bounceGuestWaiverRequest($this->business, $this->owner, 'dead@example.com');
        [, $healthy] = bounceGuestWaiverRequest($this->business, $this->owner, 'alive@example.com');

        // Both are past the first reminder interval.
        SignatureRequest::whereKey([$bounced->id, $healthy->id])->update(['invited_at' => now()->subDays(4)]);
        $bounced->update(['invitation_bounced_at' => now()]);

        $this->artisan('lien:send-waiver-reminders')->assertSuccessful();

        Mail::assertQueued(WaiverSignatureReminder::class,
            fn (WaiverSignatureReminder $mail) => $mail->hasTo('alive@example.com'));
        Mail::assertNotQueued(WaiverSignatureReminder::class,
            fn (WaiverSignatureReminder $mail) => $mail->hasTo('dead@example.com'));
    });

    it('warns the owner on the waiver page', function () {
        [$waiver, $request] = bounceGuestWaiverRequest($this->business, $this->owner);
        $request->update(['invitation_bounced_at' => now()]);

        test()->actingAs($this->owner);
        session(['current_business_id' => $this->business->id]);

        test()->get(route('lien.waivers.show', $waiver))
            ->assertSuccessful()
            ->assertSee('never received the invitation')
            ->assertSee('counterparty@example.com');
    });
});

describe('portal banner', function () {
    it('warns a bounced user and links to profile settings', function () {
        $user = User::factory()->create(['email_bounced_at' => now()]);
        $business = \App\Domains\Business\Models\Business::factory()->create([
            'onboarding_completed_at' => now(),
        ]);
        $business->users()->attach($user, ['role' => 'owner']);

        test()->actingAs($user);
        session(['current_business_id' => $business->id]);

        test()->get(route('dashboard'))
            ->assertSuccessful()
            ->assertSee('messages are bouncing')
            ->assertSee(route('profile.edit'));
    });

    it('shows nothing for a deliverable email', function () {
        $user = User::factory()->create();
        $business = \App\Domains\Business\Models\Business::factory()->create([
            'onboarding_completed_at' => now(),
        ]);
        $business->users()->attach($user, ['role' => 'owner']);

        test()->actingAs($user);
        session(['current_business_id' => $business->id]);

        test()->get(route('dashboard'))
            ->assertSuccessful()
            ->assertDontSee('messages are bouncing');
    });
});
