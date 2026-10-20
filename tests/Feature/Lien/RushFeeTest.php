<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Livewire\LienBoard;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingCheckout;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingEvent;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Services\RushTier;
use App\Enums\PaymentStatus;
use App\Mail\PaymentReceipt;
use App\Models\Payment;
use App\Models\Price;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\PriceSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Service\CustomerService;
use Stripe\Service\PaymentIntentService;
use Stripe\StripeClient;

/*
 * Rush fees (EREG-146). Today is Tuesday, October 20, 2026. Kansas
 * mechanics liens are due 4 months after last furnishing; Johnson County
 * e-records, so we must submit one business day before the deadline.
 */

const RUSH_EXPEDITE_TEXT = "We start today. With fewer than 4 business days left we can't promise the county records it in time, and the order is not refunded on that basis.";

/** Deadline Friday October 23, submit by Thursday October 22: 2 business days left. */
const RUSH_EXPEDITE_DATES = [
    'first_furnish_date' => '2026-05-01',
    'last_furnish_date' => '2026-06-23',
    'completion_date' => '2026-06-23',
];

/** Deadline Wednesday October 28, submit by Tuesday October 27: 5 business days left. */
const RUSH_RUSH_DATES = [
    'first_furnish_date' => '2026-05-01',
    'last_furnish_date' => '2026-06-28',
    'completion_date' => '2026-06-28',
];

/** Maricopa County, Arizona, first furnished October 3: the preliminary notice is due October 23. */
const ARIZONA_PRELIM_PROJECT = [
    'jobsite_state' => 'AZ',
    'jobsite_county' => 'Maricopa',
    'first_furnish_date' => '2026-10-03',
    'last_furnish_date' => '2026-10-15',
    'completion_date' => '2026-10-15',
];

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 12:00:00', 'America/New_York')->utc());
    Storage::fake('s3');
    Mail::fake();
    LienDocumentRegistry::flush();
    config(['cashier.secret' => null]);

    (new PriceSeeder)->run();

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create(['lien_onboarding_completed_at' => now()]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

afterEach(fn () => LienDocumentRegistry::flush());

if (! function_exists('rushProject')) {
    /** A general contractor's project (Johnson County, Kansas unless $attributes say otherwise) with an owner and its deadlines. */
    function rushProject(Business $business, array $attributes): LienProject
    {
        $project = LienProject::factory()->forBusiness($business)->create(array_merge([
            'jobsite_state' => 'KS',
            'jobsite_county' => 'Johnson County',
            'claimant_type' => ClaimantType::Gc,
            'property_class' => 'residential',
        ], $attributes));

        app(DeadlineCalculator::class)->calculateForProject($project);

        LienParty::create([
            'business_id' => $business->id,
            'project_id' => $project->id,
            'role' => 'owner',
            'name' => 'Test Owner',
            'address1' => '123 Main St',
            'city' => 'Anytown',
            'state' => $project->jobsite_state,
            'zip' => '12345',
        ]);

        return $project;
    }

    /**
     * The project's deadline for a document (a mechanics lien unless given)
     * and a full-service filing on it.
     *
     * @return array{0: LienProjectDeadline, 1: LienFiling}
     */
    function rushFiling(LienProject $project, array $overrides = [], string $slug = 'mechanics_lien'): array
    {
        $deadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', $slug))
            ->latest('id')
            ->firstOrFail();

        $filing = LienFiling::factory()->forProject($project)->create(array_merge([
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'status' => FilingStatus::Draft,
            'service_level' => 'full_service',
            'created_by_user_id' => auth()->id(),
        ], $overrides));

        return [$deadline, $filing];
    }

    /** Steps 1 to 3 from the project, on to the review step. */
    function rushReviewStep(LienProject $project, LienProjectDeadline $deadline): Testable
    {
        return Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->call('nextStep')
            ->call('nextStep')
            ->set('has_written_contract', '1')
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);
    }

    /** Whether the "Proceed to Payment" button carries the disabled attribute. */
    function rushPayButtonDisabled(Testable $component): bool
    {
        preg_match('/<button[^>]*wire:click="proceedToCheckout"[^>]*>/', $component->html(), $match);

        expect($match)->not->toBeEmpty();

        return preg_match('/\sdisabled(?=[\s=>\/])/', $match[0]) === 1;
    }

    /**
     * Stand a fake Stripe client in for the real one (FilingCheckout and
     * Cashier both resolve it from the container) and return its payment
     * intents service to set expectations on.
     */
    function rushStripe(): Mockery\MockInterface
    {
        $intents = Mockery::mock(PaymentIntentService::class);
        $customers = Mockery::mock(CustomerService::class);
        $customers->shouldReceive('create')->andReturn(Customer::constructFrom(['id' => 'cus_rush']));
        $customers->shouldReceive('retrieve')->andReturn(Customer::constructFrom(['id' => 'cus_rush']));

        $client = new class('sk_test_fake_for_tests') extends StripeClient
        {
            public array $fakes = [];

            public function __get($name)
            {
                return $this->fakes[$name] ?? parent::__get($name);
            }
        };
        $client->fakes = ['paymentIntents' => $intents, 'customers' => $customers];

        app()->bind(StripeClient::class, fn () => $client);

        return $intents;
    }

    function rushIntent(string $id, int $amount, array $metadata = [], string $status = 'requires_payment_method'): PaymentIntent
    {
        return PaymentIntent::constructFrom([
            'id' => $id,
            'client_secret' => $id.'_secret',
            'amount' => $amount,
            'status' => $status,
            'metadata' => $metadata,
        ]);
    }

    function rushPrice(string $productKey, string $variantKey): Price
    {
        return Price::resolve('lien', $productKey, $variantKey);
    }
}

