<?php

use App\Console\Commands\SendReviewRequests;
use App\Domains\Business\Models\Business;
use App\Domains\Forms\Enums\FormApplicationStateAdminStatus;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Enums\PaymentStatus;
use App\Mail\ReviewRequest;
use App\Models\EmailUnsubscribe;
use App\Models\Payment;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/*
| email:send-review-requests. A customer is asked for a Google review 2 days
| after their lien is recorded or a state approves their sales tax
| registration: once per person, ever, and never if they paid for anything
| before the start date (past clients are the owner's to ask).
*/

if (! function_exists('reviewRequestCustomer')) {
    /**
     * A business with one owner.
     *
     * @return array{0: Business, 1: User}
     */
    function reviewRequestCustomer(array $user = []): array
    {
        $business = Business::factory()->create();
        $owner = User::factory()->create(array_merge(['first_name' => 'Adam'], $user));
        $business->users()->attach($owner, ['role' => 'owner']);

        return [$business, $owner];
    }
}

if (! function_exists('reviewRequestLien')) {
    /** A lien filing paid for after the start date, recorded at $recordedAt (UTC). */
    function reviewRequestLien(Business $business, ?User $creator, ?string $recordedAt, FilingStatus $status = FilingStatus::Recorded, array $overrides = []): LienFiling
    {
        $project = LienProject::factory()->forBusiness($business)->create();

        return LienFiling::factory()->forProject($project)->create(array_merge([
            'status' => $status,
            'created_by_user_id' => $creator?->id,
            'paid_at' => '2026-09-28 15:00:00',
            'recorded_at' => $recordedAt,
        ], $overrides));
    }
}

if (! function_exists('reviewRequestApproval')) {
    /**
     * A registration paid for after the start date with one state card,
     * approved at $approvedAt (UTC), or still New when that is null.
     */
    function reviewRequestApproval(Business $business, ?User $creator, ?string $approvedAt, string $formType = 'sales_tax_permit', array $application = []): FormApplicationState
    {
        $record = FormApplication::create(array_merge([
            'business_id' => $business->id,
            'form_type' => $formType,
            'definition_version' => 1,
            'selected_states' => ['CA'],
            'status' => 'submitted',
            'current_phase' => 'review',
            'core_data' => [],
            'created_by_user_id' => $creator?->id,
            'paid_at' => '2026-09-28 15:00:00',
            'submitted_at' => '2026-09-28 16:00:00',
            'locked_at' => '2026-09-28 16:00:00',
        ], $application));

        return FormApplicationState::create(array_merge([
            'form_application_id' => $record->id,
            'state_code' => 'CA',
            'status' => 'complete',
            'completed_at' => '2026-09-28 16:00:00',
            'data' => [],
        ], $approvedAt === null ? [] : [
            'current_admin_status' => FormApplicationStateAdminStatus::Approved,
            'current_admin_status_changed_at' => $approvedAt,
        ]));
    }
}

beforeEach(function () {
    Mail::fake();

    config([
        'review_requests.starts_at' => '2026-09-27',
        'review_requests.delay_days' => 2,
    ]);

    // Wednesday 2026-10-07, 10:00 in New York: the daily run.
    $this->travelTo(Carbon::parse('2026-10-07 14:00:00', 'UTC'));
});

