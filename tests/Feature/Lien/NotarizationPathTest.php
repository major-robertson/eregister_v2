<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Documents\LienCountyKey;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingCheckout;
use App\Domains\Lien\Livewire\FilingShow;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Support\NotarizationInstructions;
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
 * The notarization path (EREG-142). Today is Tuesday, October 20, 2026.
 * Kansas liens are sworn before a notary (lien_documents/ks.php) and
 * Johnson County e-records (lien_counties/ks/johnson.php). No data file
 * says a county needs the wet-ink original yet, so those cases fake a
 * county with LienDocumentRegistry::fake(). New Mexico has no data file:
 * every flag is unknown.
 */

/** Last furnished August 14: the Kansas lien is due Monday December 14, 2026. */
const NOTARY_DATES = [
    'first_furnish_date' => '2026-07-01',
    'last_furnish_date' => '2026-08-14',
    'completion_date' => '2026-08-14',
];

/** A Johnson County that takes mail only and needs the original signed document. */
const NOTARY_WET_INK_JOHNSON = [
    'county' => 'Johnson',
    'recording' => ['filing_office' => ['label' => 'Johnson County Register of Deeds', 'method' => 'mail']],
    'signature_requirements' => [
        'wet_ink_original_required' => true,
        'source' => 'Test county: originals only.',
    ],
];

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 12:00:00', 'America/New_York')->utc());
    Storage::fake('s3');
    Mail::fake();
    LienDocumentRegistry::flush();
    // Johnson County's lien recording fee (EREG-143) is left out: these
    // totals price the service on its own.
    LienDocumentRegistry::fake('KS', array_replace(require database_path('data/lien_counties/ks/johnson.php'), ['recording_fee_cents' => null]), 'johnson');
    config(['cashier.secret' => null]);

    (new PriceSeeder)->run();

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create(['lien_onboarding_completed_at' => now()]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

afterEach(fn () => LienDocumentRegistry::flush());

if (! function_exists('notaryProject')) {
    /** A general contractor's project (Johnson County, Kansas unless $attributes say otherwise) with an owner and its deadlines. */
    function notaryProject(Business $business, array $attributes = []): LienProject
    {
        $project = LienProject::factory()->forBusiness($business)->create(array_merge([
            'jobsite_state' => 'KS',
            'jobsite_county' => 'Johnson County',
            'claimant_type' => ClaimantType::Gc,
            'property_class' => 'residential',
            'apn' => '087-123-04-0-11-02-005.00',
        ], NOTARY_DATES, $attributes));

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
     * and a filing on it, full service unless $overrides say otherwise.
     *
     * @return array{0: LienProjectDeadline, 1: LienFiling}
     */
    function notaryFiling(LienProject $project, array $overrides = [], string $slug = 'mechanics_lien'): array
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
    function notaryReviewStep(LienProject $project, LienProjectDeadline $deadline): Testable
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
    function notaryStripe(): Mockery\MockInterface
    {
        $intents = Mockery::mock(PaymentIntentService::class);
        $customers = Mockery::mock(CustomerService::class);
        $customers->shouldReceive('create')->andReturn(Customer::constructFrom(['id' => 'cus_notary']));
        $customers->shouldReceive('retrieve')->andReturn(Customer::constructFrom(['id' => 'cus_notary']));

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

    function notaryIntent(string $id, int $amount): PaymentIntent
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

describe('signature requirements', function () {
    it('lays the county file over the state file', function () {
        $johnson = LienDocumentRegistry::signatureRequirements('KS', 'johnson');

        expect($johnson)->toMatchArray([
            'esign_accepted' => null,
            'notary_required' => true,
            'wet_ink_original_required' => false,
            'ron_accepted' => null,
        ])
            ->and($johnson['source'])->toStartWith('The claim is sworn before a notary (execution: jurat).')
            ->and($johnson['source'])->toContain('The June 2026 lien was e-recorded with the Register of Deeds');

        // A county without a file, or a file that knows nothing, keeps the state's flags.
        expect(LienDocumentRegistry::signatureRequirements('KS', 'sedgwick'))
            ->toMatchArray(['notary_required' => true, 'wet_ink_original_required' => null]);
        expect(LienDocumentRegistry::signatureRequirements('PA', 'butler'))
            ->toMatchArray(['notary_required' => false, 'wet_ink_original_required' => null]);
    });

    it('lets a known county value win over the state and ignores its unknowns', function () {
        LienDocumentRegistry::fake('KS', ['signature_requirements' => [
            'notary_required' => false,
            'wet_ink_original_required' => false,
            'source' => 'Test state.',
        ]]);
        LienDocumentRegistry::fake('KS', ['signature_requirements' => [
            'notary_required' => true,
            'wet_ink_original_required' => null,
            'source' => 'Test county.',
        ]], 'johnson');

        expect(LienDocumentRegistry::signatureRequirements('ks', 'johnson'))->toBe([
            'esign_accepted' => null,
            'notary_required' => true,
            'wet_ink_original_required' => false,
            'ron_accepted' => null,
            'source' => 'Test state. Test county.',
        ]);
    });

    it('gives a document kind its own block over the state', function () {
        // California's claim is verified without a notary; its release is acknowledged.
        expect(LienDocumentRegistry::signatureRequirements('CA', null)['notary_required'])->toBeFalse()
            ->and(LienDocumentRegistry::signatureRequirements('CA', null, 'mechanics_lien')['notary_required'])->toBeFalse()
            ->and(LienDocumentRegistry::signatureRequirements('CA', null, 'lien_release')['notary_required'])->toBeTrue()
            ->and(LienDocumentRegistry::signatureRequirements('FL', null, 'lien_release')['notary_required'])->toBeTrue();
    });

    it('leaves the letters and the states without a data file unknown', function () {
        expect(LienDocumentRegistry::signatureRequirements('KS', 'johnson', 'noi'))->toBe(LienDocumentRegistry::SIGNATURE_REQUIREMENTS)
            ->and(LienDocumentRegistry::signatureRequirements('FL', null, 'prelim_notice'))->toBe(LienDocumentRegistry::SIGNATURE_REQUIREMENTS)
            ->and(LienDocumentRegistry::signatureRequirements('KS', null, 'demand_letter'))->toBe(LienDocumentRegistry::SIGNATURE_REQUIREMENTS)
            ->and(LienDocumentRegistry::signatureRequirements('NM', 'bernalillo'))->toBe(LienDocumentRegistry::SIGNATURE_REQUIREMENTS)
            ->and(LienDocumentRegistry::for('NM')['signature_requirements'])->toBe(LienDocumentRegistry::SIGNATURE_REQUIREMENTS);
    });

    it('records online notarization only where the files say so', function () {
        expect(LienDocumentRegistry::signatureRequirements('FL', null)['ron_accepted'])->toBeTrue()
            ->and(LienDocumentRegistry::signatureRequirements('TN', 'sumner')['ron_accepted'])->toBeTrue()
            ->and(LienDocumentRegistry::signatureRequirements('TX', null)['ron_accepted'])->toBeNull();
    });

    it('lists the copy lines and the admin summary per flag', function () {
        $all = ['esign_accepted' => false, 'notary_required' => true, 'wet_ink_original_required' => true, 'ron_accepted' => null, 'source' => 'x'];

        expect(NotarizationInstructions::lines($all))->toBe([
            'Requires a notarized signature.',
            'The county needs the original signed document, mailed to us.',
        ])
            ->and(NotarizationInstructions::lines(['notary_required' => false, 'wet_ink_original_required' => false]))->toBe([])
            ->and(NotarizationInstructions::lines(LienDocumentRegistry::SIGNATURE_REQUIREMENTS))->toBe([])
            ->and(NotarizationInstructions::summary($all))->toBe('Notary required · Original required · E-sign not accepted')
            ->and(NotarizationInstructions::summary(['notary_required' => false, 'wet_ink_original_required' => false, 'ron_accepted' => true]))
            ->toBe('No notary · Original not required · Online notary accepted')
            ->and(NotarizationInstructions::summary(LienDocumentRegistry::SIGNATURE_REQUIREMENTS))->toBe('Unknown');
    });
});

describe('product pages', function () {
    it('says a Florida lien and release need a notarized signature', function () {
        $this->get('/liens/florida')->assertOk()
            ->assertSee('Requires a notarized signature.')
            ->assertDontSee('The county needs the original signed document');

        $this->get('/liens/lien-release/florida')->assertOk()
            ->assertSee('Requires a notarized signature.');
    });

    it('says nothing for unknown or false flags', function () {
        // New Mexico has no data file; California's claim is verified without a notary.
        $this->get('/liens/new-mexico')->assertOk()->assertDontSee('Requires a notarized signature.');
        $this->get('/liens/california')->assertOk()->assertDontSee('Requires a notarized signature.');

        // The California release is notarized.
        $this->get('/liens/lien-release/california')->assertOk()->assertSee('Requires a notarized signature.');
    });

    it('adds the original-document line where the state needs the original', function () {
        LienDocumentRegistry::fake('FL', ['signature_requirements' => [
            'notary_required' => true,
            'wet_ink_original_required' => true,
            'source' => 'Test state.',
        ]]);

        $this->get('/liens/florida')->assertOk()
            ->assertSeeInOrder(['Requires a notarized signature.', 'The county needs the original signed document, mailed to us.']);
    });
});

describe('review step', function () {
    it('shows the notary line for a Kansas lien and no original', function () {
        $project = notaryProject($this->business);
        [$deadline] = notaryFiling($project);

        notaryReviewStep($project, $deadline)
            ->assertSee('Requires a notarized signature.')
            ->assertDontSee('The county needs the original signed document')
            ->assertDontSee('Overnight the original to us');
    });

    it('shows nothing for a state with unknown flags', function () {
        $project = notaryProject($this->business, ['jobsite_state' => 'NM', 'jobsite_county' => 'Bernalillo']);
        [$deadline] = notaryFiling($project);

        notaryReviewStep($project, $deadline)
            ->assertDontSee('Requires a notarized signature.')
            ->assertDontSee('The county needs the original signed document');
    });

    it('adds the original line and the arrival date, with no fee, on full service in a wet-ink county', function () {
        LienDocumentRegistry::fake('KS', NOTARY_WET_INK_JOHNSON, 'johnson');

        $project = notaryProject($this->business);
        [$deadline, $filing] = notaryFiling($project);

        // Mail-in county: submit five business days before Monday December 14,
        // so by Monday December 7; the original must reach us a business day
        // earlier, Friday December 4.
        $component = notaryReviewStep($project, $deadline)
            ->assertSee('We must submit it to the county by December 7, 2026.')
            ->assertSeeInOrder([
                'Requires a notarized signature.',
                'The county needs the original signed document, mailed to us.',
                'Overnight the original to us.',
                'Your original must reach us by December 4, 2026.',
            ])
            ->assertDontSee('Overnight return of your original')
            ->assertDontSee('$25.00');

        $component->set('service_level', 'self_serve')
            ->assertSee('Requires a notarized signature.')
            ->assertDontSee('The county needs the original signed document')
            ->assertDontSee('Your original must reach us');

        $component->set('service_level', 'full_service')
            ->set('disclaimerAccepted', true)
            ->set('claimAttested', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        expect($filing->fresh()->must_submit_by->toDateString())->toBe('2026-12-07');
    });
});

describe('checkout', function () {
    it('charges a full-service order in a wet-ink county the service price alone', function () {
        LienDocumentRegistry::fake('KS', NOTARY_WET_INK_JOHNSON, 'johnson');

        $project = notaryProject($this->business);
        [, $filing] = notaryFiling($project, ['status' => FilingStatus::AwaitingPayment, 'must_submit_by' => '2026-12-07']);

        notaryStripe()->shouldReceive('create')
            ->once()
            ->withArgs(fn (array $params) => $params['amount'] === 29900)
            ->andReturn(notaryIntent('pi_original', 29900));

        Livewire::test(FilingCheckout::class, ['filing' => $filing])
            ->assertSet('amountCents', 29900)
            ->assertSee('Mechanics Lien (Full Service)')
            ->assertDontSee('Overnight return of your original');

        expect(Payment::whereMorphedTo('purchasable', $filing)->sole()->meta['items'])->toHaveCount(1)
            ->and(Price::where('product_family', 'lien')->where('product_key', 'overnight_original')->exists())->toBeFalse();
    });
});

describe('instructions', function () {
    it('picks the instruction set per case', function (string $state, string $county, string $slug, string $level, FilingStatus $status, ?array $fakeCounty, string $expected) {
        if ($fakeCounty !== null) {
            LienDocumentRegistry::fake($state, $fakeCounty, LienCountyKey::normalize($county));
        }

        $project = notaryProject($this->business, ['jobsite_state' => $state, 'jobsite_county' => $county]);
        [, $filing] = notaryFiling($project, ['status' => $status, 'service_level' => $level, 'paid_at' => now()], $slug);

        expect(NotarizationInstructions::key($filing))->toBe($expected)
            ->and(NotarizationInstructions::for($filing)['key'])->toBe($expected);
    })->with([
        'a Kansas lien goes to a notary and uploads' => ['KS', 'Johnson', 'mechanics_lien', 'full_service', FilingStatus::AwaitingNotary, null, 'notarize_and_upload'],
        'a Kansas lien in review still needs a notary' => ['KS', 'Johnson', 'mechanics_lien', 'full_service', FilingStatus::Paid, null, 'notarize_and_upload'],
        'a California claim signs online' => ['CA', 'Los Angeles', 'mechanics_lien', 'full_service', FilingStatus::Paid, null, 'esign'],
        'a California release goes to a notary' => ['CA', 'Los Angeles', 'lien_release', 'full_service', FilingStatus::Paid, null, 'notarize_and_upload'],
        'awaiting notary wins over the data' => ['CA', 'Los Angeles', 'mechanics_lien', 'full_service', FilingStatus::AwaitingNotary, null, 'notarize_and_upload'],
        'awaiting e-signature wins over the data' => ['KS', 'Johnson', 'mechanics_lien', 'full_service', FilingStatus::AwaitingEsign, null, 'esign'],
        'a notice of intent signs online' => ['KS', 'Johnson', 'noi', 'full_service', FilingStatus::Paid, null, 'esign'],
        'unknown flags follow the document: a notarized New Mexico lien' => ['NM', 'Bernalillo', 'mechanics_lien', 'full_service', FilingStatus::Paid, null, 'notarize_and_upload'],
        'a wet-ink county on full service mails the original' => ['KS', 'Johnson', 'mechanics_lien', 'full_service', FilingStatus::AwaitingNotary, NOTARY_WET_INK_JOHNSON, 'notarize_and_mail_original'],
        'a wet-ink county on self-serve uploads' => ['KS', 'Johnson', 'mechanics_lien', 'self_serve', FilingStatus::AwaitingNotary, NOTARY_WET_INK_JOHNSON, 'notarize_and_upload'],
        'e-sign not accepted goes to a notary' => ['CA', 'Los Angeles', 'mechanics_lien', 'full_service', FilingStatus::Paid, ['county' => 'Los Angeles', 'signature_requirements' => ['esign_accepted' => false, 'source' => 'Test county.']], 'notarize_and_upload'],
    ]);

    it('gives each set a heading and three to five short steps', function () {
        LienDocumentRegistry::fake('KS', NOTARY_WET_INK_JOHNSON, 'johnson');

        $project = notaryProject($this->business);
        [, $filing] = notaryFiling($project, ['status' => FilingStatus::AwaitingNotary, 'paid_at' => now(), 'must_submit_by' => '2026-12-07']);

        $sets = [];

        foreach ([FilingStatus::AwaitingEsign, FilingStatus::AwaitingNotary] as $status) {
            foreach (['self_serve', 'full_service'] as $level) {
                $filing->forceFill(['status' => $status, 'service_level' => $level]);
                $set = NotarizationInstructions::for($filing);
                $sets[$set['key']] = $set;
            }
        }

        expect(array_keys($sets))->toEqualCanonicalizing(['esign', 'notarize_and_upload', 'notarize_and_mail_original']);

        foreach ($sets as $set) {
            expect($set['heading'])->toBeString()->not->toBe('')
                ->and(count($set['steps']))->toBeGreaterThanOrEqual(3)->toBeLessThanOrEqual(5);
        }

        expect($sets['esign']['heading'])->toBe('Sign your mechanics lien online')
            ->and($sets['notarize_and_upload']['steps'])->toContain('Scan every page and upload the scan to us.')
            ->and($sets['notarize_and_mail_original']['heading'])->toBe('Sign your mechanics lien in front of a notary and mail us the original')
            ->and($sets['notarize_and_mail_original']['steps'])->toContain('The county needs the original. Overnight it to us at eRegister, 4869 Brownsboro Rd STE 101-E, Louisville, KY 40207.')
            ->and($sets['notarize_and_mail_original']['steps'])->toContain('It must reach us by December 4, 2026.')
            ->and(implode(' ', $sets['notarize_and_mail_original']['steps']))->not->toContain('$')->not->toContain('fee');
    });

    it('asks for the original promptly without a date', function () {
        LienDocumentRegistry::fake('KS', NOTARY_WET_INK_JOHNSON, 'johnson');

        $project = notaryProject($this->business);
        [, $filing] = notaryFiling($project, ['status' => FilingStatus::AwaitingNotary, 'paid_at' => now(), 'must_submit_by' => null]);

        $set = NotarizationInstructions::for($filing->fresh());

        expect($set['key'])->toBe('notarize_and_mail_original')
            ->and($set['steps'])->toContain('Send it as soon as it is signed.');
    });

    it('shows the instructions on the filing page only while we wait on a signature', function () {
        $project = notaryProject($this->business);
        [, $filing] = notaryFiling($project, ['status' => FilingStatus::AwaitingNotary, 'paid_at' => now()]);

        Livewire::test(FilingShow::class, ['filing' => $filing])
            ->assertSee('Sign your mechanics lien in front of a notary')
            ->assertSee('Take it and your photo ID to a notary. Sign it in front of the notary.')
            ->assertSee('Scan every page and upload the scan to us.');

        $filing->update(['status' => FilingStatus::AwaitingEsign]);

        Livewire::test(FilingShow::class, ['filing' => $filing->fresh()])
            ->assertSee('Sign your mechanics lien online')
            ->assertDontSee('Sign your mechanics lien in front of a notary');

        $filing->update(['status' => FilingStatus::Paid]);

        Livewire::test(FilingShow::class, ['filing' => $filing->fresh()])
            ->assertDontSee('Sign your mechanics lien online')
            ->assertDontSee('Sign your mechanics lien in front of a notary');
    });
});

describe('admin', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('lien.view', 'lien.update');
    });

    it('shows the merged signature requirements for the filing\'s county', function () {
        $project = notaryProject($this->business);
        [, $filing] = notaryFiling($project, ['status' => FilingStatus::Paid, 'paid_at' => now()]);

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Signature Requirements')
            ->assertSee('Notary required · Original not required')
            ->assertSee('The June 2026 lien was e-recorded with the Register of Deeds');
    });

    it('says Unknown where no file states anything', function () {
        $project = notaryProject($this->business, ['jobsite_state' => 'NM', 'jobsite_county' => 'Bernalillo']);
        [, $filing] = notaryFiling($project, ['status' => FilingStatus::Paid, 'paid_at' => now()]);

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Signature Requirements')
            ->assertSeeInOrder(['Signature Requirements', 'Unknown'])
            ->assertDontSee('Notary required');
    });
});
