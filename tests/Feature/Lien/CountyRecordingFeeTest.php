<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingCheckout;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Services\CountyRecordingFee;
use App\Domains\Lien\Support\RecordingTurnaround;
use App\Domains\Lien\Support\ServiceComparison;
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
 * The county recording fee, the turnaround promise and the service
 * comparison on the review step (EREG-143). Today is Tuesday, October 20,
 * 2026. Johnson County, Kansas e-records and its file states $17 for a lien
 * (lien_counties/ks/johnson.php), not a release; Sedgwick County has no file,
 * so the Kansas state file applies: a mail-in office and no fee stated.
 */

/** Last furnished August 14: the Kansas lien is due Monday December 14, 2026. */
const FEE_DATES = [
    'first_furnish_date' => '2026-07-01',
    'last_furnish_date' => '2026-08-14',
    'completion_date' => '2026-08-14',
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

if (! function_exists('feeProject')) {
    /** A general contractor's project (Johnson County, Kansas unless $attributes say otherwise) with an owner and its deadlines. */
    function feeProject(Business $business, array $attributes = []): LienProject
    {
        $project = LienProject::factory()->forBusiness($business)->create(array_merge([
            'jobsite_state' => 'KS',
            'jobsite_county' => 'Johnson County',
            'claimant_type' => ClaimantType::Gc,
            'property_class' => 'residential',
            'apn' => '087-123-04-0-11-02-005.00',
        ], FEE_DATES, $attributes));

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
    function feeFiling(LienProject $project, array $overrides = [], string $slug = 'mechanics_lien'): array
    {
        $deadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', $slug))
            ->latest('id')
            ->first();

        $filing = LienFiling::factory()->forProject($project)->create(array_merge([
            'document_type_id' => $deadline?->document_type_id ?? LienDocumentType::where('slug', $slug)->value('id'),
            'project_deadline_id' => $deadline?->id,
            'status' => FilingStatus::Draft,
            'service_level' => 'full_service',
            'created_by_user_id' => auth()->id(),
        ], $overrides));

        return [$deadline, $filing];
    }

    /** Steps 1 to 3 from the project, on to the review step. */
    function feeReviewStep(LienProject $project, LienProjectDeadline $deadline): Testable
    {
        return Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->call('nextStep')
            ->call('nextStep')
            ->set('has_written_contract', '1')
            ->fill(['base_contract_amount' => '5000', 'credits_deductions' => '0', 'payments_received' => '0'])
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.')
            ->set('stateAnswers', wizardStateAnswers())
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);
    }

    /** A fake Stripe client whose payment intents service the test sets expectations on. */
    function feeStripe(): Mockery\MockInterface
    {
        $intents = Mockery::mock(PaymentIntentService::class);
        $customers = Mockery::mock(CustomerService::class);
        $customers->shouldReceive('create')->andReturn(Customer::constructFrom(['id' => 'cus_fee']));
        $customers->shouldReceive('retrieve')->andReturn(Customer::constructFrom(['id' => 'cus_fee']));

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

    function feeIntent(string $id, int $amount): PaymentIntent
    {
        return PaymentIntent::constructFrom([
            'id' => $id,
            'client_secret' => $id.'_secret',
            'amount' => $amount,
            'status' => 'requires_payment_method',
            'metadata' => [],
        ]);
    }
}

describe('data', function () {
    it('takes the county file over the state file, per document', function () {
        // Denver states one fee for both; Colorado states one for every county.
        expect(LienDocumentRegistry::recordingFeeCents('CO', 'denver', 'mechanics_lien'))->toBe(4300)
            ->and(LienDocumentRegistry::recordingFeeCents('CO', 'denver', 'lien_release'))->toBe(4300)
            ->and(LienDocumentRegistry::recordingFeeCents('CO', 'el-paso', 'mechanics_lien'))->toBe(4300);

        // Johnson County states its lien fee only; Kansas states none.
        expect(LienDocumentRegistry::recordingFeeCents('KS', 'johnson', 'mechanics_lien'))->toBe(1700)
            ->and(LienDocumentRegistry::recordingFeeCents('KS', 'johnson', 'lien_release'))->toBeNull()
            ->and(LienDocumentRegistry::recordingFeeCents('KS', 'sedgwick', 'mechanics_lien'))->toBeNull()
            ->and(LienDocumentRegistry::recordingFeeCents('KS', null, 'mechanics_lien'))->toBeNull();

        // Charleston states the lien; South Carolina's uniform release fee fills the rest.
        expect(LienDocumentRegistry::recordingFeeCents('SC', 'charleston', 'mechanics_lien'))->toBe(2500)
            ->and(LienDocumentRegistry::recordingFeeCents('SC', 'charleston', 'lien_release'))->toBe(1000)
            ->and(LienDocumentRegistry::recordingFeeCents('SC', 'greenville', 'lien_release'))->toBe(1000);

        // A per-page schedule is not a fee we can charge up front.
        expect(LienDocumentRegistry::recordingFeeCents('MO', 'jackson', 'mechanics_lien'))->toBeNull()
            ->and(LienDocumentRegistry::recordingFeeCents('WA', 'pierce', 'mechanics_lien'))->toBeNull();
    });

    it('has no fee for the documents the county does not record', function () {
        foreach (['prelim_notice', 'noi', 'demand_letter'] as $kind) {
            expect(LienDocumentRegistry::recordingFeeCents('CO', 'denver', $kind))->toBeNull();
        }

        expect(CountyRecordingFee::cents('ZZ', null, 'mechanics_lien'))->toBeNull();
    });

    it('lets a county value for one document fall back to the state for the other', function () {
        LienDocumentRegistry::fake('KS', ['recording_fee_cents' => ['mechanics_lien' => 3000, 'lien_release' => 2000]]);
        LienDocumentRegistry::fake('KS', ['county' => 'Test', 'recording_fee_cents' => ['mechanics_lien' => 5000]], 'test');

        expect(LienDocumentRegistry::recordingFeeCents('KS', 'test', 'mechanics_lien'))->toBe(5000)
            ->and(LienDocumentRegistry::recordingFeeCents('KS', 'test', 'lien_release'))->toBe(2000)
            ->and(LienDocumentRegistry::recordingFeeCents('KS', 'other', 'mechanics_lien'))->toBe(3000);
    });

    it('applies on full service to liens and releases only', function () {
        expect(CountyRecordingFee::applies('full_service', 'mechanics_lien'))->toBeTrue()
            ->and(CountyRecordingFee::applies('full_service', 'lien_release'))->toBeTrue()
            ->and(CountyRecordingFee::applies('self_serve', 'mechanics_lien'))->toBeFalse()
            ->and(CountyRecordingFee::applies('full_service', 'noi'))->toBeFalse()
            ->and(CountyRecordingFee::applies(null, 'mechanics_lien'))->toBeFalse();
    });
});

describe('turnaround', function () {
    it('uses the county method, else the state method', function () {
        // Johnson County e-records; Kansas offices take mail.
        expect(RecordingTurnaround::sentence('KS', 'johnson'))->toBe('Typically 1 to 5 business days after we submit.')
            ->and(RecordingTurnaround::sentence('KS', 'sedgwick'))->toBe('Typically 2 to 6 weeks after we submit.')
            ->and(RecordingTurnaround::sentence('KS'))->toBe('Typically 2 to 6 weeks after we submit.')
            ->and(RecordingTurnaround::words('ZZ'))->toBeNull();
    });

    it('takes a county file\'s own words, as the milestone email does', function () {
        LienDocumentRegistry::fake('KS', ['county' => 'Test', 'recording' => ['filing_office' => ['method' => 'mail'], 'turnaround' => ['mail' => '3 to 4 weeks']]], 'test');

        expect(RecordingTurnaround::sentence('KS', 'test'))->toBe('Typically 3 to 4 weeks after we submit.');

        LienDocumentRegistry::fake('KS', ['county' => 'Test', 'recording' => ['turnaround' => 'about a week']], 'test');

        expect(RecordingTurnaround::sentence('KS', 'test'))->toBe('Typically about a week after we submit.');
    });
});

describe('review step', function () {
    it('shows the known fee and the turnaround on a full-service lien', function () {
        $project = feeProject($this->business);
        [$deadline] = feeFiling($project);

        feeReviewStep($project, $deadline)
            ->assertSeeInOrder(['Recording with the county', 'County recording fee', '$17.00', 'Typically 1 to 5 business days after we submit.'])
            ->assertDontSee(CountyRecordingFee::UNKNOWN_LINE);
    });

    it('says the fee is billed at cost where it is not known', function () {
        $project = feeProject($this->business, ['jobsite_county' => 'Sedgwick']);
        [$deadline] = feeFiling($project);

        feeReviewStep($project, $deadline)
            ->assertSee(CountyRecordingFee::UNKNOWN_LINE)
            ->assertSee('Typically 2 to 6 weeks after we submit.')
            ->assertViewHas('countyRecording', ['fee' => null, 'turnaround' => 'Typically 2 to 6 weeks after we submit.']);
    });

    it('never shows it on self-serve or for a notice', function () {
        $project = feeProject($this->business);
        [$deadline] = feeFiling($project);

        feeReviewStep($project, $deadline)
            ->set('service_level', 'self_serve')
            ->assertViewHas('countyRecording', null)
            ->assertDontSee('Recording with the county')
            ->assertDontSee(CountyRecordingFee::UNKNOWN_LINE);

        [$noticeDeadline] = feeFiling($project, [], 'noi');

        Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $noticeDeadline])
            ->set('step', 4)
            ->assertViewHas('countyRecording', null)
            ->assertDontSee('Recording with the county');
    });

    it('compares self-serve and full service for the county', function () {
        $project = feeProject($this->business);
        [$deadline] = feeFiling($project);

        $component = feeReviewStep($project, $deadline)
            ->assertSee('Self-serve and full service, side by side')
            ->assertSeeInOrder([
                'Who prepares it', 'We do, from your answers.', 'We do, and our team checks it before it goes out.',
                'Who signs it', 'You print it and sign it in front of a notary.', 'You sign it in front of a notary and upload a scan.',
                'Who records it', 'You record it with the county.', 'We record it with the county and send copies to every party the law requires.',
                'County recording fee', 'You pay the county when you record it.', '$17.00, added at checkout.',
                'Typical turnaround', 'Typically 1 to 5 business days after you submit.', 'Typically 1 to 5 business days after we submit.',
                'What you get back', 'The document as a PDF, with filing instructions.', 'The recorded copy and proof of mailing.',
            ]);

        // The table stays while the customer weighs self-serve.
        $component->set('service_level', 'self_serve')->assertSee('$17.00, added at checkout.');
    });

    it('builds the same rows for a notice, without the county rows', function () {
        expect(ServiceComparison::rows('noi', 'KS', 'johnson'))->toBe([
            ['label' => 'Who prepares it', 'self_serve' => 'We do, from your answers.', 'full_service' => 'We do, and our team checks it before it goes out.'],
            ['label' => 'Who signs it', 'self_serve' => 'You print it and sign it.', 'full_service' => 'You sign it online. No printing or notary.'],
            ['label' => 'Who sends it', 'self_serve' => 'You mail or deliver it.', 'full_service' => 'We mail it by certified mail.'],
            ['label' => 'What you get back', 'self_serve' => 'The document as a PDF, with sending instructions.', 'full_service' => 'Proof of mailing.'],
        ])
            ->and(ServiceComparison::prices('mechanics_lien', 'NJ'))->toBe(['self_serve' => 9900, 'full_service' => 89900]);
    });

    it('says who overnights the original in the table where the county needs it', function () {
        LienDocumentRegistry::fake('KS', [
            'county' => 'Johnson',
            'recording' => ['filing_office' => ['method' => 'mail']],
            'signature_requirements' => ['wet_ink_original_required' => true, 'source' => 'Test county.'],
        ], 'johnson');

        $rows = collect(ServiceComparison::rows('mechanics_lien', 'KS', 'johnson'))->keyBy('label');

        expect($rows)->not->toHaveKey('Overnight original')
            ->and($rows['Who signs it']['full_service'])->toBe('You sign it in front of a notary and overnight the original to us.')
            ->and($rows['County recording fee']['full_service'])->toBe('Billed at cost after recording.')
            ->and($rows['Typical turnaround']['full_service'])->toBe('Typically 2 to 6 weeks after we submit.');
    });
});