describe('when it asks', function () {
    it('asks the customer 2 days after their lien is recorded', function () {
        [$business, $adam] = reviewRequestCustomer();
        $filing = reviewRequestLien($business, $adam, null, FilingStatus::SubmittedForRecording);

        // Recorded Monday at 9:00 Eastern.
        $this->travelTo(Carbon::parse('2026-10-05 13:00:00', 'UTC'));
        $filing->transitionTo(FilingStatus::Recorded);

        // Tuesday's run is too soon.
        $this->travelTo(Carbon::parse('2026-10-06 14:00:00', 'UTC'));
        $this->artisan('email:send-review-requests')->assertSuccessful();
        Mail::assertNothingQueued();

        $this->travelTo(Carbon::parse('2026-10-07 14:00:00', 'UTC'));
        $this->artisan('email:send-review-requests')->assertSuccessful();

        Mail::assertQueued(ReviewRequest::class, fn (ReviewRequest $mail) => $mail->hasTo($adam->email) && $mail->recipient->is($adam));
        expect(SentEmail::where('email_type', SendReviewRequests::EMAIL_TYPE)
            ->where('user_id', $adam->id)
            ->where('emailable_type', 'user')
            ->where('emailable_id', $adam->id)
            ->exists())->toBeTrue();
    });

    it('waits two full days', function () {
        [$business, $adam] = reviewRequestCustomer();

        // Recorded Monday at 11:00 Eastern, so Wednesday's 10:00 run is an hour early.
        reviewRequestLien($business, $adam, '2026-10-05 15:00:00');

        $this->artisan('email:send-review-requests')->assertSuccessful();
        Mail::assertNothingQueued();

        $this->travelTo(Carbon::parse('2026-10-08 14:00:00', 'UTC'));
        $this->artisan('email:send-review-requests')->assertSuccessful();
        Mail::assertQueued(ReviewRequest::class, 1);
    });

    it('asks the customer 2 days after a state approves their sales tax registration', function () {
        [$business, $adam] = reviewRequestCustomer();
        $state = reviewRequestApproval($business, $adam, null);

        // Approved Monday at 9:00 Eastern.
        $this->travelTo(Carbon::parse('2026-10-05 13:00:00', 'UTC'));
        $state->transitionAdminStatusTo(FormApplicationStateAdminStatus::SubmittedToState);
        $state->transitionAdminStatusTo(FormApplicationStateAdminStatus::Approved);

        $this->travelTo(Carbon::parse('2026-10-06 14:00:00', 'UTC'));
        $this->artisan('email:send-review-requests')->assertSuccessful();
        Mail::assertNotQueued(ReviewRequest::class);

        $this->travelTo(Carbon::parse('2026-10-07 14:00:00', 'UTC'));
        $this->artisan('email:send-review-requests')->assertSuccessful();
        Mail::assertQueued(ReviewRequest::class, fn (ReviewRequest $mail) => $mail->hasTo($adam->email));
    });

    it('still asks when the recorded lien has moved on without a problem', function (FilingStatus $status) {
        [$business, $adam] = reviewRequestCustomer();
        reviewRequestLien($business, $adam, '2026-10-02 15:00:00', $status);

        $this->artisan('email:send-review-requests')->assertSuccessful();

        Mail::assertQueued(ReviewRequest::class, 1);
    })->with([FilingStatus::Recorded, FilingStatus::Mailed, FilingStatus::Complete]);

    it('does not ask when the recorded lien went on hold, back to review, or was canceled or refunded', function (FilingStatus $status) {
        [$business, $adam] = reviewRequestCustomer();
        reviewRequestLien($business, $adam, '2026-10-02 15:00:00', $status);

        $this->artisan('email:send-review-requests')->assertSuccessful();

        Mail::assertNothingQueued();
    })->with([FilingStatus::Hold, FilingStatus::NeedsReview, FilingStatus::Canceled, FilingStatus::Refunded]);

    it('does not ask about a deleted filing', function () {
        [$business, $adam] = reviewRequestCustomer();
        reviewRequestLien($business, $adam, '2026-10-02 15:00:00')->delete();

        $this->artisan('email:send-review-requests')->assertSuccessful();

        Mail::assertNothingQueued();
    });

    it('does not ask about an order more than two weeks old, so downtime cannot end in a burst', function () {
        [$business, $adam] = reviewRequestCustomer();
        reviewRequestLien($business, $adam, '2026-10-01 15:00:00');

        $this->travelTo(Carbon::parse('2026-10-16 14:00:00', 'UTC'));
        $this->artisan('email:send-review-requests')->assertSuccessful();

        Mail::assertNothingQueued();
    });

    it('runs every morning at 10 Eastern', function () {
        $this->artisan('schedule:list')->assertSuccessful();

        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains((string) $event->command, 'email:send-review-requests'));

        expect($event)->not->toBeNull()
            ->and($event->expression)->toBe('0 10 * * *')
            ->and($event->timezone)->toBe('America/New_York');
    });
});

