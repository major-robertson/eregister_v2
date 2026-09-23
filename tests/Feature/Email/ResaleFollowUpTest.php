<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\ResaleCert\Livewire\Dashboard;
use App\Domains\ResaleCert\Livewire\SubscriptionCheckout;
use App\Domains\ResaleCert\ResaleFollowUp;
use App\Mail\ResaleStartedReminder;
use App\Models\EmailSequence;
use App\Models\EmailUnsubscribe;
use App\Models\Price;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

/*
| resale_started (EREG-65): someone who came for resale certificates and has
| not subscribed gets "get your certificate" reminders an hour later, then a
| day later, then three days after that, until they subscribe, pay for a
| sales tax registration instead, or opt out of order reminders.
*/

if (! function_exists('resaleFollowUpRegister')) {
    /** Register through the real endpoint, as a visitor who came from $landingPath. */
    function resaleFollowUpRegister(\Illuminate\Foundation\Testing\TestCase $test, string $email, ?string $landingPath, ?array $intent = null): User
    {
        $test->withSession(array_filter([
            'signup_landing_path' => $landingPath,
            'signup_intent' => $intent,
        ]))->post(route('register.store'), [
            'first_name' => 'Rita',
            'last_name' => 'Resale',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        return User::where('email', $email)->firstOrFail();
    }
}

if (! function_exists('resaleFollowUpBusiness')) {
    function resaleFollowUpBusiness(User $user, string $state = 'TX'): Business
    {
        $business = Business::factory()->create([
            'onboarding_completed_at' => now(),
            'business_address' => ['line1' => '1 Main St', 'city' => 'Springfield', 'state' => $state, 'zip' => '62701'],
        ]);
        $business->users()->attach($user, ['role' => 'owner']);

        return $business;
    }
}

if (! function_exists('resaleFollowUpDue')) {
    function resaleFollowUpDue(User $user, ?Business $business = null): EmailSequence
    {
        return EmailSequence::create([
            'user_id' => $user->id,
            'business_id' => $business?->id,
            'sequence_type' => 'resale_started',
            'sequenceable_type' => $user->getMorphClass(),
            'sequenceable_id' => $user->id,
            'customer_type' => 'new',
            'resume_url' => route('resale-cert.checkout'),
            'next_send_at' => now()->subMinute(),
        ]);
    }
}

beforeEach(function () {
    config(['cashier.secret' => null]);
    // Whole seconds: timestamps come back from the database without microseconds.
    $this->freezeSecond();

    Price::updateOrCreate(
        ['product_family' => config('resale_cert.price_family'), 'product_key' => config('resale_cert.price_key'), 'variant_key' => 'default', 'billing_type' => 'subscription'],
        ['amount_cents' => 29700, 'currency' => 'usd', 'active' => true],
    );
    Price::updateOrCreate(
        ['product_family' => 'tax', 'product_key' => 'sales_tax_permit', 'variant_key' => 'per_state', 'billing_type' => 'one_time'],
        ['amount_cents' => 19900, 'currency' => 'usd', 'active' => true],
    );
});

describe('who gets the follow-ups', function () {
    it('starts them when a visitor signs up through the certificate door', function () {
        Mail::fake();

        $user = resaleFollowUpRegister($this, 'rita@example.com', '/lp/resale-certificate/ca', [
            'product' => 'resale-cert', 'intent' => null, 'state' => 'CA', 'source' => null,
        ]);

        $sequence = EmailSequence::where('sequence_type', 'resale_started')->sole();

        expect($sequence->user_id)->toBe($user->id)
            ->and($sequence->sequenceable_type)->toBe('user')
            ->and($sequence->business_id)->toBeNull()
            ->and($sequence->resume_url)->toBe(route('resale-cert.checkout'))
            ->and($sequence->next_send_at->equalTo(now()->addHour()))->toBeTrue();
    });

    it('leaves out a resale page visitor who chose the permit door', function () {
        Mail::fake();

        resaleFollowUpRegister($this, 'paul@example.com', '/lp/resale-certificate/ca', [
            'product' => 'sales-tax', 'intent' => null, 'state' => 'CA', 'source' => null,
        ]);

        expect(EmailSequence::where('sequence_type', 'resale_started')->exists())->toBeFalse();
    });

    it('falls back to the landing page when no door was recorded', function () {
        Mail::fake();

        $user = resaleFollowUpRegister($this, 'tina@example.com', '/lp/resale-certificate/tx');

        expect(EmailSequence::where('sequence_type', 'resale_started')->sole()->user_id)->toBe($user->id);
    });

    it('does nothing for sign-ups from other pages', function () {
        Mail::fake();

        resaleFollowUpRegister($this, 'larry@example.com', '/llc');

        expect(EmailSequence::where('sequence_type', 'resale_started')->exists())->toBeFalse();
    });

    it('starts them from the dashboard for someone who came from the resale pages', function () {
        $user = User::factory()->create(['signup_landing_path' => '/resale-certificates/texas']);
        $business = resaleFollowUpBusiness($user);

        $this->actingAs($user)->withSession(['current_business_id' => $business->id]);

        Livewire::test(Dashboard::class)->assertOk();

        expect(EmailSequence::where('sequence_type', 'resale_started')->sole()->business_id)->toBe($business->id);
    });

    it('attaches the business to a series that started at sign-up', function () {
        $user = User::factory()->create(['signup_landing_path' => '/']);
        ResaleFollowUp::start($user);
        $business = resaleFollowUpBusiness($user, 'IL');

        $this->actingAs($user)->withSession(['current_business_id' => $business->id]);

        Livewire::test(Dashboard::class)->assertOk();

        expect(EmailSequence::where('sequence_type', 'resale_started')->sole()->business_id)->toBe($business->id);
    });

    it('does not start them when someone else only browses the dashboard', function () {
        $user = User::factory()->create(['signup_landing_path' => '/liens']);
        $business = resaleFollowUpBusiness($user);

        $this->actingAs($user)->withSession(['current_business_id' => $business->id]);

        Livewire::test(Dashboard::class)->assertOk();

        expect(EmailSequence::where('sequence_type', 'resale_started')->exists())->toBeFalse();
    });

    it('starts them when anyone opens checkout, and the subscription stops them', function () {
        Mail::fake();

        $user = User::factory()->create(['signup_landing_path' => '/llc']);
        $business = resaleFollowUpBusiness($user);

        $this->actingAs($user)->withSession(['current_business_id' => $business->id]);

        // The keyless stub checkout activates the subscription on the spot.
        Livewire::test(SubscriptionCheckout::class);

        $sequence = EmailSequence::where('sequence_type', 'resale_started')->sole();
        expect($sequence->business_id)->toBe($business->id);

        $sequence->update(['next_send_at' => now()->subMinute()]);
        $this->artisan('email:process-sequences')->assertSuccessful();

        expect($sequence->fresh()->suppression_reason)->toBe('subscribed');
        Mail::assertNotQueued(ResaleStartedReminder::class);
    });
});

describe('the emails', function () {
    it('sends the first one naming the state they searched for, with the checkout link', function () {
        Mail::fake();

        $user = User::factory()->create(['first_name' => 'Rita', 'signup_landing_path' => '/lp/resale-certificate/ca']);
        $sequence = resaleFollowUpDue($user);

        $this->artisan('email:process-sequences')->assertSuccessful();

        Mail::assertQueued(ResaleStartedReminder::class, function (ResaleStartedReminder $mail) {
            $html = $mail->render();

            return $mail->step === 1
                && $mail->envelope()->subject === 'Get your California resale certificate today'
                && str_contains($html, 'You signed up for a California resale certificate')
                && str_contains($html, '$297 a year')
                && str_contains($html, route('resale-cert.checkout'));
        });

        expect(SentEmail::where('email_type', 'resale_started_step_1')->count())->toBe(1)
            ->and($sequence->fresh()->next_send_at->equalTo(now()->addDay()))->toBeTrue();
    });

    it('falls back to the business state, with the right article', function () {
        $user = User::factory()->create(['signup_landing_path' => '/lp/resale-certificate']);
        $sequence = resaleFollowUpDue($user, resaleFollowUpBusiness($user, 'IL'));

        expect((new ResaleStartedReminder($sequence, 1))->envelope()->subject)->toBe('Get your Illinois resale certificate today')
            ->and((new ResaleStartedReminder($sequence, 3))->envelope()->subject)->toBe('Do you still need an Illinois resale certificate?');
    });

    it('stays general when no state is known', function () {
        $user = User::factory()->create(['signup_landing_path' => '/resale-certificates']);
        $sequence = resaleFollowUpDue($user);

        expect((new ResaleStartedReminder($sequence, 1))->envelope()->subject)->toBe('Get your resale certificate today')
            ->and((new ResaleStartedReminder($sequence, 3))->envelope()->subject)->toBe('Do you still need a resale certificate?');
    });

    it('offers a permit registration in the last one, for people who have no permit yet', function () {
        $user = User::factory()->create(['signup_landing_path' => '/lp/resale-certificate/tx']);
        $html = (new ResaleStartedReminder(resaleFollowUpDue($user), 3))->render();

        expect($html)
            ->toContain('This is my last reminder')
            ->toContain('$199 per state')
            ->toContain(route('sales-tax.registrations.start'));
    });
});

describe('what stops them', function () {
    it('stops when they turn off order reminders', function () {
        Mail::fake();

        $user = User::factory()->create(['signup_landing_path' => '/lp/resale-certificate/tx']);
        $sequence = resaleFollowUpDue($user);
        EmailUnsubscribe::unsubscribe($user, EmailUnsubscribe::CATEGORY_ABANDON_CHECKOUT);

        $this->artisan('email:process-sequences')->assertSuccessful();

        expect($sequence->fresh()->suppression_reason)->toBe('unsubscribed');
        Mail::assertNotQueued(ResaleStartedReminder::class);
    });

    it('stops when they paid for a sales tax registration instead', function () {
        Mail::fake();

        $user = User::factory()->create(['signup_landing_path' => '/lp/resale-certificate/tx']);
        $business = resaleFollowUpBusiness($user);
        $sequence = resaleFollowUpDue($user, $business);

        FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['TX'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $user->id,
            'paid_at' => now(),
        ]);

        $this->artisan('email:process-sequences')->assertSuccessful();

        expect($sequence->fresh()->suppression_reason)->toBe('registration_paid');
        Mail::assertNotQueued(ResaleStartedReminder::class);
    });

    it('never starts a second series for the same person', function () {
        $user = User::factory()->create(['signup_landing_path' => '/lp/resale-certificate/tx']);
        $first = ResaleFollowUp::start($user);
        $first->suppress('all_steps_sent');

        $again = ResaleFollowUp::start($user, resaleFollowUpBusiness($user));

        expect($again->id)->toBe($first->id)
            ->and($again->fresh()->suppressed_at)->not->toBeNull()
            ->and(EmailSequence::where('sequence_type', 'resale_started')->count())->toBe(1);
    });
});
