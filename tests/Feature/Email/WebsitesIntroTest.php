<?php

use App\Domains\Business\Models\Business;
use App\Mail\WebsitesIntro;
use App\Mail\WebsitesIntroReminder;
use App\Models\EmailUnsubscribe;
use App\Models\SentEmail;
use App\Models\User;
use App\Models\WebsiteInvitation;
use App\Support\HappyWebsites;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;

/*
| email:send-websites-intro. The owner invites a customer to Happy Websites
| (a free site, the first month free) and holds a spot through a date: the
| Friday of the following week. One reminder goes out 3 days before that date
| unless the customer answered. A person is invited once, ever.
|
| The clock starts on Monday 2026-10-05 at 10:30 AM Eastern, so a spot is held
| through Friday, October 16 and the reminder is due on Tuesday, October 13.
*/

if (! function_exists('websitesIntroCustomer')) {
    /**
     * A customer with one business who signed up at $signedUp (UTC).
     *
     * @return array{0: User, 1: Business}
     */
    function websitesIntroCustomer(array $user = [], string $signedUp = '2026-09-01 15:00:00'): array
    {
        $owner = User::factory()->create(array_merge(['first_name' => 'Dana'], $user));
        // created_at is guarded on User.
        $owner->forceFill(['created_at' => $signedUp])->saveQuietly();

        $company = Business::factory()->create(['name' => 'Smith Roofing']);
        $company->users()->attach($owner, ['role' => 'owner']);

        return [$owner, $company];
    }
}

if (! function_exists('websitesIntroInvitation')) {
    /** An invitation sent on Monday 2026-10-05, so the spot is held through Friday, October 16. */
    function websitesIntroInvitation(User $user, ?Business $business = null, array $overrides = []): WebsiteInvitation
    {
        return WebsiteInvitation::create(array_merge([
            'user_id' => $user->id,
            'business_id' => $business?->id,
            'message_id' => "websites-intro.{$user->id}.abcdefgh12345678@eregister.test",
            'invited_at' => '2026-10-05 14:30:00',
            'deadline_on' => '2026-10-16',
        ], $overrides));
    }
}

beforeEach(function () {
    Carbon::setTestNow('2026-10-05 14:30:00');

    config()->set('happy_websites.intro.enabled', true);
    config()->set('happy_websites.intro.daily_cap', 10);
    config()->set('happy_websites.intro.inbound_address', null);
    config()->set('happy_websites.lead_email', ['hello@happywebsites.com']);
    config()->set('mail.postal_address', '1 Test St, Louisville, KY 40207');
    config()->set('mail.from.address', 'support@eregister.test');
});

describe('the command', function () {
    it('sends nothing while it is off', function () {
        Mail::fake();
        config()->set('happy_websites.intro.enabled', false);
        websitesIntroCustomer();

        $this->artisan('email:send-websites-intro')
            ->expectsOutputToContain('are off')
            ->assertSuccessful();

        Mail::assertNothingQueued();
        expect(WebsiteInvitation::count())->toBe(0);
    });

    it('lists who would get it on a dry run and saves nothing, even while off', function () {
        Mail::fake();
        config()->set('happy_websites.intro.enabled', false);
        [$dana] = websitesIntroCustomer();

        $this->artisan('email:send-websites-intro --dry-run')
            ->expectsOutputToContain("Would invite user #{$dana->id} ({$dana->email}): Smith Roofing, spot held through Friday, October 16")
            ->expectsOutputToContain('Dry run: 1 invitation(s) and 0 reminder(s) would go out.')
            ->expectsOutputToContain('Customers who could still be invited, in all: 1')
            ->assertSuccessful();

        Mail::assertNothingQueued();
        expect(WebsiteInvitation::count())->toBe(0);
    });

    it('refuses to send without a postal address', function () {
        Mail::fake();
        config()->set('mail.postal_address', null);
        websitesIntroCustomer();

        $this->artisan('email:send-websites-intro')
            ->expectsOutputToContain('MAIL_POSTAL_ADDRESS is not set')
            ->assertFailed();

        Mail::assertNothingQueued();
        expect(WebsiteInvitation::count())->toBe(0);
    });

    it('runs on weekday mornings Eastern', function () {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains((string) $event->command, 'email:send-websites-intro'));

        expect($event)->not->toBeNull()
            ->and($event->expression)->toBe('30 10 * * 1-5')
            ->and($event->timezone)->toBe('America/New_York');
    });

    it('sends both emails to one account as a sample without inviting anyone', function () {
        Mail::fake();
        [$dana] = websitesIntroCustomer();

        $this->artisan('email:send-websites-intro', ['--test-to' => $dana->email])
            ->expectsOutputToContain('as a sample')
            ->assertSuccessful();

        Mail::assertSent(WebsitesIntro::class, fn (WebsitesIntro $mail) => $mail->hasTo($dana->email));
        Mail::assertSent(WebsitesIntroReminder::class, fn (WebsitesIntroReminder $mail) => $mail->hasTo($dana->email));
        expect(WebsiteInvitation::count())->toBe(0);

        $this->artisan('email:send-websites-intro', ['--test-to' => 'nobody@example.com'])
            ->expectsOutputToContain('No account uses nobody@example.com')
            ->assertFailed();
    });
});

