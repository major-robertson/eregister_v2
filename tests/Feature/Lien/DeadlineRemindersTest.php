<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienDeadlineRule;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienNotificationLog;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Notifications\DeadlineApproaching;
use App\Models\EmailUnsubscribe;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Notification;

/*
| lien:send-deadline-reminders. The rule that matters most: it only looks
| forward. This command matched nothing for months, so the first run after the
| fix meets a database full of overdue deadlines, and none of them may be mailed.
*/

if (! function_exists('reminderBusiness')) {
    /**
     * @return array{0: Business, 1: User}
     */
    function reminderBusiness(string $timezone = 'America/New_York'): array
    {
        $business = Business::factory()->create(['timezone' => $timezone]);
        $owner = User::factory()->create(['first_name' => 'Dana']);
        $business->users()->attach($owner, ['role' => 'owner']);

        return [$business, $owner];
    }
}

if (! function_exists('reminderDeadline')) {
    /** A deadline on its own project, due on $dueDate (a plain date). */
    function reminderDeadline(Business $business, string $dueDate, string $slug = 'prelim_notice', array $overrides = []): LienProjectDeadline
    {
        $project = LienProject::factory()->forBusiness($business)->inState('CA')->create([
            'name' => 'Market Street Lofts',
            'wizard_completed_at' => now(),
        ]);

        return LienProjectDeadline::factory()->forProject($project)->create(array_merge([
            'document_type_id' => LienDocumentType::where('slug', $slug)->firstOrFail()->id,
            'due_date' => $dueDate,
        ], $overrides));
    }
}

beforeEach(function () {
    Notification::fake();

    // Monday 2026-10-05, 10:00 in New York (14:00 UTC), 07:00 in Los Angeles.
    $this->travelTo(Carbon::parse('2026-10-05 14:00:00', 'UTC'));
    $this->today = Carbon::parse('2026-10-05');
});

describe('only looks forward', function () {
    it('never mails an overdue deadline, however many there are and however empty the log is', function () {
        // What production looks like on the first run: hundreds of open
        // deadlines already past, and not one log row.
        foreach (['2026-10-04', '2026-10-03', '2026-09-28', '2026-09-05', '2026-01-15', '2025-06-01', '1926-06-20'] as $pastDate) {
            [$business] = reminderBusiness();
            reminderDeadline($business, $pastDate);
            reminderDeadline($business, $pastDate, 'mechanics_lien');
        }

        expect(LienNotificationLog::count())->toBe(0);

        $this->artisan('lien:send-deadline-reminders')->assertSuccessful();

        Notification::assertNothingSent();
        expect(LienNotificationLog::count())->toBe(0);
    });

    it('does not catch up on a reminder day that already went by', function () {
        [$business] = reminderBusiness();

        // Due in 6, 2 and 13 days: their 7, 3 and 14-day reminders were "yesterday".
        reminderDeadline($business, '2026-10-11');
        reminderDeadline($business, '2026-10-07');
        reminderDeadline($business, '2026-10-18');

        $this->artisan('lien:send-deadline-reminders')->assertSuccessful();

        Notification::assertNothingSent();
        expect(LienNotificationLog::count())->toBe(0);
    });

    it('stays quiet on every day that is not a reminder day', function () {
        [$business] = reminderBusiness();

        foreach ([2, 4, 5, 6, 8, 10, 13, 15, 30, 90] as $daysOut) {
            reminderDeadline($business, $this->today->copy()->addDays($daysOut)->toDateString());
        }

        $this->artisan('lien:send-deadline-reminders')->assertSuccessful();

        Notification::assertNothingSent();
    });
});