describe('checkout', function () {
    it('adds the known fee as an item with no price row on a full-service lien', function () {
        $project = feeProject($this->business);
        [, $filing] = feeFiling($project, ['status' => FilingStatus::AwaitingPayment, 'must_submit_by' => '2026-12-11']);
        $base = Price::resolve('lien', 'mechanics_lien', 'full_service');

        feeStripe()->shouldReceive('create')
            ->once()
            ->withArgs(fn (array $params) => $params['amount'] === 31600)
            ->andReturn(feeIntent('pi_fee', 31600));

        Livewire::test(FilingCheckout::class, ['filing' => $filing])
            ->assertSet('amountCents', 31600)
            ->assertSeeInOrder(['Mechanics Lien (Full Service)', '$299.00', 'County recording fee', '$17.00', '$316.00', 'Typically 1 to 5 business days after we submit.'])
            ->assertDontSee(CountyRecordingFee::UNKNOWN_LINE);

        $payment = Payment::whereMorphedTo('purchasable', $filing)->sole();

        expect($payment->amount_cents)->toBe(31600)
            ->and($payment->price_id)->toBe($base->id)
            ->and($payment->meta['items'])->toEqual([
                ['price_id' => $base->id, 'product_key' => 'mechanics_lien', 'variant_key' => 'full_service', 'label' => 'Mechanics Lien (Full Service)', 'amount_cents' => 29900],
                ['price_id' => null, 'product_key' => 'county_recording_fee', 'variant_key' => null, 'label' => 'County recording fee', 'amount_cents' => 1700],
            ]);
    });

    it('adds nothing on self-serve, where the fee is unknown, or for a release the county file does not price', function (array $project, array $filing, string $slug, int $amount, ?string $note) {
        $model = feeProject($this->business, $project);
        [, $order] = feeFiling($model, ['status' => FilingStatus::AwaitingPayment, ...$filing], $slug);

        feeStripe()->shouldReceive('create')->once()->andReturn(feeIntent('pi_plain', $amount));

        $component = Livewire::test(FilingCheckout::class, ['filing' => $order])
            ->assertSet('amountCents', $amount)
            ->assertDontSee('<dt>County recording fee</dt>', false);

        // Full service says the fee comes later; self-serve says nothing about it.
        $note === null
            ? $component->assertDontSee('County recording fee')->assertDontSee('after we submit')
            : $component->assertSeeInOrder([CountyRecordingFee::UNKNOWN_LINE, $note]);

        expect(collect(Payment::whereMorphedTo('purchasable', $order)->sole()->meta['items'])->pluck('product_key'))
            ->not->toContain('county_recording_fee');
    })->with([
        'self-serve' => [[], ['service_level' => 'self_serve'], 'mechanics_lien', 9900, null],
        'unknown county fee' => [['jobsite_county' => 'Sedgwick'], ['must_submit_by' => '2026-12-07'], 'mechanics_lien', 29900, 'Typically 2 to 6 weeks after we submit.'],
        'release in Johnson County' => [[], [], 'lien_release', 9900, 'Typically 1 to 5 business days after we submit.'],
    ]);

    it('lists the fee on the receipt though it has no price row', function () {
        $project = feeProject($this->business);
        [, $filing] = feeFiling($project, ['status' => FilingStatus::Paid, 'paid_at' => now()]);

        $payment = Payment::factory()->forPurchasable($filing)->succeeded()->create([
            'price_id' => Price::resolve('lien', 'mechanics_lien', 'full_service')->id,
            'amount_cents' => 31600,
            'meta' => ['items' => [
                ['price_id' => 1, 'product_key' => 'mechanics_lien', 'variant_key' => 'full_service', 'label' => 'Mechanics Lien (Full Service)', 'amount_cents' => 29900],
                ['price_id' => null, 'product_key' => 'county_recording_fee', 'variant_key' => null, 'label' => 'County recording fee', 'amount_cents' => 1700],
            ]],
        ]);

        $rendered = (new PaymentReceipt($payment))->render();

        expect($rendered)->toContain('County recording fee')
            ->and($rendered)->toContain('$17.00')
            ->and($rendered)->toContain('$316.00');
    });
});