describe('who is invited', function () {
    it('invites a customer once, with a spot held through the Friday of the following week', function () {
        Mail::fake();
        [$dana, $business] = websitesIntroCustomer();

        $this->artisan('email:send-websites-intro')->assertSuccessful();

        Mail::assertQueued(WebsitesIntro::class, function (WebsitesIntro $mail) use ($dana) {
            return $mail->hasTo($dana->email)
                && $mail->hasSubject('Dana, can I introduce you to someone?')
                && $mail->hasReplyTo('hello@happywebsites.com');
        });

        $invitation = WebsiteInvitation::sole();
        expect($invitation->user_id)->toBe($dana->id)
            ->and($invitation->business_id)->toBe($business->id)
            ->and($invitation->deadline_on->toDateString())->toBe('2026-10-16')
            ->and($invitation->message_id)->toStartWith("websites-intro.{$dana->id}.")
            ->and($invitation->message_id)->toEndWith('@eregister.test');

        // The next mornings find nobody new.
        Carbon::setTestNow('2026-10-06 14:30:00');
        $this->artisan('email:send-websites-intro')->assertSuccessful();
        Carbon::setTestNow('2026-10-20 14:30:00');
        $this->artisan('email:send-websites-intro')->assertSuccessful();

        Mail::assertQueuedCount(1);
    });

    it('holds a Friday send through the next Friday', function () {
        expect(WebsiteInvitation::deadlineFor(Carbon::parse('2026-10-09 14:30:00'))->toDateString())->toBe('2026-10-16')
            // 9 PM Eastern on Sunday is already Monday in UTC. The week is the Eastern one.
            ->and(WebsiteInvitation::deadlineFor(Carbon::parse('2026-10-12 01:00:00'))->toDateString())->toBe('2026-10-16');
    });

    it('leaves out everyone it should not write to', function () {
        Mail::fake();
        Role::findOrCreate('admin', 'web');

        [$tooNew] = websitesIntroCustomer(['email' => 'new@example.com'], '2026-09-30 15:00:00');
        [$tooOld] = websitesIntroCustomer(['email' => 'old@example.com'], '2025-01-15 15:00:00');
        [$bounced] = websitesIntroCustomer(['email' => 'bounced@example.com', 'email_bounced_at' => now()]);
        [$optedOut] = websitesIntroCustomer(['email' => 'optedout@example.com']);
        EmailUnsubscribe::unsubscribe($optedOut, EmailUnsubscribe::CATEGORY_MARKETING);
        [$noEmail] = websitesIntroCustomer(['email' => 'nomail@example.com', 'unsubscribed_from_all_emails_at' => now()]);
        [$test] = websitesIntroCustomer(['email' => 'someone@test.test']);
        [$staff] = websitesIntroCustomer(['email' => 'staff@example.com']);
        $staff->assignRole('admin');
        [$lead] = websitesIntroCustomer(['email' => 'lead@example.com']);
        SentEmail::recordOrSkip(HappyWebsites::REQUEST_EMAIL_TYPE, $lead, $lead, fn () => null);
        [$invited] = websitesIntroCustomer(['email' => 'invited@example.com']);
        websitesIntroInvitation($invited, null, ['invited_at' => '2026-09-14 14:30:00', 'deadline_on' => '2026-09-25', 'reminded_at' => '2026-09-22 14:30:00']);

        $noBusiness = User::factory()->create(['email' => 'nobusiness@example.com']);
        $noBusiness->forceFill(['created_at' => '2026-09-01 15:00:00'])->saveQuietly();

        [$dana] = websitesIntroCustomer(['email' => 'dana@example.com']);

        $this->artisan('email:send-websites-intro')->assertSuccessful();

        Mail::assertQueued(WebsitesIntro::class, fn (WebsitesIntro $mail) => $mail->hasTo('dana@example.com'));
        Mail::assertQueuedCount(1);
        expect(WebsiteInvitation::where('invited_at', now())->pluck('user_id')->all())->toBe([$dana->id]);
    });

    it('takes the newest sign-ups first, up to the daily cap', function () {
        Mail::fake();
        config()->set('happy_websites.intro.daily_cap', 2);

        websitesIntroCustomer(['email' => 'july@example.com'], '2026-07-01 15:00:00');
        websitesIntroCustomer(['email' => 'september@example.com'], '2026-09-10 15:00:00');
        websitesIntroCustomer(['email' => 'august@example.com'], '2026-08-01 15:00:00');

        $this->artisan('email:send-websites-intro')->assertSuccessful();

        Mail::assertQueued(WebsitesIntro::class, fn (WebsitesIntro $mail) => $mail->hasTo('september@example.com'));
        Mail::assertQueued(WebsitesIntro::class, fn (WebsitesIntro $mail) => $mail->hasTo('august@example.com'));
        Mail::assertQueuedCount(2);

        // The one left over follows the next morning.
        Carbon::setTestNow('2026-10-06 14:30:00');
        $this->artisan('email:send-websites-intro')->assertSuccessful();

        Mail::assertQueued(WebsitesIntro::class, fn (WebsitesIntro $mail) => $mail->hasTo('july@example.com'));
        Mail::assertQueuedCount(3);
    });

    it('leaves alone someone we just emailed, and picks them up later', function () {
        Mail::fake();
        [$dana] = websitesIntroCustomer();
        SentEmail::recordOrSkip('review_request', $dana, $dana, fn () => null);

        $this->artisan('email:send-websites-intro')->assertSuccessful();
        Mail::assertNothingQueued();

        Carbon::setTestNow('2026-10-09 14:30:00');
        $this->artisan('email:send-websites-intro')->assertSuccessful();
        Mail::assertQueued(WebsitesIntro::class, fn (WebsitesIntro $mail) => $mail->hasTo($dana->email));
    });
});

