<?php

use App\Console\Commands\SendUnpaidRegistrationCheckIns;
use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Enums\PaymentStatus;
use App\Mail\UnpaidRegistrationCheckIn;
use App\Models\EmailUnsubscribe;
use App\Models\Payment;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/*
| email:unpaid-registration-check-in. A one-off personal note from the owner
| to people who started a sales tax registration, reached the payment step
| and never paid: once per person, never to anyone who has since paid.
*/

if (! function_exists('checkInCustomer')) {
    /** @return array{0: Business, 1: User} */
    function checkInCustomer(array $user = []): array
    {
        $business = Business::factory()->create();
        $owner = User::factory()->create(array_merge(['first_name' => 'dana', 'email' => 'dana@example.com'], $user));
        $business->users()->attach($owner, ['role' => 'owner']);

        return [$business, $owner];
    }
}

if (! function_exists('checkInRegistration')) {
    /**
     * A sales tax registration started at $startedAt (UTC) by $creator. With
     * a payment status, the person opened the card form and that is where
     * the payment row stands; null means they never reached payment.
     */
    function checkInRegistration(Business $business, ?User $creator, string $startedAt, ?PaymentStatus $payment = PaymentStatus::Initiated, array $application = []): FormApplication
    {
        $record = FormApplication::create(array_merge([
            'business_id' => $business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['TX'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $creator?->id,
        ], $application));
        $record->forceFill(['created_at' => $startedAt])->saveQuietly();

        if ($payment) {
            Payment::factory()->forPurchasable($record)->create([
                'status' => $payment,
                'amount_cents' => 19900,
                'paid_at' => $payment === PaymentStatus::Succeeded ? $startedAt : null,
            ]);
        }

        return $record;
    }
}

beforeEach(function () {
    Mail::fake();
    config(['mail.from.address' => 'contact@eregister.com']);

    $this->travelTo(Carbon::parse('2026-10-15 14:00:00', 'UTC'));
});

it('emails someone who reached the card form and did not pay', function () {
    [$business, $dana] = checkInCustomer();
    checkInRegistration($business, $dana, '2026-10-10 15:00:00');

    $this->artisan('email:unpaid-registration-check-in')->assertSuccessful();

    Mail::assertQueued(UnpaidRegistrationCheckIn::class, function (UnpaidRegistrationCheckIn $mail) use ($dana) {
        return $mail->hasTo($dana->email)
            && $mail->hasFrom('contact@eregister.com', 'Major from eRegister')
            && $mail->hasReplyTo('contact@eregister.com')
            && $mail->hasSubject('Quick question about your Texas registration');
    });
    expect(SentEmail::where('user_id', $dana->id)->where('email_type', SendUnpaidRegistrationCheckIns::EMAIL_TYPE)->count())->toBe(1);
});

it('writes a short personal note in plain text', function () {
    [, $dana] = checkInCustomer();

    $text = (new UnpaidRegistrationCheckIn($dana, ['TX', 'FL']))->render();

    expect($text)
        ->toContain('Hi Dana,')
        ->toContain("I'm Major, the owner of eRegister.")
        ->toContain('I saw you started your Texas and Florida sales tax registration with us.')
        ->toContain('Was it more than you expected?')
        ->toContain('Just reply to this email.')
        ->not->toContain('<');
    expect((new UnpaidRegistrationCheckIn($dana, ['TX', 'FL', 'CA']))->render())
        ->toContain('your sales tax registration for 3 states');
});

it('sends once per person, even with two unpaid registrations or a second run', function () {
    [$business, $dana] = checkInCustomer();
    checkInRegistration($business, $dana, '2026-10-08 15:00:00');
    checkInRegistration($business, $dana, '2026-10-10 15:00:00', application: ['selected_states' => ['FL']]);

    $this->artisan('email:unpaid-registration-check-in')->assertSuccessful();
    $this->artisan('email:unpaid-registration-check-in')->assertSuccessful();

    Mail::assertQueuedCount(1);
});

it('skips people who never reached payment, paid, or were refunded', function (?PaymentStatus $payment) {
    [$business, $dana] = checkInCustomer();
    checkInRegistration($business, $dana, '2026-10-10 15:00:00', $payment);

    $this->artisan('email:unpaid-registration-check-in')->assertSuccessful();

    Mail::assertNothingQueued();
})->with([
    'never reached payment' => [null],
    'paid' => [PaymentStatus::Succeeded],
    'refunded' => [PaymentStatus::Refunded],
]);

it('skips a person whose business paid for a registration later', function () {
    [$business, $dana] = checkInCustomer();
    checkInRegistration($business, $dana, '2026-10-08 15:00:00');
    checkInRegistration($business, $dana, '2026-10-09 15:00:00', PaymentStatus::Succeeded, ['paid_at' => '2026-10-09 15:05:00']);

    $this->artisan('email:unpaid-registration-check-in')->assertSuccessful();

    Mail::assertNothingQueued();
});

it('waits a day, and looks back 60 days unless told otherwise', function () {
    [$business, $dana] = checkInCustomer();
    checkInRegistration($business, $dana, '2026-10-15 02:00:00');
    [$oldBusiness, $lee] = checkInCustomer(['first_name' => 'Lee', 'email' => 'lee@example.com']);
    checkInRegistration($oldBusiness, $lee, '2026-08-01 15:00:00');

    $this->artisan('email:unpaid-registration-check-in')->assertSuccessful();
    Mail::assertNothingQueued();

    $this->artisan('email:unpaid-registration-check-in', ['--since' => '2026-07-01'])->assertSuccessful();
    Mail::assertQueued(UnpaidRegistrationCheckIn::class, fn ($mail) => $mail->hasTo('lee@example.com'));
    Mail::assertQueuedCount(1);
});

it('skips test accounts, bounced addresses and people who turned reminders off', function (string $case) {
    [$business, $dana] = checkInCustomer($case === 'test account' ? ['email' => 'dana@test.test'] : []);
    checkInRegistration($business, $dana, '2026-10-10 15:00:00');

    match ($case) {
        'bounced' => $dana->forceFill(['email_bounced_at' => now()])->save(),
        'order reminders off' => EmailUnsubscribe::unsubscribe($dana, EmailUnsubscribe::CATEGORY_ABANDON_CHECKOUT),
        'marketing off' => EmailUnsubscribe::unsubscribe($dana, EmailUnsubscribe::CATEGORY_MARKETING),
        default => null,
    };

    $this->artisan('email:unpaid-registration-check-in')->assertSuccessful();

    Mail::assertNothingQueued();
})->with(['test account', 'bounced', 'order reminders off', 'marketing off']);

it('lists who would be emailed on a dry run without sending or logging', function () {
    [$business, $dana] = checkInCustomer();
    checkInRegistration($business, $dana, '2026-10-10 15:00:00');

    $this->artisan('email:unpaid-registration-check-in', ['--dry-run' => true])
        ->expectsOutputToContain('Would email user #'.$dana->id)
        ->expectsOutputToContain('Dry run: 1 person(s)')
        ->assertSuccessful();

    Mail::assertNothingQueued();
    expect(SentEmail::count())->toBe(0);
});