describe('admin', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('lien.view', 'lien.update');
    });

    it('shows the fee the order paid, or that it is to be billed', function () {
        $project = feeProject($this->business);
        [, $paid] = feeFiling($project, ['status' => FilingStatus::Paid, 'paid_at' => now()]);
        [, $unpriced] = feeFiling(feeProject($this->business, ['jobsite_county' => 'Sedgwick']), ['status' => FilingStatus::Paid, 'paid_at' => now()]);

        Payment::factory()->forPurchasable($paid)->succeeded()->create([
            'price_id' => Price::resolve('lien', 'mechanics_lien', 'full_service')->id,
            'amount_cents' => 31600,
            'meta' => ['items' => [
                ['price_id' => 1, 'product_key' => 'mechanics_lien', 'variant_key' => 'full_service', 'label' => 'Mechanics Lien (Full Service)', 'amount_cents' => 29900],
                ['price_id' => null, 'product_key' => 'county_recording_fee', 'variant_key' => null, 'label' => 'County recording fee', 'amount_cents' => 1700],
            ]],
        ]);

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $paid])
            ->assertSeeInOrder(['County recording fee', '$17.00 charged']);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $unpriced])
            ->assertSeeInOrder(['County recording fee', 'Not charged: bill at cost after recording']);
    });
});