describe('sends on the exact day', function () {
    it('reminds the business 14, 7, 3 and 1 days out and on the day itself', function (int $daysOut) {
        [$business, $owner] = reminderBusiness();
        $deadline = reminderDeadline($business, $this->today->copy()->addDays($daysOut)->toDateString());

        $this->artisan('lien:send-deadline-reminders')->assertSuccessful();

        Notification::assertSentToTimes($owner, DeadlineApproaching::class, 1);
        Notification::assertSentTo($owner, DeadlineApproaching::class,
            fn (DeadlineApproaching $notification) => $notification->deadline->is($deadline) && $notification->daysRemaining === $daysOut);

        $log = LienNotificationLog::sole();
        expect($log->project_deadline_id)->toBe($deadline->id);
        expect($log->interval_days)->toBe($daysOut);
        expect($log->business_id)->toBe($business->id);
    })->with([14, 7, 3, 1, 0]);

    it('sends each reminder once, however often the hourly command runs', function () {
        [$business, $owner] = reminderBusiness();
        reminderDeadline($business, '2026-10-12');

        $this->artisan('lien:send-deadline-reminders');
        $this->travel(1)->hours();
        $this->artisan('lien:send-deadline-reminders');
        $this->travel(5)->hours();
        $this->artisan('lien:send-deadline-reminders');

        Notification::assertSentToTimes($owner, DeadlineApproaching::class, 1);
        expect(LienNotificationLog::count())->toBe(1);
    });

    it('walks one deadline through all five reminders as the days pass', function () {
        [$business, $owner] = reminderBusiness();
        reminderDeadline($business, '2026-10-19');

        // 15 daily runs, from 14 days out to the day after it was due.
        foreach (range(0, 15) as $day) {
            $this->travelTo(Carbon::parse('2026-10-05 14:00:00', 'UTC')->addDays($day));
            $this->artisan('lien:send-deadline-reminders');
        }

        Notification::assertSentToTimes($owner, DeadlineApproaching::class, 5);
        expect(LienNotificationLog::orderByDesc('interval_days')->pluck('interval_days')->all())->toBe([14, 7, 3, 1, 0]);
    });

    it('counts calendar days across a daylight-saving change', function () {
        // US clocks go back on Sunday 2026-11-01.
        $this->travelTo(Carbon::parse('2026-10-28 14:00:00', 'UTC'));

        [$business, $owner] = reminderBusiness();
        reminderDeadline($business, '2026-11-04');

        $this->artisan('lien:send-deadline-reminders');

        Notification::assertSentTo($owner, DeadlineApproaching::class, fn ($notification) => $notification->daysRemaining === 7);
    });
});

describe('each business in its own morning', function () {
    it('waits until 8am local time', function () {
        [$eastern, $easternOwner] = reminderBusiness('America/New_York');
        [$pacific, $pacificOwner] = reminderBusiness('America/Los_Angeles');
        reminderDeadline($eastern, '2026-10-12');
        reminderDeadline($pacific, '2026-10-12');

        // 10:00 in New York, 07:00 in Los Angeles.
        $this->artisan('lien:send-deadline-reminders');

        Notification::assertSentToTimes($easternOwner, DeadlineApproaching::class, 1);
        Notification::assertNotSentTo($pacificOwner, DeadlineApproaching::class);

        $this->travel(1)->hours();
        $this->artisan('lien:send-deadline-reminders');

        Notification::assertSentToTimes($pacificOwner, DeadlineApproaching::class, 1);
        Notification::assertSentToTimes($easternOwner, DeadlineApproaching::class, 1);
    });

    it("uses the business's own calendar day, not the server's", function () {
        // 03:30 UTC on Oct 6 is still the evening of Oct 5 in New York.
        $this->travelTo(Carbon::parse('2026-10-06 03:30:00', 'UTC'));

        [$business, $owner] = reminderBusiness('America/New_York');
        $dueInSevenLocalDays = reminderDeadline($business, '2026-10-12');
        reminderDeadline($business, '2026-10-13'); // 7 days from the UTC date, 8 from New York's

        $this->artisan('lien:send-deadline-reminders');

        Notification::assertSentToTimes($owner, DeadlineApproaching::class, 1);
        Notification::assertSentTo($owner, DeadlineApproaching::class,
            fn ($notification) => $notification->deadline->is($dueInSevenLocalDays));
    });
});

describe('skips steps the customer already handled', function () {
    it('says nothing once a filing for that step is paid, in any later state', function (FilingStatus $status) {
        [$business, $owner] = reminderBusiness();
        $deadline = reminderDeadline($business, '2026-10-12');

        LienFiling::factory()->forProject($deadline->project)->create([
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'status' => $status,
            'paid_at' => now(),
        ]);

        $this->artisan('lien:send-deadline-reminders');

        Notification::assertNothingSent();
        expect(LienNotificationLog::count())->toBe(0);
    })->with([
        'paid' => FilingStatus::Paid,
        'needs review' => FilingStatus::NeedsReview,
        'ready to file' => FilingStatus::ReadyToFile,
        'on hold' => FilingStatus::Hold,
        'awaiting client' => FilingStatus::AwaitingClient,
        'awaiting e-sign' => FilingStatus::AwaitingEsign,
        'in fulfillment' => FilingStatus::InFulfillment,
        'mailed' => FilingStatus::Mailed,
        'recorded' => FilingStatus::Recorded,
        'complete' => FilingStatus::Complete,
    ]);

    it('still reminds when the filing was only started or never paid for', function (FilingStatus $status) {
        [$business, $owner] = reminderBusiness();
        $deadline = reminderDeadline($business, '2026-10-12');

        LienFiling::factory()->forProject($deadline->project)->create([
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'status' => $status,
        ]);

        $this->artisan('lien:send-deadline-reminders');

        Notification::assertSentToTimes($owner, DeadlineApproaching::class, 1);
    })->with([
        'draft' => FilingStatus::Draft,
        'awaiting payment' => FilingStatus::AwaitingPayment,
    ]);

    it('says nothing for a step marked done elsewhere or not applicable', function () {
        [$business] = reminderBusiness();

        reminderDeadline($business, '2026-10-12', overrides: ['completed_externally_at' => now()->subDay()]);
        reminderDeadline($business, '2026-10-12', overrides: ['status' => 'not_applicable']);

        $this->artisan('lien:send-deadline-reminders');

        Notification::assertNothingSent();
    });
});