describe('the emails', function () {
    it('says what the owner wrote, with the date, the work link and the footer', function () {
        [$dana, $business] = websitesIntroCustomer();
        $mail = new WebsitesIntro(websitesIntroInvitation($dana, $business));

        $mail->assertFrom('support@eregister.test', 'Major from eRegister')
            ->assertHasSubject('Dana, can I introduce you to someone?')
            ->assertSeeInText('Hi Dana,')
            ->assertSeeInText('Major here from eRegister.')
            ->assertSeeInText("Our sister company, Happy Websites, is building free sites for a few of our customers this month. I'd like Smith Roofing to be one of them.")
            ->assertSeeInText("They'll build the whole thing and run it for a month, free. If you like it after that, it's $99 a month to keep it.")
            ->assertSeeInText('Some of their work: https://happywebsites.com/work?utm_source=eregister&utm_medium=email&utm_campaign=websites')
            ->assertSeeInText('I can hold a spot for you through Friday, October 16. If you want it, just reply "yes" and I\'ll make the intro.')
            // Any mailer but Postmark gets our own preferences page.
            ->assertSeeInText('Unsubscribe: '.URL::signedRoute('email.preferences', ['user' => $dana->id]))
            ->assertSeeInText(config('app.name').', 1 Test St, Louisville, KY 40207');
    });

    it('leaves the unsubscribe link to Postmark on the broadcast stream', function () {
        config()->set('mail.default', 'postmark');
        [$dana, $business] = websitesIntroCustomer();

        (new WebsitesIntro(websitesIntroInvitation($dana, $business)))
            ->assertSeeInText('Unsubscribe: {{{ pm:unsubscribe }}}');
    });

    it('still reads well without a first name or a business name', function () {
        // No first name, and no business on the invitation (it was deleted since).
        [$someone] = websitesIntroCustomer(['first_name' => '']);
        $invitation = websitesIntroInvitation($someone);

        (new WebsitesIntro($invitation))
            ->assertHasSubject('Can I introduce you to someone?')
            ->assertSeeInText('Hi there,')
            ->assertSeeInText("I'd like your business to be one of them.");

        (new WebsitesIntroReminder($invitation))
            ->assertHasSubject('Re: Can I introduce you to someone?')
            ->assertSeeInText('Hi there,');
    });

    it('goes out on the broadcast stream with its own Message-ID, and the reminder joins that thread', function () {
        [$dana, $business] = websitesIntroCustomer();
        $invitation = websitesIntroInvitation($dana, $business);

        // Queued, as the command sends them, so both have to survive the queue.
        Mail::to($dana)->queue(new WebsitesIntro($invitation));
        Mail::to($dana)->queue(new WebsitesIntroReminder($invitation));

        [$first, $reminder] = Mail::mailer()->getSymfonyTransport()->messages()
            ->map(fn ($sent) => $sent->getOriginalMessage())->all();

        expect($first->getHeaders()->get('X-PM-Message-Stream')->getBodyAsString())->toBe('broadcast')
            ->and($first->getHeaders()->get('Message-ID')->getBodyAsString())->toBe("<{$invitation->message_id}>")
            ->and($first->getHeaders()->get('X-PM-KeepID')->getBodyAsString())->toBe('true')
            ->and($first->getHtmlBody())->toBeNull();

        expect($reminder->getSubject())->toBe('Re: Dana, can I introduce you to someone?')
            ->and($reminder->getHeaders()->get('X-PM-Message-Stream')->getBodyAsString())->toBe('broadcast')
            ->and($reminder->getHeaders()->get('In-Reply-To')->getBodyAsString())->toBe("<{$invitation->message_id}>")
            ->and($reminder->getHeaders()->get('References')->getBodyAsString())->toBe("<{$invitation->message_id}>")
            ->and($reminder->getTextBody())->toContain("Quick reminder, I've got your spot with Happy Websites held until Friday, October 16. Free site, free first month, only pay if you want to keep it.")
            ->and($reminder->getTextBody())->toContain('Reply "yes" if you want it. If not, no worries.')
            ->and($reminder->getTextBody())->toContain('Unsubscribe: ')
            ->and($reminder->getTextBody())->toContain(config('app.name').', 1 Test St, Louisville, KY 40207');
    });

    it('sends a "yes" to every inbox, and to the app when Postmark inbound is set up', function () {
        config()->set('happy_websites.lead_email', ['hello@happywebsites.com', 'owner@example.com']);
        [$dana, $business] = websitesIntroCustomer();
        $invitation = websitesIntroInvitation($dana, $business);

        expect(collect(WebsitesIntro::replyAddresses($dana))->pluck('address')->all())
            ->toBe(['hello@happywebsites.com', 'owner@example.com']);

        config()->set('happy_websites.intro.inbound_address', 'abc123@inbound.postmarkapp.com');
        $tracking = 'abc123+'.HappyWebsites::replyHash($dana->id).'@inbound.postmarkapp.com';

        (new WebsitesIntro($invitation))
            ->assertHasReplyTo('hello@happywebsites.com')
            ->assertHasReplyTo('owner@example.com')
            ->assertHasReplyTo($tracking);
        (new WebsitesIntroReminder($invitation))->assertHasReplyTo($tracking);
    });
});

