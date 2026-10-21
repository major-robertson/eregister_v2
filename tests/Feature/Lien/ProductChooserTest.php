<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingCheckout;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Livewire\LienProductChooser;
use App\Domains\Lien\Livewire\ProjectForm;
use App\Domains\Lien\Livewire\ProjectList;
use App\Domains\Lien\Livewire\ProjectShow;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Models\LienStateRule;
use App\Domains\Lien\Seo\LienStatePage;
use App\Domains\Lien\Support\LienProductIntent;
use App\Domains\Lien\Support\RecordingTurnaround;
use App\Domains\Lien\Support\ServiceComparison;
use App\Models\Payment;
use App\Models\User;
use App\Support\SignupIntent;
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
 * "What do you need to happen?" (EREG-143): the product chooser on the
 * pricing page and the lien dashboard, its Start flow into sign-up or the
 * project flow, and the prerequisites on the wizard's first step. Today is
 * Tuesday, October 20, 2026.
 */

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 12:00:00', 'America/New_York')->utc());
    Storage::fake('s3');
    LienDocumentRegistry::flush();
});

afterEach(fn () => LienDocumentRegistry::flush());

if (! function_exists('chooserStart')) {
    /** The chooser's Start link for a product, with the state when given. */
    function chooserStart(string $product, ?string $state = null): string
    {
        return route('liens.start', array_filter(['product' => $product, 'state' => $state]));
    }

    /** A logged-in customer whose business has finished business and lien onboarding. */
    function chooserCustomer(object $test, array $attributes = []): Business
    {
        $test->user = User::factory()->create();
        $business = Business::factory()->onboarded()->withLienOnboarding()->create($attributes);
        $business->users()->attach($test->user, ['role' => 'owner']);

        $test->actingAs($test->user);
        session(['current_business_id' => $business->id]);

        return $business;
    }

    /** A project in $state with its deadlines; last furnished October 1, so every lien window is open. */
    function chooserProject(Business $business, string $state, ClaimantType $claimant = ClaimantType::Gc): LienProject
    {
        $project = LienProject::factory()->forBusiness($business)->create([
            'jobsite_state' => $state,
            'jobsite_county' => 'Test County',
            'claimant_type' => $claimant,
            'property_class' => 'residential',
            'first_furnish_date' => '2026-09-01',
            'last_furnish_date' => '2026-10-01',
            'completion_date' => '2026-10-01',
        ]);

        app(DeadlineCalculator::class)->calculateForProject($project);

        return $project;
    }

    /**
     * A general contractor's project in Johnson County, Kansas (e-records)
     * unless $attributes say otherwise, last furnished August 14, with an
     * owner and its deadlines.
     */
    function turnaroundProject(Business $business, array $attributes = []): LienProject
    {
        $project = LienProject::factory()->forBusiness($business)->create(array_merge([
            'jobsite_state' => 'KS',
            'jobsite_county' => 'Johnson County',
            'claimant_type' => ClaimantType::Gc,
            'property_class' => 'residential',
            'apn' => '087-123-04-0-11-02-005.00',
            'first_furnish_date' => '2026-07-01',
            'last_furnish_date' => '2026-08-14',
            'completion_date' => '2026-08-14',
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
     * @return array{0: ?LienProjectDeadline, 1: LienFiling}
     */
    function turnaroundFiling(LienProject $project, array $overrides = [], string $slug = 'mechanics_lien'): array
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
    function turnaroundReviewStep(LienProject $project, LienProjectDeadline $deadline): Testable
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
    function turnaroundStripe(): Mockery\MockInterface
    {
        $intents = Mockery::mock(PaymentIntentService::class);
        $customers = Mockery::mock(CustomerService::class);
        $customers->shouldReceive('create')->andReturn(Customer::constructFrom(['id' => 'cus_turnaround']));
        $customers->shouldReceive('retrieve')->andReturn(Customer::constructFrom(['id' => 'cus_turnaround']));

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

    function turnaroundIntent(string $id, int $amount): PaymentIntent
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

describe('cards', function () {
    it('shows six needs, with what each product does and does not do', function () {
        Livewire::test(LienProductChooser::class)
            ->assertSet('state', '')
            ->assertSeeInOrder([
                'Protect my right to lien later', 'Preliminary notice',
                'Warn them a lien is coming', 'Notice of intent to lien',
                'Put a lien on the property', 'Mechanics lien',
                'Ask for payment formally', 'Payment demand letter',
                'Remove a lien I filed', 'Lien release',
                'Public or government job', 'Bond claim',
            ])
            ->assertSee('It does not put a lien on the property.')
            ->assertSee('Liens do not attach to public property.')
            ->assertSee(route('contact'))
            ->assertSee(chooserStart('mechanics_lien'))
            ->assertSee('$299')
            ->assertDontSee('Deadline in')
            ->assertDontSee(chooserStart('bond_claim'));
    });

    it('names the state\'s deadline rule, price and turnaround on each card', function () {
        $lien = LienStatePage::forCode('CO')->headlineLienDeadline();

        expect($lien)->not->toBeNull();

        Livewire::test(LienProductChooser::class)
            ->set('state', 'co')
            ->assertSet('state', 'CO')
            ->assertSee('Colorado does not require one to keep lien rights.')
            ->assertSee('Deadline in Colorado: at least 10 days before the lien statement is filed.')
            ->assertSee("Deadline in Colorado: {$lien}.")
            ->assertSee('No deadline. You can send one at any time.')
            ->assertSee('Record it once you have been paid.')
            ->assertSee('Typically 1 to 5 business days after we submit.')
            ->assertSee(chooserStart('mechanics_lien', 'CO'))
            ->assertDontSee('$899');

        $prelim = LienStatePage::forCode('TX')->headlineDeadline('prelim_notice');

        Livewire::test(LienProductChooser::class)
            ->set('state', 'TX')
            ->assertSee("Deadline in Texas: {$prelim}.")
            ->assertSee('Texas does not require one. You can still send one before you file.')
            ->set('state', 'NJ')
            ->assertSee('$899');
    });

    it('starts a customer on their latest project\'s state, else their business address', function () {
        $business = chooserCustomer($this, ['business_address' => ['line1' => '1 Main St', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78701']]);

        Livewire::test(LienProductChooser::class)->assertSet('state', 'TX');

        LienProject::factory()->forBusiness($business)->create(['jobsite_state' => 'FL']);

        Livewire::test(LienProductChooser::class)->assertSet('state', 'FL');
    });
});

describe('prerequisites', function () {
    it('names the notice of intent on the lien card where the state requires one', function () {
        Livewire::test(LienProductChooser::class)
            ->set('state', 'CO')
            ->assertSee("Colorado requires a notice of intent at least 10 days before the lien statement is filed. Order it first if you haven't.")
            ->assertSee('Start a notice of intent')
            ->assertSee(chooserStart('noi', 'CO'))
            ->assertDontSee('Send it first');
    });

    it('says some claimants where only some need it, and names the state\'s own preliminary notice', function () {
        Livewire::test(LienProductChooser::class)
            ->set('state', 'MO')
            ->assertSeeInOrder([
                "Missouri requires a notice of intent from some claimants at least 10 days before the lien is filed. Order it first if you haven't.",
                "Missouri requires a Notice to Owner (Original Contractor's Disclosure) to keep lien rights. Send it first if you haven't.",
            ]);
    });

    it('names Florida\'s Notice to Owner', function () {
        Livewire::test(LienProductChooser::class)
            ->set('state', 'FL')
            ->assertSee("Florida requires a Notice to Owner from subcontractors and suppliers to keep lien rights. Send it first if you haven't.")
            ->assertSee('Start a Notice to Owner')
            ->assertSee(chooserStart('prelim_notice', 'FL'))
            ->assertDontSee('Order it first');
    });

    it('names none where the state requires neither', function () {
        Livewire::test(LienProductChooser::class)
            ->set('state', 'NY')
            ->assertDontSee('Order it first')
            ->assertDontSee('Send it first');
    });
});

describe('states we do not serve', function () {
    it('points a lien in an attorney state to an attorney and keeps the other documents', function () {
        Livewire::test(LienProductChooser::class)
            ->set('state', 'HI')
            ->assertSee("We don't file mechanics liens in Hawaii. We can connect you with a Hawaii attorney who specializes in construction lien filings.")
            ->assertDontSee(chooserStart('mechanics_lien', 'HI'))
            ->assertSee(chooserStart('prelim_notice', 'HI'))
            ->assertSee(chooserStart('lien_release', 'HI'))
            ->set('state', 'MD')
            ->assertSee("We don't file mechanics liens in Maryland.")
            ->assertDontSee('Maryland requires a notice of intent');
    });

    it('says so for every document in a state without lien rules', function () {
        LienStateRule::query()->whereKey('WY')->delete();

        Livewire::test(LienProductChooser::class)
            ->set('state', 'WY')
            ->assertSee("We don't file preliminary notices in Wyoming.")
            ->assertSee("We don't file mechanics liens in Wyoming.")
            ->assertSee("We don't file lien releases in Wyoming.")
            ->assertDontSee(chooserStart('demand_letter', 'WY'))
            ->assertSee(route('contact'));
    });
});

describe('start', function () {
    it('sends a visitor to sign-up with the product and state kept', function () {
        $this->get(chooserStart('mechanics_lien', 'tx'))->assertRedirect(route('register'));

        expect(LienProductIntent::get())->toBe(['product' => 'mechanics_lien', 'state' => 'TX'])
            ->and(SignupIntent::get())->toMatchArray(['product' => 'liens', 'intent' => 'mechanics-lien', 'state' => 'TX']);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create your account to start your Texas mechanics lien');
    });

    it('turns away anything that is not an orderable product', function () {
        $this->get(route('liens.start', ['product' => 'bond_claim']))->assertNotFound();
        $this->get(route('liens.start'))->assertNotFound();
    });

    it('takes a customer with no project to a new project in the chosen state', function () {
        chooserCustomer($this);

        $this->get(chooserStart('noi', 'CO'))->assertRedirect(route('lien.projects.create'));

        Livewire::test(ProjectForm::class)->assertSet('jobsite_state', 'CO');
    });

    it('takes a customer with one project to it, which offers the product first until it starts', function () {
        $business = chooserCustomer($this);
        $project = chooserProject($business, 'KS');
        $deadline = $project->getDeadlineForType('mechanics_lien');

        $this->get(chooserStart('mechanics_lien', 'KS'))->assertRedirect(route('lien.projects.show', $project));

        Livewire::test(ProjectShow::class, ['project' => $project])
            ->assertSee('You picked:')
            ->assertSee('Start your mechanics lien')
            ->call('startFiling', $deadline->id)
            ->assertRedirect(route('lien.filings.start', ['project' => $project, 'deadline' => $deadline]));

        expect(LienProductIntent::get())->toBeNull();

        Livewire::test(ProjectShow::class, ['project' => $project])->assertDontSee('You picked:');
    });

    it('forgets the choice on Not now', function () {
        $business = chooserCustomer($this);
        $project = chooserProject($business, 'KS');
        LienProductIntent::store(['product' => 'demand_letter', 'state' => 'KS']);

        Livewire::test(ProjectShow::class, ['project' => $project])
            ->assertSee('Payment demand letter')
            ->call('dismissChosenProduct')
            ->assertDontSee('You picked:');

        expect(LienProductIntent::get())->toBeNull();
    });

    it('takes a customer with several projects to the list, which says what they picked', function () {
        $business = chooserCustomer($this);
        LienProject::factory()->forBusiness($business)->count(2)->create();

        $this->get(chooserStart('noi', 'CO'))->assertRedirect(route('lien.projects.index'));

        Livewire::test(ProjectList::class)
            ->assertSee('You picked: Notice of intent to lien. Open the project it is for, or add a new project.');
    });
});

describe('pages', function () {
    it('puts the chooser on the pricing page with Livewire loaded', function () {
        app('livewire')->flushState();

        $html = $this->get('/liens/pricing')
            ->assertOk()
            ->assertSee('What do you need to happen?')
            ->assertSee(chooserStart('mechanics_lien'))
            ->assertSee('Choose a state above to compare self-serve and full service there.')
            ->assertSee('Lien Filing Pricing | Flat Rates, State Fees Included')
            ->assertSee('State fees included')
            ->assertSee('Do your prices include state filing fees?')
            ->assertDontSee('recording fee at cost')
            ->assertDontSee('billed at cost', false)
            ->getContent();

        expect($html)->toMatch('/livewire(\.min)?\.js/');

        app('livewire')->flushState();
    });

    it('compares self-serve and full service for the chosen document and state', function () {
        Livewire::test(LienProductChooser::class, ['compare' => true])
            ->assertSee('Choose a state above to compare')
            ->set('state', 'CO')
            ->assertSeeInOrder([
                'Self-serve and full service, side by side in Colorado',
                'Who signs it', 'You print it and sign it in front of a notary.', 'You sign it in front of a notary and upload a scan.',
                'Who records it', 'You record it with the county.',
                'Typical turnaround', 'Typically 1 to 5 business days after you submit.', 'Typically 1 to 5 business days after we submit.',
            ])
            ->assertDontSee('County recording fee')
            ->assertDontSee('Overnight original')
            ->set('document', 'noi')
            ->assertSee('We mail it by certified mail.')
            ->assertDontSee('Who records it')
            ->set('document', 'unknown')
            ->assertSet('document', 'mechanics_lien')
            ->set('state', 'HI')
            ->assertSeeInOrder(['Self-serve and full service, side by side in Hawaii', "We don't file mechanics liens in Hawaii."])
            ->assertDontSee('Who signs it');
    });

    it('leaves the comparison off the dashboard', function () {
        Livewire::test(LienProductChooser::class)
            ->set('state', 'CO')
            ->assertDontSee('side by side')
            ->assertDontSee('Who signs it');
    });

    it('shows the chooser on the lien dashboard', function () {
        chooserCustomer($this);

        $this->get(route('lien.dashboard'))
            ->assertOk()
            ->assertSee('What do you need to happen?');
    });
});

describe('turnaround and comparison', function () {
    beforeEach(function () {
        Mail::fake();
        config(['cashier.secret' => null]);
        (new PriceSeeder)->run();

        $this->business = chooserCustomer($this);
    });

    it('uses the county method, else the state method', function () {
        // Johnson County e-records; Kansas offices take mail.
        expect(RecordingTurnaround::sentence('KS', 'johnson'))->toBe('Typically 1 to 5 business days after we submit.')
            ->and(RecordingTurnaround::sentence('KS', 'sedgwick'))->toBe('Typically 2 to 6 weeks after we submit.')
            ->and(RecordingTurnaround::sentence('KS'))->toBe('Typically 2 to 6 weeks after we submit.')
            ->and(RecordingTurnaround::words('ZZ'))->toBeNull()
            ->and(RecordingTurnaround::weRecord('full_service', 'lien_release'))->toBeTrue()
            ->and(RecordingTurnaround::weRecord('self_serve', 'mechanics_lien'))->toBeFalse()
            ->and(RecordingTurnaround::weRecord('full_service', 'noi'))->toBeFalse();
    });

    it('takes a county file\'s own words, as the milestone email does', function () {
        LienDocumentRegistry::fake('KS', ['county' => 'Test', 'recording' => ['filing_office' => ['method' => 'mail'], 'turnaround' => ['mail' => '3 to 4 weeks']]], 'test');

        expect(RecordingTurnaround::sentence('KS', 'test'))->toBe('Typically 3 to 4 weeks after we submit.');

        LienDocumentRegistry::fake('KS', ['county' => 'Test', 'recording' => ['turnaround' => 'about a week']], 'test');

        expect(RecordingTurnaround::sentence('KS', 'test'))->toBe('Typically about a week after we submit.');
    });

    it('shows the turnaround and no county fee on a full-service lien\'s review step', function () {
        $project = turnaroundProject($this->business);
        [$deadline] = turnaroundFiling($project);

        $component = turnaroundReviewStep($project, $deadline)
            ->assertSeeInOrder(['Recording with the county', 'Typically 1 to 5 business days after we submit.'])
            ->assertViewHas('recordingTurnaround', 'Typically 1 to 5 business days after we submit.')
            ->assertDontSee('County recording fee')
            ->assertDontSee('billed at cost');

        $component->set('service_level', 'self_serve')
            ->assertViewHas('recordingTurnaround', null)
            ->assertDontSee('Recording with the county');

        [$noticeDeadline] = turnaroundFiling($project, [], 'noi');

        Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $noticeDeadline])
            ->set('step', 4)
            ->assertViewHas('recordingTurnaround', null)
            ->assertDontSee('Recording with the county');
    });

    it('compares self-serve and full service for the county, with no fee rows', function () {
        $project = turnaroundProject($this->business);
        [$deadline] = turnaroundFiling($project);

        turnaroundReviewStep($project, $deadline)
            ->assertSee('Self-serve and full service, side by side')
            ->assertSeeInOrder([
                'Who prepares it', 'We do, from your answers.', 'We do, and our team checks it before it goes out.',
                'Who signs it', 'You print it and sign it in front of a notary.', 'You sign it in front of a notary and upload a scan.',
                'Who records it', 'You record it with the county.', 'We record it with the county and send copies to every party the law requires.',
                'Typical turnaround', 'Typically 1 to 5 business days after you submit.', 'Typically 1 to 5 business days after we submit.',
                'What you get back', 'The document as a PDF, with filing instructions.', 'The recorded copy and proof of mailing.',
            ])
            ->assertDontSee('County recording fee')
            ->assertDontSee('Overnight original');

        expect(array_column(ServiceComparison::rows('mechanics_lien', 'KS', 'johnson'), 'label'))->toBe([
            'Who prepares it', 'Who signs it', 'Who records it', 'Typical turnaround', 'What you get back',
        ]);
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

    it('says who overnights the original where the county needs it, with no charge for it', function () {
        LienDocumentRegistry::fake('KS', [
            'county' => 'Johnson',
            'recording' => ['filing_office' => ['method' => 'mail']],
            'signature_requirements' => ['wet_ink_original_required' => true, 'source' => 'Test county.'],
        ], 'johnson');

        $rows = collect(ServiceComparison::rows('mechanics_lien', 'KS', 'johnson'))->keyBy('label');

        expect($rows)->not->toHaveKey('Overnight original')
            ->and($rows)->not->toHaveKey('County recording fee')
            ->and($rows['Who signs it']['full_service'])->toBe('You sign it in front of a notary and overnight the original to us.')
            ->and($rows['Typical turnaround']['full_service'])->toBe('Typically 2 to 6 weeks after we submit.');
    });

    it('charges the flat price alone at checkout and shows the turnaround', function (array $attributes, array $filing, string $slug, int $amount, ?string $turnaround) {
        $project = turnaroundProject($this->business, $attributes);
        [, $order] = turnaroundFiling($project, ['status' => FilingStatus::AwaitingPayment, ...$filing], $slug);

        turnaroundStripe()->shouldReceive('create')
            ->once()
            ->withArgs(fn (array $params) => $params['amount'] === $amount)
            ->andReturn(turnaroundIntent('pi_flat', $amount));

        $component = Livewire::test(FilingCheckout::class, ['filing' => $order])
            ->assertSet('amountCents', $amount)
            ->assertDontSee('County recording fee')
            ->assertDontSee('billed at cost');

        $turnaround === null
            ? $component->assertDontSee('after we submit')
            : $component->assertSee($turnaround);

        expect(Payment::whereMorphedTo('purchasable', $order)->sole()->meta['items'])->toHaveCount(1);
    })->with([
        'full-service lien in Johnson County' => [[], ['must_submit_by' => '2026-12-11'], 'mechanics_lien', 29900, 'Typically 1 to 5 business days after we submit.'],
        'full-service lien in a mail-in county' => [['jobsite_county' => 'Sedgwick'], ['must_submit_by' => '2026-12-07'], 'mechanics_lien', 29900, 'Typically 2 to 6 weeks after we submit.'],
        'full-service release' => [[], [], 'lien_release', 9900, 'Typically 1 to 5 business days after we submit.'],
        'self-serve lien' => [[], ['service_level' => 'self_serve'], 'mechanics_lien', 9900, null],
    ]);
});

describe('wizard first step', function () {
    beforeEach(function () {
        $this->business = chooserCustomer($this);
    });

    it('names the notice of intent and links its step on a mechanics lien', function () {
        $project = chooserProject($this->business, 'CO');
        $noi = $project->getDeadlineForType('noi');
        $sentence = "Colorado requires a notice of intent at least 10 days before the lien statement is filed. Order it first if you haven't.";

        Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $project->getDeadlineForType('mechanics_lien')])
            ->assertSet('step', 1)
            ->assertSee($sentence)
            ->assertSee('Order the notice of intent')
            ->assertSee(route('lien.filings.start', ['project' => $project, 'deadline' => $noi]))
            ->set('project_type_category', 'residential')
            ->call('nextStep')
            ->assertSet('step', 2)
            ->assertDontSee($sentence);
    });

    it('names Florida\'s Notice to Owner for a subcontractor, not for a contractor hired by the owner', function () {
        $sub = chooserProject($this->business, 'FL', ClaimantType::Subcontractor);

        Livewire::test(FilingWizard::class, ['project' => $sub, 'deadline' => $sub->getDeadlineForType('mechanics_lien')])
            ->assertSee("Florida requires a Notice to Owner to keep lien rights. Send it first if you haven't.")
            ->assertSee('Order the Notice to Owner')
            ->assertSee(route('lien.filings.start', ['project' => $sub, 'deadline' => $sub->getDeadlineForType('prelim_notice')]));

        $gc = chooserProject($this->business, 'FL');

        Livewire::test(FilingWizard::class, ['project' => $gc, 'deadline' => $gc->getDeadlineForType('mechanics_lien')])
            ->assertViewHas('prerequisites', [])
            ->assertDontSee('Send it first');
    });

    it('says nothing on other documents or where the state requires neither', function () {
        $colorado = chooserProject($this->business, 'CO');

        Livewire::test(FilingWizard::class, ['project' => $colorado, 'deadline' => $colorado->getDeadlineForType('noi')])
            ->assertViewHas('prerequisites', [])
            ->assertDontSee('Order it first');

        $newYork = chooserProject($this->business, 'NY');

        Livewire::test(FilingWizard::class, ['project' => $newYork, 'deadline' => $newYork->getDeadlineForType('mechanics_lien')])
            ->assertViewHas('prerequisites', []);
    });

    it('drops a prerequisite the project has already ordered', function () {
        $project = chooserProject($this->business, 'CO');
        $noi = $project->getDeadlineForType('noi');

        LienFiling::factory()->forProject($project)->create([
            'document_type_id' => $noi->document_type_id,
            'project_deadline_id' => $noi->id,
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
            'service_level' => 'full_service',
        ]);

        Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $project->getDeadlineForType('mechanics_lien')])
            ->assertViewHas('prerequisites', [])
            ->assertDontSee('Order it first');
    });
});
