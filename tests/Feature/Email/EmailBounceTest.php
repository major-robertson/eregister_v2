<?php

use App\Models\EmailSequence;
use App\Models\User;
use App\Support\Email\RecordEmailBounce;
use Illuminate\Queue\Events\JobFailed;

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