describe('the reminder', function () {
    it('goes out 3 days before the date to a customer who has not answered, once', function () {
        Mail::fake();
        [$dana, $business] = websitesIntroCustomer();
        $invitation = websitesIntroInvitation($dana, $business);

        // Monday of that week is a day early.
        Carbon::setTestNow('2026-10-12 14:30:00');
        $this->artisan('email:send-websites-intro')->assertSuccessful();
        Mail::assertNotQueued(WebsitesIntroReminder::class);

        Carbon::setTestNow('2026-10-13 14:30:00');
        $this->artisan('email:send-websites-intro')
            ->expectsOutputToContain("Reminded user #{$dana->id}")
            ->assertSuccessful();

        Mail::assertQueued(WebsitesIntroReminder::class, function (WebsitesIntroReminder $mail) use ($dana) {
            return $mail->hasTo($dana->email)
                && $mail->hasSubject('Re: Dana, can I introduce you to someone?')
                && $mail->hasReplyTo('hello@happywebsites.com');
        });
        expect($invitation->refresh()->reminded_at)->not->toBeNull();

        Carbon::setTestNow('2026-10-14 14:30:00');
        $this->artisan('email:send-websites-intro')->assertSuccessful();
        Mail::assertQueued(WebsitesIntroReminder::class, 1);
    });

    it('skips anyone who answered, opted out, bounced or asked in the portal', function () {
        Mail::fake();

        [$answered] = websitesIntroCustomer(['email' => 'answered@example.com']);
        websitesIntroInvitation($answered, null, ['replied_at' => '2026-10-07 12:00:00']);

        [$optedOut] = websitesIntroCustomer(['email' => 'optedout@example.com']);
        websitesIntroInvitation($optedOut);
        EmailUnsubscribe::unsubscribe($optedOut, EmailUnsubscribe::CATEGORY_MARKETING);

        [$bounced] = websitesIntroCustomer(['email' => 'bounced@example.com']);
        websitesIntroInvitation($bounced);
        $bounced->forceFill(['email_bounced_at' => now()])->save();

        [$lead] = websitesIntroCustomer(['email' => 'lead@example.com']);
        websitesIntroInvitation($lead);
        SentEmail::recordOrSkip(HappyWebsites::REQUEST_EMAIL_TYPE, $lead, $lead, fn () => null);

        Carbon::setTestNow('2026-10-13 14:30:00');
        $this->artisan('email:send-websites-intro')->assertSuccessful();

        Mail::assertNotQueued(WebsitesIntroReminder::class);
        expect(WebsiteInvitation::whereNotNull('reminded_at')->count())->toBe(0);
    });

    it('still goes out a day or two late, but never on the date itself', function () {
        Mail::fake();
        [$dana, $business] = websitesIntroCustomer();
        websitesIntroInvitation($dana, $business);

        // The Tuesday and Wednesday runs were missed.
        Carbon::setTestNow('2026-10-15 14:30:00');
        $this->artisan('email:send-websites-intro')->assertSuccessful();
        Mail::assertQueued(WebsitesIntroReminder::class, 1);

        [$late, $lateBusiness] = websitesIntroCustomer(['email' => 'late@example.com']);
        websitesIntroInvitation($late, $lateBusiness);

        Carbon::setTestNow('2026-10-16 14:30:00');
        $this->artisan('email:send-websites-intro')->assertSuccessful();
        Mail::assertQueued(WebsitesIntroReminder::class, 1);
    });

    it('shows on a dry run without being marked as sent', function () {
        Mail::fake();
        [$dana, $business] = websitesIntroCustomer();
        $invitation = websitesIntroInvitation($dana, $business);

        Carbon::setTestNow('2026-10-13 14:30:00');
        $this->artisan('email:send-websites-intro --dry-run')
            ->expectsOutputToContain("Would remind user #{$dana->id} ({$dana->email}): spot held through Friday, October 16")
            ->assertSuccessful();

        Mail::assertNothingQueued();
        expect($invitation->refresh()->reminded_at)->toBeNull();
    });
});