describe('skips jobs the customer backed out of', function () {
    it('says nothing about any step on a job once a filing there was refunded or canceled', function (FilingStatus $status) {
        [$business] = reminderBusiness();

        // The lien was bought and then refunded; its deadline is 7 days out,
        // and another step on the same job is 14 days out.
        $lien = reminderDeadline($business, '2026-10-12', 'mechanics_lien');
        $noi = LienDocumentType::where('slug', 'noi')->firstOrFail();
        LienProjectDeadline::factory()->forProject($lien->project)->create([
            'document_type_id' => $noi->id,
            // A project holds one deadline per rule.
            'deadline_rule_id' => LienDeadlineRule::where('document_type_id', $noi->id)->whereKeyNot($lien->deadline_rule_id)->value('id'),
            'due_date' => '2026-10-19',
        ]);

        LienFiling::factory()->forProject($lien->project)->create([
            'document_type_id' => $lien->document_type_id,
            'project_deadline_id' => $lien->id,
            'status' => $status,
            'paid_at' => now()->subWeeks(3),
        ]);

        $this->artisan('lien:send-deadline-reminders')->assertSuccessful();

        Notification::assertNothingSent();
        expect(LienNotificationLog::count())->toBe(0);
    })->with([
        'refunded' => FilingStatus::Refunded,
        'canceled' => FilingStatus::Canceled,
    ]);

    it('still says nothing after the refunded filing is deleted', function () {
        [$business] = reminderBusiness();
        $lien = reminderDeadline($business, '2026-10-12', 'mechanics_lien');

        LienFiling::factory()->forProject($lien->project)->create([
            'document_type_id' => $lien->document_type_id,
            'project_deadline_id' => $lien->id,
            'status' => FilingStatus::Refunded,
            'paid_at' => now()->subWeeks(3),
        ])->delete();

        $this->artisan('lien:send-deadline-reminders');

        Notification::assertNothingSent();
    });

    it("keeps reminding about the customer's other jobs", function () {
        [$business, $owner] = reminderBusiness();
        $refundedJob = reminderDeadline($business, '2026-10-12', 'mechanics_lien');
        $otherJob = reminderDeadline($business, '2026-10-12', 'mechanics_lien');

        LienFiling::factory()->forProject($refundedJob->project)->create([
            'document_type_id' => $refundedJob->document_type_id,
            'project_deadline_id' => $refundedJob->id,
            'status' => FilingStatus::Refunded,
            'paid_at' => now()->subWeeks(3),
        ]);

        $this->artisan('lien:send-deadline-reminders');

        Notification::assertSentToTimes($owner, DeadlineApproaching::class, 1);
        Notification::assertSentTo($owner, DeadlineApproaching::class,
            fn (DeadlineApproaching $notification) => $notification->deadline->is($otherJob));
    });
});

describe('who gets it', function () {
    it('goes to every member except bounced addresses and people who turned it off', function () {
        [$business, $owner] = reminderBusiness();

        $teammate = User::factory()->create();
        $bounced = User::factory()->create(['email_bounced_at' => now()]);
        $optedOut = User::factory()->create();
        $optedOutOfEverything = User::factory()->create(['unsubscribed_from_all_emails_at' => now()]);

        foreach ([$teammate, $bounced, $optedOut, $optedOutOfEverything] as $member) {
            $business->users()->attach($member, ['role' => 'member']);
        }

        EmailUnsubscribe::unsubscribe($optedOut, EmailUnsubscribe::CATEGORY_DEADLINE_REMINDERS);

        reminderDeadline($business, '2026-10-12');

        $this->artisan('lien:send-deadline-reminders');

        Notification::assertSentToTimes($owner, DeadlineApproaching::class, 1);
        Notification::assertSentToTimes($teammate, DeadlineApproaching::class, 1);
        Notification::assertNotSentTo($bounced, DeadlineApproaching::class);
        Notification::assertNotSentTo($optedOut, DeadlineApproaching::class);
        Notification::assertNotSentTo($optedOutOfEverything, DeadlineApproaching::class);
    });

    it('lists deadline reminders on the email preferences page', function () {
        expect(EmailUnsubscribe::$categories)->toHaveKey(EmailUnsubscribe::CATEGORY_DEADLINE_REMINDERS);
    });
});

