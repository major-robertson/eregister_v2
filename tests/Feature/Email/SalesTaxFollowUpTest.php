<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\SalesTax\SalesTaxFollowUp;
use App\Mail\SalesTaxStartedReminder;
use App\Models\EmailSequence;
use App\Models\EmailUnsubscribe;
use App\Models\Price;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/*
| sales_tax_started (EREG-71): someone who signed up through a sales tax door
| and has not picked a state gets "get your permit" reminders an hour later,
| then a day later, then three days after that, until they pick a state (the
| order screen's own reminders take over) or opt out of order reminders.
*/

if (! function_exists('salesTaxFollowUpRegister')) {
    /** Register through the real endpoint, as a visitor who came from $landingPath. */
    function salesTaxFollowUpRegister(\Illuminate\Foundation\Testing\TestCase $test, string $email, ?string $landingPath, ?array $intent = null): User
    {
        $test->withSession(array_filter([
            'signup_landing_path' => $landingPath,
            'signup_intent' => $intent,
        ]))->post(route('register.store'), [
            'first_name' => 'Sam',
            'last_name' => 'Seller',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        return User::where('email', $email)->firstOrFail();
    }
}

if (! function_exists('salesTaxFollowUpBusiness')) {
    function salesTaxFollowUpBusiness(User $user, string $state = 'TX'): Business
    {
        $business = Business::factory()->create([
            'onboarding_completed_at' => now(),
            'business_address' => ['line1' => '1 Main St', 'city' => 'Springfield', 'state' => $state, 'zip' => '62701'],
        ]);
        $business->users()->attach($user, ['role' => 'owner']);

        return $business;
    }
}

if (! function_exists('salesTaxFollowUpDue')) {
    function salesTaxFollowUpDue(User $user, ?string $state = null): EmailSequence
    {
        return EmailSequence::create([
            'user_id' => $user->id,
            'sequence_type' => 'sales_tax_started',
            'sequenceable_type' => $user->getMorphClass(),
            'sequenceable_id' => $user->id,
            'customer_type' => 'new',
            'resume_url' => route('sales-tax.registrations.start', array_filter(['state' => $state])),
            'next_send_at' => now()->subMinute(),
        ]);
    }
}

if (! function_exists('salesTaxFollowUpApplication')) {
    /** The draft that picking a state creates on the way to the order screen. */
    function salesTaxFollowUpApplication(Business $business, User $user): FormApplication
    {
        return FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['TX'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $user->id,
        ]);
    }
}

beforeEach(function () {
    config(['cashier.secret' => null]);
    // Whole seconds: timestamps come back from the database without microseconds.
    $this->freezeSecond();

    Price::updateOrCreate(
        ['product_family' => 'tax', 'product_key' => 'sales_tax_permit', 'variant_key' => 'per_state', 'billing_type' => 'one_time'],
        ['amount_cents' => 19900, 'currency' => 'usd', 'active' => true],
    );
});

describe('who gets the follow-ups', function () {
    it('starts them when a visitor signs up through a sales tax door', function () {
        Mail::fake();

        $user = salesTaxFollowUpRegister($this, 'sam@example.com', '/lp/sales-tax/tx', [
            'product' => 'sales-tax', 'intent' => null, 'state' => 'TX', 'source' => null,
        ]);

        $sequence = EmailSequence::where('sequence_type', 'sales_tax_started')->sole();

        expect($sequence->user_id)->toBe($user->id)
            ->and($sequence->sequenceable_type)->toBe('user')
            ->and($sequence->resume_url)->toBe(route('sales-tax.registrations.start', ['state' => 'TX']))
            ->and($sequence->next_send_at->equalTo(now()->addHour()))->toBeTrue();
    });

    it('starts them for a resale page visitor who chose a sales tax door', function () {
        Mail::fake();

        salesTaxFollowUpRegister($this, 'dee@example.com', '/lp/resale-certificate', [
            'product' => 'sales-tax', 'intent' => 'not-sure', 'state' => null, 'source' => null,
        ]);

        expect(EmailSequence::where('sequence_type', 'sales_tax_started')->sole()->resume_url)
            ->toBe(route('sales-tax.registrations.start'))
            ->and(EmailSequence::where('sequence_type', 'resale_started')->exists())->toBeFalse();
    });

    it('leaves out a sales tax page visitor who chose the certificate door', function () {
        Mail::fake();

        salesTaxFollowUpRegister($this, 'cora@example.com', '/lp/sales-tax/tx', [
            'product' => 'resale-cert', 'intent' => null, 'state' => 'TX', 'source' => null,
        ]);

        expect(EmailSequence::where('sequence_type', 'sales_tax_started')->exists())->toBeFalse()
            ->and(EmailSequence::where('sequence_type', 'resale_started')->exists())->toBeTrue();
    });

    it('falls back to the landing page when no door was recorded', function () {
        Mail::fake();

        $user = salesTaxFollowUpRegister($this, 'tom@example.com', '/sales-tax-registration');

        expect(EmailSequence::where('sequence_type', 'sales_tax_started')->sole()->user_id)->toBe($user->id);
    });

    it('does nothing for sign-ups from other pages', function () {
        Mail::fake();

        salesTaxFollowUpRegister($this, 'larry@example.com', '/llc');

        expect(EmailSequence::where('sequence_type', 'sales_tax_started')->exists())->toBeFalse();
    });

    it('skips someone who already has a sales tax application', function () {
        $user = User::factory()->create(['signup_landing_path' => '/lp/sales-tax/tx']);
        salesTaxFollowUpApplication(salesTaxFollowUpBusiness($user), $user);

        expect(SalesTaxFollowUp::start($user))->toBeNull()
            ->and(EmailSequence::where('sequence_type', 'sales_tax_started')->exists())->toBeFalse();
    });

    it('never starts a second series for the same person', function () {
        $user = User::factory()->create(['signup_landing_path' => '/lp/sales-tax/tx']);
        $first = SalesTaxFollowUp::start($user);
        $first->suppress('all_steps_sent');

        $again = SalesTaxFollowUp::start($user, salesTaxFollowUpBusiness($user));

        expect($again->id)->toBe($first->id)
            ->and($again->fresh()->suppressed_at)->not->toBeNull()
            ->and(EmailSequence::where('sequence_type', 'sales_tax_started')->count())->toBe(1);
    });
});

describe('the emails', function () {
    it('sends the first one naming the state they picked, with a link that preselects it', function () {
        Mail::fake();

        $user = User::factory()->create(['first_name' => 'Sam', 'signup_landing_path' => '/lp/sales-tax/tx']);
        $sequence = salesTaxFollowUpDue($user, 'TX');

        $this->artisan('email:process-sequences')->assertSuccessful();

        Mail::assertQueued(SalesTaxStartedReminder::class, function (SalesTaxStartedReminder $mail) {
            $html = $mail->render();

            return $mail->step === 1
                && $mail->envelope()->subject === 'Get your Texas sales tax permit'
                && str_contains($html, 'You signed up to register for sales tax in Texas')
                && str_contains($html, '$199 per state')
                && str_contains($html, route('sales-tax.registrations.start', ['state' => 'TX']));
        });

        expect(SentEmail::where('email_type', 'sales_tax_started_step_1')->count())->toBe(1)
            ->and($sequence->fresh()->next_send_at->equalTo(now()->addDay()))->toBeTrue();
    });

    it('names the state agency in the second one', function () {
        $user = User::factory()->create(['signup_landing_path' => '/lp/sales-tax/tx']);
        $mail = new SalesTaxStartedReminder(salesTaxFollowUpDue($user, 'TX'), 2);

        expect($mail->envelope()->subject)->toBe('We file your Texas sales tax registration for you')
            ->and($mail->render())->toContain('file it with the Texas Comptroller of Public Accounts for you');
    });

    it('falls back to the business state', function () {
        $user = User::factory()->create(['signup_landing_path' => '/lp/sales-tax']);
        salesTaxFollowUpBusiness($user, 'IL');

        expect((new SalesTaxStartedReminder(salesTaxFollowUpDue($user), 3))->envelope()->subject)
            ->toBe('Still need your Illinois sales tax permit?');
    });

    it('never names a state without sales tax', function () {
        $user = User::factory()->create(['signup_landing_path' => '/lp/resale-certificate']);
        salesTaxFollowUpBusiness($user, 'DE');
        $mail = new SalesTaxStartedReminder(salesTaxFollowUpDue($user), 1);

        expect($mail->envelope()->subject)->toBe('Get your sales tax permit')
            ->and($mail->render())->toContain('You signed up to register for sales tax, but you');
    });
});

describe('what stops them', function () {
    it('stops once they pick a state', function () {
        Mail::fake();

        $user = User::factory()->create(['signup_landing_path' => '/lp/sales-tax/tx']);
        $sequence = salesTaxFollowUpDue($user, 'TX');
        salesTaxFollowUpApplication(salesTaxFollowUpBusiness($user), $user);

        $this->artisan('email:process-sequences')->assertSuccessful();

        expect($sequence->fresh()->suppression_reason)->toBe('order_started');
        Mail::assertNotQueued(SalesTaxStartedReminder::class);
    });

    it('stops when they turn off order reminders', function () {
        Mail::fake();

        $user = User::factory()->create(['signup_landing_path' => '/lp/sales-tax/tx']);
        $sequence = salesTaxFollowUpDue($user, 'TX');
        EmailUnsubscribe::unsubscribe($user, EmailUnsubscribe::CATEGORY_ABANDON_CHECKOUT);

        $this->artisan('email:process-sequences')->assertSuccessful();

        expect($sequence->fresh()->suppression_reason)->toBe('unsubscribed');
        Mail::assertNotQueued(SalesTaxStartedReminder::class);
    });
});