describe('who it asks', function () {
    it('asks each person once, ever, whatever they order next', function () {
        [$business, $adam] = reviewRequestCustomer();
        reviewRequestLien($business, $adam, '2026-10-01 15:00:00');
        reviewRequestLien($business, $adam, '2026-10-02 15:00:00');
        reviewRequestApproval($business, $adam, '2026-10-03 15:00:00');

        $this->artisan('email:send-review-requests')->assertSuccessful();

        reviewRequestLien($business, $adam, '2026-10-06 15:00:00');
        $this->travelTo(Carbon::parse('2026-10-09 14:00:00', 'UTC'));
        $this->artisan('email:send-review-requests')->assertSuccessful();

        Mail::assertQueued(ReviewRequest::class, 1);
        expect(SentEmail::where('email_type', SendReviewRequests::EMAIL_TYPE)->count())->toBe(1);
    });

    it('asks the person who placed the order, or the business\'s first user when no one did', function () {
        [$business, $adam] = reviewRequestCustomer();
        $bea = User::factory()->create(['first_name' => 'Bea']);
        $business->users()->attach($bea, ['role' => 'member']);
        reviewRequestLien($business, $bea, '2026-10-02 15:00:00');

        [$otherBusiness, $cal] = reviewRequestCustomer(['first_name' => 'Cal']);
        reviewRequestLien($otherBusiness, null, '2026-10-02 16:00:00');

        $this->artisan('email:send-review-requests')->assertSuccessful();

        Mail::assertQueued(ReviewRequest::class, 2);
        Mail::assertQueued(ReviewRequest::class, fn (ReviewRequest $mail) => $mail->hasTo($bea->email));
        Mail::assertQueued(ReviewRequest::class, fn (ReviewRequest $mail) => $mail->hasTo($cal->email));
        Mail::assertNotQueued(ReviewRequest::class, fn (ReviewRequest $mail) => $mail->hasTo($adam->email));
    });

    it('never asks a past client, even about a lien recorded after the start date', function (string $pastOrder) {
        [$business, $adam] = reviewRequestCustomer();
        $before = '2026-09-20 15:00:00';

        match ($pastOrder) {
            'a payment' => Payment::factory()->succeeded()->create(['business_id' => $business->id, 'paid_at' => $before]),
            'a payment later refunded' => Payment::factory()->create([
                'business_id' => $business->id,
                'status' => PaymentStatus::Refunded,
                'paid_at' => $before,
                'refunded_at' => '2026-09-22 15:00:00',
            ]),
            'a lien filing with no payment row' => reviewRequestLien($business, $adam, null, FilingStatus::Complete, ['paid_at' => $before]),
            'a registration with no payment row' => reviewRequestApproval($business, $adam, null, application: ['paid_at' => $before]),
            'an order under another of their businesses' => (function () use ($adam, $before) {
                $oldBusiness = Business::factory()->create();
                $oldBusiness->users()->attach($adam, ['role' => 'owner']);
                Payment::factory()->succeeded()->create(['business_id' => $oldBusiness->id, 'paid_at' => $before]);
            })(),
        };

        reviewRequestLien($business, $adam, '2026-10-02 15:00:00');

        $this->artisan('email:send-review-requests')->assertSuccessful();

        Mail::assertNothingQueued();
    })->with([
        'a payment',
        'a payment later refunded',
        'a lien filing with no payment row',
        'a registration with no payment row',
        'an order under another of their businesses',
    ]);

    it('ignores liens recorded and states approved before the start date', function () {
        // Nothing paid before the start date (comped orders), but recorded and approved before it.
        [$business, $adam] = reviewRequestCustomer();
        reviewRequestLien($business, $adam, '2026-09-26 15:00:00', overrides: ['paid_at' => null]);

        [$otherBusiness, $bea] = reviewRequestCustomer(['first_name' => 'Bea']);
        reviewRequestApproval($otherBusiness, $bea, '2026-09-26 15:00:00', application: ['paid_at' => null]);

        $this->travelTo(Carbon::parse('2026-09-29 14:00:00', 'UTC'));
        $this->artisan('email:send-review-requests')->assertSuccessful();

        Mail::assertNothingQueued();
    });

    it('skips people it should not email', function (string $kind) {
        [$business, $adam] = reviewRequestCustomer($kind === 'a test account' ? ['email' => 'adam@test.test'] : []);

        match ($kind) {
            'a bounced address' => $adam->forceFill(['email_bounced_at' => now()])->save(),
            'unsubscribed from all email' => $adam->forceFill(['unsubscribed_from_all_emails_at' => now()])->save(),
            'unsubscribed from marketing' => EmailUnsubscribe::unsubscribe($adam, EmailUnsubscribe::CATEGORY_MARKETING),
            'a test account' => null,
        };

        reviewRequestLien($business, $adam, '2026-10-02 15:00:00');

        $this->artisan('email:send-review-requests')->assertSuccessful();

        Mail::assertNothingQueued();
        expect(SentEmail::where('email_type', SendReviewRequests::EMAIL_TYPE)->exists())->toBeFalse();
    })->with(['a bounced address', 'unsubscribed from all email', 'unsubscribed from marketing', 'a test account']);

    it('skips refunded sales tax orders and LLC approvals', function () {
        [$business, $adam] = reviewRequestCustomer();
        $state = reviewRequestApproval($business, $adam, '2026-10-02 15:00:00');
        Payment::factory()->forPurchasable($state->application)->create([
            'status' => PaymentStatus::Refunded,
            'paid_at' => '2026-09-28 15:00:00',
            'refunded_at' => '2026-10-03 15:00:00',
        ]);

        [$otherBusiness, $bea] = reviewRequestCustomer(['first_name' => 'Bea']);
        reviewRequestApproval($otherBusiness, $bea, '2026-10-02 15:00:00', 'llc');

        $this->artisan('email:send-review-requests')->assertSuccessful();

        Mail::assertNothingQueued();
    });

    it('lists who would be asked on a dry run, without sending or recording anything', function () {
        [$business, $adam] = reviewRequestCustomer();
        reviewRequestLien($business, $adam, '2026-10-02 15:00:00');

        $this->artisan('email:send-review-requests', ['--dry-run' => true])
            ->expectsOutputToContain("Would ask user #{$adam->id} ({$adam->email})")
            ->expectsOutputToContain('Dry run: 1 customer(s) would be asked for a review.')
            ->assertSuccessful();

        Mail::assertNothingQueued();
        expect(SentEmail::count())->toBe(0);
    });
});

describe('the email', function () {
    it('is the owner\'s note, word for word, with no footer', function () {
        $mail = new ReviewRequest(User::factory()->make(['first_name' => 'Adam']));

        $mail->assertHasSubject('Hey Adam');

        expect($mail->render())->toBe(
            "Hey Adam, if you wouldn't mind leaving us a review, I would be very grateful. https://g.page/r/CTSM6sX9m7MNEAI/review\n"
            ."\n"
            ."Thanks,\n"
            ."Major\n"
            ."eRegister\n"
        );
    });

    it('greets the customer by first name, or "there" without one', function (?string $firstName, string $greeting) {
        $mail = new ReviewRequest(User::factory()->make(['first_name' => $firstName]));

        $mail->assertHasSubject("Hey {$greeting}");
        expect($mail->render())->toStartWith("Hey {$greeting}, if you wouldn't mind");
    })->with([
        'a first name' => ['Adam', 'Adam'],
        'a lowercase first name' => ['adam', 'Adam'],
        'a blank first name' => ['  ', 'there'],
        'no first name' => [null, 'there'],
    ]);
});