describe('prices', function () {
    it('seeds the two rush fee rows from config', function () {
        expect(Price::resolve('lien', 'rush_fee', 'rush')->amount_cents)->toBe(7500)
            ->and(Price::resolve('lien', 'rush_fee', 'expedite')->amount_cents)->toBe(15000)
            ->and(RushTier::price('expedite')->active)->toBeTrue()
            ->and(RushTier::price(null))->toBeNull();
    });
});

describe('review step', function () {
    it('shows the expedite fee and asks for its terms before payment', function () {
        $project = rushProject($this->business, RUSH_EXPEDITE_DATES);
        [$deadline, $filing] = rushFiling($project);

        $component = rushReviewStep($project, $deadline)
            ->assertSee('We must submit it to the county by October 22, 2026.')
            ->assertSee('2 business days left.')
            ->assertSee('Expedite')
            ->assertSee('$150.00')
            ->assertSee('2 business days left before we must submit to the county.')
            ->assertSee(RUSH_EXPEDITE_TEXT)
            ->assertDontSee('Fewer business days are left');

        expect(rushPayButtonDisabled($component))->toBeTrue();

        $component->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasErrors('rushAcknowledged')
            ->assertNoRedirect();

        expect($filing->fresh()->status)->toBe(FilingStatus::Draft)
            ->and($filing->events()->where('event_type', LienFilingEvent::RUSH_ACKNOWLEDGED)->count())->toBe(0);

        $component->set('rushAcknowledged', true);

        expect(rushPayButtonDisabled($component))->toBeFalse();

        $component->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        $filing->refresh();
        $event = $filing->events()->where('event_type', LienFilingEvent::RUSH_ACKNOWLEDGED)->sole();

        expect($filing->status)->toBe(FilingStatus::AwaitingPayment)
            ->and($filing->rush_tier)->toBe('expedite')
            ->and($filing->rush_acknowledged_at->equalTo(now()))->toBeTrue()
            ->and($filing->must_submit_by->toDateString())->toBe('2026-10-22')
            ->and($event->payload_json)->toEqual([
                'tier' => 'expedite',
                'text' => RUSH_EXPEDITE_TEXT,
                'fee_cents' => 15000,
                'business_days_left' => 2,
                'must_submit_by' => '2026-10-22',
            ])
            ->and($event->created_by)->toBe($this->user->id)
            ->and($event->description())->toBe('Expedite terms accepted');
    });

    it('shows the rush fee without asking for terms', function () {
        $project = rushProject($this->business, RUSH_RUSH_DATES);
        [$deadline, $filing] = rushFiling($project);

        $component = rushReviewStep($project, $deadline)
            ->assertSee('We must submit it to the county by October 27, 2026.')
            ->assertSee('Rush')
            ->assertSee('$75.00')
            ->assertSee('5 business days left before we must submit to the county.')
            ->assertDontSee('Expedite')
            ->assertDontSee('not refunded on that basis');

        expect(rushPayButtonDisabled($component))->toBeFalse();

        $component->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        expect($filing->fresh()->rush_tier)->toBe('rush')
            ->and($filing->fresh()->rush_acknowledged_at)->toBeNull()
            ->and($filing->events()->where('event_type', LienFilingEvent::RUSH_ACKNOWLEDGED)->count())->toBe(0);
    });

    it('shows no fee with more than 6 business days left', function () {
        $project = rushProject($this->business, [
            'first_furnish_date' => '2026-07-01',
            'last_furnish_date' => '2026-08-14',
            'completion_date' => '2026-08-14',
        ]);
        [$deadline, $filing] = rushFiling($project);

        rushReviewStep($project, $deadline)
            ->assertSee('36 business days left.')
            ->assertDontSee('before we must submit to the county')
            ->assertDontSee('$75.00')
            ->assertDontSee('$150.00')
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertRedirect(route('lien.filings.checkout', $filing));

        expect($filing->fresh()->rush_tier)->toBeNull();
    });

    it('charges no fee on self-serve', function () {
        $project = rushProject($this->business, RUSH_EXPEDITE_DATES);
        [$deadline] = rushFiling($project);

        $component = rushReviewStep($project, $deadline)
            ->assertSee('$150.00')
            ->set('service_level', 'self_serve')
            ->assertDontSee('$150.00')
            ->assertDontSee('not refunded on that basis');

        expect(rushPayButtonDisabled($component))->toBeFalse();
    });

    it('charges expedite when the submit-by date is today or gone but the deadline is still ahead', function (array $attributes, string $submitBy, int $daysLeft) {
        $project = rushProject($this->business, $attributes);
        [$deadline, $filing] = rushFiling($project);

        $component = rushReviewStep($project, $deadline)
            ->assertDontSee('has passed')
            ->assertSee("We must submit it to the county by {$submitBy}.")
            ->assertSee('No business days left before that date.')
            ->assertSee('Expedite')
            ->assertSee('$150.00')
            ->assertSee('No business days left before we must submit to the county.')
            ->assertSee(RUSH_EXPEDITE_TEXT);

        expect(rushPayButtonDisabled($component))->toBeTrue();

        $component->set('disclaimerAccepted', true)
            ->set('rushAcknowledged', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        $filing->refresh();

        expect($filing->rush_tier)->toBe('expedite')
            ->and($filing->deadline_business_days_left)->toBe($daysLeft)
            ->and($filing->events()->where('event_type', LienFilingEvent::RUSH_ACKNOWLEDGED)->sole()->payload_json['business_days_left'])->toBe($daysLeft);
    })->with([
        // Deadline Wednesday October 21; Johnson County e-records, submit by today.
        '0 days' => [[
            'first_furnish_date' => '2026-05-01',
            'last_furnish_date' => '2026-06-21',
            'completion_date' => '2026-06-21',
        ], 'October 20, 2026', 0],
        // Deadline Friday October 23; Sedgwick County takes mail, five business days back.
        '-2 days' => [RUSH_EXPEDITE_DATES + ['jobsite_county' => 'Sedgwick'], 'October 16, 2026', -2],
    ]);

    it('charges no fee on a notice by default', function () {
        // Arizona preliminary notices are due 20 days after first furnishing: Friday October 23.
        $project = rushProject($this->business, ARIZONA_PRELIM_PROJECT);
        [$deadline, $filing] = rushFiling($project, [], 'prelim_notice');

        $component = rushReviewStep($project, $deadline)
            ->assertSee('We must send it by October 22, 2026.')
            ->assertSee('2 business days left.')
            ->assertDontSee('before we must send it.')
            ->assertDontSee('$150.00')
            ->assertDontSee('not refunded on that basis');

        expect(rushPayButtonDisabled($component))->toBeFalse();

        $component->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        expect($filing->fresh()->rush_tier)->toBeNull();
    });

    it('charges notices with their own wording once config lists them', function () {
        config(['lien.rush_fee_documents' => ['mechanics_lien', 'prelim_notice', 'noi']]);
        $noticeText = "We start today. With fewer than 4 business days left we can't promise it is served in time, and the order is not refunded on that basis.";

        $project = rushProject($this->business, ARIZONA_PRELIM_PROJECT);
        [$deadline, $filing] = rushFiling($project, [], 'prelim_notice');

        rushReviewStep($project, $deadline)
            ->assertSee('Expedite')
            ->assertSee('$150.00')
            ->assertSee('2 business days left before we must send it.')
            ->assertSee($noticeText)
            ->assertDontSee('before we must submit to the county')
            ->set('disclaimerAccepted', true)
            ->set('rushAcknowledged', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        expect($filing->fresh()->rush_tier)->toBe('expedite')
            ->and($filing->events()->where('event_type', LienFilingEvent::RUSH_ACKNOWLEDGED)->sole()->payload_json['text'])->toBe($noticeText);
    });

    it('opens on the review step with a note when checkout sent the customer back', function () {
        $project = rushProject($this->business, RUSH_EXPEDITE_DATES);
        [$deadline, $filing] = rushFiling($project, [
            'status' => FilingStatus::AwaitingPayment,
            'must_submit_by' => '2026-10-22',
            'rush_tier' => 'expedite',
        ]);

        Livewire::withQueryParams(['rush' => 'changed'])
            ->test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->assertSet('step', 4)
            ->assertSee('Fewer business days are left than when you last saw this order, so the fee has changed.')
            ->assertSee(RUSH_EXPEDITE_TEXT)
            ->set('disclaimerAccepted', true)
            ->set('rushAcknowledged', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        expect($filing->fresh()->rush_acknowledged_at)->not->toBeNull();
    });
});

describe('checkout', function () {
    it('charges the service plus the expedite fee and lists both', function () {
        $project = rushProject($this->business, RUSH_EXPEDITE_DATES);
        [, $filing] = rushFiling($project, [
            'status' => FilingStatus::AwaitingPayment,
            'must_submit_by' => '2026-10-22',
            'rush_tier' => 'expedite',
            'rush_acknowledged_at' => now(),
        ]);
        $base = rushPrice('mechanics_lien', 'full_service');
        $fee = rushPrice('rush_fee', 'expedite');

        rushStripe()->shouldReceive('create')
            ->once()
            ->withArgs(fn (array $params, array $options) => $params['amount'] === 44900
                && $params['customer'] === 'cus_rush'
                && $params['metadata']['lien_rush_tier'] === 'expedite'
                && $params['metadata']['app_domain'] === 'lien'
                && str_starts_with($options['idempotency_key'], 'payment_'))
            ->andReturn(rushIntent('pi_rush_new', 44900));

        Livewire::test(FilingCheckout::class, ['filing' => $filing])
            ->assertNoRedirect()
            ->assertSet('amountCents', 44900)
            ->assertSee('Mechanics Lien (Full Service)')
            ->assertSee('$299.00')
            ->assertSee('Expedite fee')
            ->assertSee('$150.00')
            ->assertSee('Total')
            ->assertSee('$449.00');

        $payment = Payment::whereMorphedTo('purchasable', $filing)->sole();

        expect($payment->amount_cents)->toBe(44900)
            ->and($payment->price_id)->toBe($base->id)
            ->and($payment->stripe_payment_intent_id)->toBe('pi_rush_new')
            ->and($payment->meta['items'])->toEqual([
                ['price_id' => $base->id, 'product_key' => 'mechanics_lien', 'variant_key' => 'full_service', 'label' => 'Mechanics Lien (Full Service)', 'amount_cents' => 29900],
                ['price_id' => $fee->id, 'product_key' => 'rush_fee', 'variant_key' => 'expedite', 'label' => 'Expedite fee', 'amount_cents' => 15000],
            ]);
    });

    it('charges the service alone without a tier', function () {
        $project = rushProject($this->business, RUSH_EXPEDITE_DATES);
        [, $filing] = rushFiling($project, [
            'status' => FilingStatus::AwaitingPayment,
            'must_submit_by' => '2026-12-11',
        ]);

        rushStripe()->shouldReceive('create')
            ->once()
            ->withArgs(fn (array $params) => $params['amount'] === 29900 && $params['metadata']['lien_rush_tier'] === 'none')
            ->andReturn(rushIntent('pi_plain', 29900));

        Livewire::test(FilingCheckout::class, ['filing' => $filing])
            ->assertSet('amountCents', 29900)
            ->assertSee('Mechanics Lien (Full Service)')
            ->assertDontSee('Rush fee')
            ->assertDontSee('Expedite fee');

        $payment = Payment::whereMorphedTo('purchasable', $filing)->sole();

        expect($payment->amount_cents)->toBe(29900)
            ->and($payment->meta['items'])->toHaveCount(1)
            ->and($filing->fresh()->rush_tier)->toBeNull();
    });

    it('adds a rush fee that came due since the review step to the open PaymentIntent', function () {
        $project = rushProject($this->business, RUSH_RUSH_DATES);
        // Reviewed a few days ago with 7 business days left; 5 are left today.
        [, $filing] = rushFiling($project, [
            'status' => FilingStatus::AwaitingPayment,
            'must_submit_by' => '2026-10-27',
            'rush_tier' => null,
        ]);
        $base = rushPrice('mechanics_lien', 'full_service');
        $payment = Payment::factory()->forPurchasable($filing)->create([
            'price_id' => $base->id,
            'amount_cents' => 29900,
            'status' => PaymentStatus::RequiresPaymentMethod,
            'stripe_payment_intent_id' => 'pi_open',
        ]);

        $intents = rushStripe();
        $intents->shouldReceive('retrieve')->once()->with('pi_open')
            ->andReturn(rushIntent('pi_open', 29900, ['lien_rush_tier' => 'none']));
        $intents->shouldReceive('update')
            ->once()
            ->withArgs(fn (string $id, array $params) => $id === 'pi_open'
                && $params['amount'] === 37400
                && $params['metadata']['lien_rush_tier'] === 'rush'
                && $params['metadata']['app_payment_id'] === $payment->id)
            ->andReturn(rushIntent('pi_open', 37400, ['lien_rush_tier' => 'rush']));
        $intents->shouldNotReceive('create');

        Livewire::test(FilingCheckout::class, ['filing' => $filing])
            ->assertNoRedirect()
            ->assertSet('paymentIntentId', 'pi_open')
            ->assertSet('clientSecret', 'pi_open_secret')
            ->assertSet('amountCents', 37400)
            ->assertSee('Rush fee')
            ->assertSee('$75.00')
            ->assertSee('$374.00');

        $payment->refresh();

        expect(Payment::whereMorphedTo('purchasable', $filing)->count())->toBe(1)
            ->and($payment->amount_cents)->toBe(37400)
            ->and($payment->price_id)->toBe($base->id)
            ->and(array_column($payment->meta['items'], 'amount_cents'))->toBe([29900, 7500])
            ->and($filing->fresh()->rush_tier)->toBe('rush');
    });

    it('leaves an open PaymentIntent alone when the total has not changed', function () {
        $project = rushProject($this->business, RUSH_RUSH_DATES);
        [, $filing] = rushFiling($project, [
            'status' => FilingStatus::AwaitingPayment,
            'must_submit_by' => '2026-10-27',
            'rush_tier' => 'rush',
        ]);
        Payment::factory()->forPurchasable($filing)->create([
            'price_id' => rushPrice('mechanics_lien', 'full_service')->id,
            'amount_cents' => 37400,
            'status' => PaymentStatus::Initiated,
            'stripe_payment_intent_id' => 'pi_same',
        ]);

        $intents = rushStripe();
        $intents->shouldReceive('retrieve')->once()->andReturn(rushIntent('pi_same', 37400, ['lien_rush_tier' => 'rush']));
        $intents->shouldNotReceive('update');
        $intents->shouldNotReceive('create');

        Livewire::test(FilingCheckout::class, ['filing' => $filing])
            ->assertSet('paymentIntentId', 'pi_same')
            ->assertSet('amountCents', 37400);

        expect(Payment::whereMorphedTo('purchasable', $filing)->sole()->meta['items'])->toHaveCount(2);
    });

    it('updates a payment row without a PaymentIntent before creating one', function () {
        $project = rushProject($this->business, RUSH_RUSH_DATES);
        [, $filing] = rushFiling($project, [
            'status' => FilingStatus::AwaitingPayment,
            'must_submit_by' => '2026-10-27',
        ]);
        $payment = Payment::factory()->forPurchasable($filing)->create([
            'price_id' => rushPrice('mechanics_lien', 'full_service')->id,
            'amount_cents' => 29900,
            'status' => PaymentStatus::Initiated,
        ]);

        rushStripe()->shouldReceive('create')
            ->once()
            ->withArgs(fn (array $params) => $params['amount'] === 37400 && $params['metadata']['app_payment_id'] === $payment->id)
            ->andReturn(rushIntent('pi_late', 37400));

        Livewire::test(FilingCheckout::class, ['filing' => $filing])->assertSet('amountCents', 37400);

        expect(Payment::whereMorphedTo('purchasable', $filing)->count())->toBe(1)
            ->and($payment->fresh()->amount_cents)->toBe(37400)
            ->and($payment->fresh()->stripe_payment_intent_id)->toBe('pi_late');
    });

    it('sends the customer back to accept the terms when the order became expedite', function () {
        $project = rushProject($this->business, RUSH_EXPEDITE_DATES);
        [$deadline, $filing] = rushFiling($project, [
            'status' => FilingStatus::AwaitingPayment,
            'must_submit_by' => '2026-10-22',
            'rush_tier' => 'rush',
        ]);

        $intents = rushStripe();
        $intents->shouldNotReceive('create');
        $intents->shouldNotReceive('retrieve');

        Livewire::test(FilingCheckout::class, ['filing' => $filing])
            ->assertRedirect(route('lien.filings.start', ['project' => $project, 'deadline' => $deadline, 'rush' => 'changed']));

        expect($filing->fresh()->rush_tier)->toBe('expedite')
            ->and($filing->fresh()->rush_acknowledged_at)->toBeNull()
            ->and(Payment::whereMorphedTo('purchasable', $filing)->count())->toBe(0);
    });

    it('keeps the expedite fee once the submit-by date has gone, while the deadline is still ahead', function () {
        // Deadline Friday October 23; the submit-by date was Friday October 16.
        $project = rushProject($this->business, RUSH_EXPEDITE_DATES);
        [, $filing] = rushFiling($project, [
            'status' => FilingStatus::AwaitingPayment,
            'must_submit_by' => '2026-10-16',
            'rush_tier' => 'expedite',
            'rush_acknowledged_at' => now()->subDay(),
        ]);

        rushStripe()->shouldReceive('create')
            ->once()
            ->withArgs(fn (array $params) => $params['amount'] === 44900 && $params['metadata']['lien_rush_tier'] === 'expedite')
            ->andReturn(rushIntent('pi_late_expedite', 44900));

        Livewire::test(FilingCheckout::class, ['filing' => $filing])
            ->assertNoRedirect()
            ->assertSet('amountCents', 44900);

        expect($filing->fresh()->rush_tier)->toBe('expedite')
            ->and($filing->fresh()->rush_acknowledged_at)->not->toBeNull();
    });

    it('drops the fee once the deadline itself has passed', function () {
        // Last furnished March 2: the Kansas lien was due July 2.
        $project = rushProject($this->business, [
            'first_furnish_date' => '2026-01-05',
            'last_furnish_date' => '2026-03-02',
            'completion_date' => '2026-03-02',
        ]);
        [, $filing] = rushFiling($project, [
            'status' => FilingStatus::AwaitingPayment,
            'must_submit_by' => '2026-07-01',
            'rush_tier' => 'expedite',
            'rush_acknowledged_at' => now()->subDay(),
        ]);

        rushStripe()->shouldReceive('create')
            ->once()
            ->withArgs(fn (array $params) => $params['amount'] === 29900 && $params['metadata']['lien_rush_tier'] === 'none')
            ->andReturn(rushIntent('pi_past', 29900));

        Livewire::test(FilingCheckout::class, ['filing' => $filing])->assertSet('amountCents', 29900);

        expect($filing->fresh()->rush_tier)->toBeNull()
            ->and($filing->fresh()->rush_acknowledged_at)->toBeNull();
    });
});

describe('receipt', function () {
    it('lists each item and the total', function () {
        $project = rushProject($this->business, RUSH_EXPEDITE_DATES);
        [, $filing] = rushFiling($project, ['status' => FilingStatus::Paid, 'paid_at' => now()]);

        $payment = Payment::factory()->forPurchasable($filing)->succeeded()->create([
            'price_id' => rushPrice('mechanics_lien', 'full_service')->id,
            'amount_cents' => 44900,
            'meta' => ['items' => [
                ['price_id' => 1, 'product_key' => 'mechanics_lien', 'variant_key' => 'full_service', 'label' => 'Mechanics Lien (Full Service)', 'amount_cents' => 29900],
                ['price_id' => 2, 'product_key' => 'rush_fee', 'variant_key' => 'expedite', 'label' => 'Expedite fee', 'amount_cents' => 15000],
            ]],
        ]);

        $rendered = (new PaymentReceipt($payment))->render();

        expect($rendered)->toContain('Mechanics Lien (Full Service)')
            ->and($rendered)->toContain('$299.00')
            ->and($rendered)->toContain('Expedite fee')
            ->and($rendered)->toContain('$150.00')
            ->and($rendered)->toContain('Total')
            ->and($rendered)->toContain('$449.00');
    });

    it('keeps the single line for a payment without items', function () {
        $project = rushProject($this->business, RUSH_EXPEDITE_DATES);
        [, $filing] = rushFiling($project, ['status' => FilingStatus::Paid, 'paid_at' => now()]);

        $payment = Payment::factory()->forPurchasable($filing)->succeeded()->create([
            'price_id' => rushPrice('mechanics_lien', 'full_service')->id,
            'amount_cents' => 29900,
        ]);

        $rendered = (new PaymentReceipt($payment))->render();

        expect($rendered)->toContain('Mechanics Lien (Full Service)')
            ->and($rendered)->toContain('$299.00')
            ->and($rendered)->not->toContain('Total');
    });
});

describe('admin', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('lien.view', 'lien.update');
    });

    it('badges rush orders on the board and puts them first in their column', function () {
        $project = rushProject($this->business, RUSH_EXPEDITE_DATES);
        $make = fn (array $attributes) => rushFiling($project, array_merge(['status' => FilingStatus::Paid], $attributes))[1];

        $plainOld = $make(['paid_at' => now()->subDays(5)]);
        $plainNew = $make(['paid_at' => now()->subDays(3)]);
        $rushLater = $make(['paid_at' => now()->subDay(), 'rush_tier' => 'rush', 'must_submit_by' => '2026-10-28']);
        $rushSooner = $make(['paid_at' => now()->subHours(2), 'rush_tier' => 'rush', 'must_submit_by' => '2026-10-26']);
        $expedite = $make(['paid_at' => now(), 'rush_tier' => 'expedite', 'must_submit_by' => '2026-10-22']);

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        $board = Livewire::test(LienBoard::class)
            ->assertSee('Expedite')
            ->assertSee('Rush');

        $column = $board->instance()->getFilings()->first(fn ($filings) => $filings->contains('id', $expedite->id));

        expect($column->pluck('id')->all())->toBe([
            $expedite->id,
            $rushSooner->id,
            $rushLater->id,
            $plainOld->id,
            $plainNew->id,
        ]);
    });

    it('shows the tier, the fee and when the terms were accepted', function () {
        $project = rushProject($this->business, RUSH_EXPEDITE_DATES);
        [, $filing] = rushFiling($project, [
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
            'rush_tier' => 'expedite',
            'rush_acknowledged_at' => now(),
        ]);
        Payment::factory()->forPurchasable($filing)->succeeded()->create([
            'price_id' => rushPrice('mechanics_lien', 'full_service')->id,
            'amount_cents' => 44900,
            'meta' => ['items' => [
                ['price_id' => 1, 'product_key' => 'mechanics_lien', 'variant_key' => 'full_service', 'label' => 'Mechanics Lien (Full Service)', 'amount_cents' => 29900],
                ['price_id' => 2, 'product_key' => 'rush_fee', 'variant_key' => 'expedite', 'label' => 'Expedite fee', 'amount_cents' => 15000],
            ]],
        ]);

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Rush fee')
            ->assertSee('Expedite')
            ->assertSee('$150.00')
            ->assertSee('Expedite terms accepted')
            ->assertSee('Oct 20, 2026 12:00 PM');
    });

    it('shows nothing about rush on an ordinary order', function () {
        $project = rushProject($this->business, RUSH_EXPEDITE_DATES);
        [, $filing] = rushFiling($project, ['status' => FilingStatus::Paid, 'paid_at' => now()]);

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertDontSee('Rush fee')
            ->assertDontSee('Expedite terms accepted');
    });
});