describe('postmark', function () {
    beforeEach(function () {
        config()->set('services.postmark.webhook_token', 'test-token');
    });

    it('turns an unsubscribe on the broadcast stream into the marketing opt-out, and back', function () {
        [$dana] = websitesIntroCustomer();
        $change = fn (array $payload) => $this->postJson(route('webhooks.postmark', ['token' => 'test-token']), array_merge([
            'RecordType' => 'SubscriptionChange',
            'Recipient' => $dana->email,
        ], $payload))->assertSuccessful();

        // The same event on the transactional stream is not a marketing opt-out.
        $change(['MessageStream' => 'outbound', 'SuppressSending' => true, 'SuppressionReason' => 'ManualSuppression', 'Origin' => 'Recipient']);
        expect(EmailUnsubscribe::isUnsubscribed($dana, EmailUnsubscribe::CATEGORY_MARKETING))->toBeFalse();

        $change(['MessageStream' => 'broadcast', 'SuppressSending' => true, 'SuppressionReason' => 'ManualSuppression', 'Origin' => 'Recipient']);
        expect(EmailUnsubscribe::isUnsubscribed($dana, EmailUnsubscribe::CATEGORY_MARKETING))->toBeTrue()
            ->and($dana->refresh()->email_bounced_at)->toBeNull();

        $change(['MessageStream' => 'broadcast', 'SuppressSending' => false, 'SuppressionReason' => null, 'Origin' => 'Recipient']);
        expect(EmailUnsubscribe::isUnsubscribed($dana, EmailUnsubscribe::CATEGORY_MARKETING))->toBeFalse();
    });

    it('records an answer from the inbound webhook so the reminder skips that customer', function () {
        Mail::fake();
        [$dana, $business] = websitesIntroCustomer();
        $invitation = websitesIntroInvitation($dana, $business);

        $this->postJson(route('webhooks.postmark.inbound', ['token' => 'test-token']), [
            'From' => $dana->email,
            'MailboxHash' => HappyWebsites::replyHash($dana->id),
            'StrippedTextReply' => 'Yes',
        ])->assertSuccessful();

        expect($invitation->refresh()->replied_at)->not->toBeNull()
            ->and(EmailUnsubscribe::isUnsubscribed($dana, EmailUnsubscribe::CATEGORY_MARKETING))->toBeFalse();

        Carbon::setTestNow('2026-10-13 14:30:00');
        $this->artisan('email:send-websites-intro')->assertSuccessful();
        Mail::assertNotQueued(WebsitesIntroReminder::class);
    });

    it('treats "stop" as an opt-out and ignores a hash that is not ours', function () {
        [$dana, $business] = websitesIntroCustomer();
        $invitation = websitesIntroInvitation($dana, $business);
        $inbound = fn (array $payload) => $this->postJson(route('webhooks.postmark.inbound', ['token' => 'test-token']), $payload);

        $inbound(['MailboxHash' => "w{$dana->id}s000000000000", 'StrippedTextReply' => 'stop'])->assertSuccessful();
        $inbound(['MailboxHash' => '', 'TextBody' => 'stop'])->assertSuccessful();
        expect($invitation->refresh()->replied_at)->toBeNull()
            ->and(EmailUnsubscribe::isUnsubscribed($dana, EmailUnsubscribe::CATEGORY_MARKETING))->toBeFalse();

        $inbound(['MailboxHash' => HappyWebsites::replyHash($dana->id), 'StrippedTextReply' => "STOP\n\nSent from my iPhone"])->assertSuccessful();
        expect($invitation->refresh()->replied_at)->not->toBeNull()
            ->and(EmailUnsubscribe::isUnsubscribed($dana, EmailUnsubscribe::CATEGORY_MARKETING))->toBeTrue();

        $this->postJson(route('webhooks.postmark.inbound', ['token' => 'wrong']), ['MailboxHash' => HappyWebsites::replyHash($dana->id)])
            ->assertUnauthorized();
    });

    it('reads a rejected promotional email as an opt-out, not a dead address', function () {
        [$dana] = websitesIntroCustomer();

        event(new JobFailed('database', Mockery::mock(Job::class, ['resolveName' => WebsitesIntroReminder::class]), new Exception(
            "Unable to send an email: You tried to send to recipient(s) that have been marked as inactive. Found inactive addresses: {$dana->email}. Inactive recipients are ones that have generated a hard bounce, a spam complaint, or a manual suppression. (code 406)."
        )));

        expect($dana->refresh()->email_bounced_at)->toBeNull()
            ->and(EmailUnsubscribe::isUnsubscribed($dana, EmailUnsubscribe::CATEGORY_MARKETING))->toBeTrue();
    });
});