describe('safety switches', function () {
    it('sends nothing at all when a run would send more than the limit', function () {
        config(['lien.notifications.max_emails_per_run' => 3]);
        Exceptions::fake();

        foreach (range(1, 4) as $i) {
            [$business] = reminderBusiness();
            reminderDeadline($business, '2026-10-12');
        }

        $this->artisan('lien:send-deadline-reminders')
            ->expectsOutputToContain('would send 4 emails in one run (limit 3). Nothing was sent.')
            ->assertFailed();

        Notification::assertNothingSent();
        expect(LienNotificationLog::count())->toBe(0);

        Exceptions::assertReported(fn (RuntimeException $e) => str_contains($e->getMessage(), 'Nothing was sent'));
    });

    it('sends normally at the limit', function () {
        config(['lien.notifications.max_emails_per_run' => 3]);

        foreach (range(1, 3) as $i) {
            [$business] = reminderBusiness();
            reminderDeadline($business, '2026-10-12');
        }

        $this->artisan('lien:send-deadline-reminders')->assertSuccessful();

        Notification::assertCount(3);
    });

    it('can be switched off from the environment', function () {
        config(['lien.notifications.reminders_enabled' => false]);

        [$business] = reminderBusiness();
        reminderDeadline($business, '2026-10-12');

        $this->artisan('lien:send-deadline-reminders')->assertSuccessful();

        Notification::assertNothingSent();
        expect(LienNotificationLog::count())->toBe(0);
    });

    it('lists what a dry run would send without sending or logging it', function () {
        [$business] = reminderBusiness();
        reminderDeadline($business, '2026-10-12');

        $this->artisan('lien:send-deadline-reminders --dry-run')
            ->expectsOutputToContain('Would send: Preliminary Notice (Market Street Lofts), 7 day(s) out, 1 recipient(s)')
            ->expectsOutputToContain('Dry run: 1 email(s) for 1 deadline(s) would be sent.')
            ->assertSuccessful();

        Notification::assertNothingSent();
        expect(LienNotificationLog::count())->toBe(0);

        // The real run afterwards still sends it.
        $this->artisan('lien:send-deadline-reminders');
        Notification::assertCount(1);
    });
});

describe('the email', function () {
    it('says when it is due in plain words, and what is at stake', function () {
        [$business, $owner] = reminderBusiness();
        $deadline = reminderDeadline($business, '2026-10-12');

        $mail = (new DeadlineApproaching($deadline, 7))->toMail($owner);

        expect($mail->subject)->toBe('Preliminary Notice due in 7 days: Market Street Lofts');
        expect($mail->greeting)->toBe('Hi Dana,');
        expect($mail->introLines[0])->toBe('Your Preliminary Notice for **Market Street Lofts** is due in 7 days, on October 12, 2026.');
        expect($mail->introLines[1])->toBe('If you miss this deadline, you can lose your lien rights on this job.');
        expect($mail->actionText)->toBe('Start my Preliminary Notice');
        expect($mail->actionUrl)->toBe(route('lien.filings.start', ['project' => $deadline->project, 'deadline' => $deadline]));
        expect(implode(' ', $mail->outroLines))
            ->toContain('We can prepare and send it for you.')
            ->toContain('please double-check it')
            ->toContain('Turn off deadline reminders')
            ->toContain('/email/preferences/'.$owner->id);
    });

    it('says "tomorrow" and "today", never "overdue"', function () {
        [$business, $owner] = reminderBusiness();
        $deadline = reminderDeadline($business, '2026-10-05', 'mechanics_lien');

        $tomorrow = (new DeadlineApproaching($deadline, 1))->toMail($owner);
        $today = (new DeadlineApproaching($deadline, 0))->toMail($owner);

        expect($tomorrow->subject)->toBe('Mechanics Lien due tomorrow: Market Street Lofts');
        expect($today->subject)->toBe('Mechanics Lien due today: Market Street Lofts');
        expect($today->introLines[0])->toContain('is due today, on October 5, 2026.');
        expect(strtolower($today->subject.' '.implode(' ', $today->introLines)))->not->toContain('overdue');

        // With a day or less left, no promise that we can turn it around in time.
        expect(implode(' ', $today->outroLines))->toContain('Time is short')->not->toContain('We can prepare and send it for you');
        expect(implode(' ', $tomorrow->outroLines))->toContain('Time is short');
    });

    it('leaves out the lien-rights warning for a lien release', function () {
        [$business, $owner] = reminderBusiness();
        $deadline = reminderDeadline($business, '2026-10-12', 'lien_release');

        $mail = (new DeadlineApproaching($deadline, 7))->toMail($owner);

        expect(implode(' ', $mail->introLines))->not->toContain('lose your lien rights');
    });
});
