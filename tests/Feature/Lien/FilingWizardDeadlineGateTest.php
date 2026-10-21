<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Actions\UpdateLienDocumentDetails;
use App\Domains\Lien\Admin\Livewire\LienBoard;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Models\LienDeadlineRule;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingEvent;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Seo\NoticeOfIntentStatePage;
use App\Models\Price;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

beforeEach(function () {
    // Tuesday, October 20, 2026, noon Eastern.
    $this->travelTo(CarbonImmutable::parse('2026-10-20 12:00:00', 'America/New_York')->utc());
    Storage::fake('s3');
    LienDocumentRegistry::flush();

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create(['lien_onboarding_completed_at' => now()]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

afterEach(fn () => LienDocumentRegistry::flush());

if (! function_exists('gateProject')) {
    /**
     * A general contractor's project with an owner and its deadlines. Kansas
     * mechanics liens are due 4 months after last furnishing; Sedgwick County
     * has no county file, so the state's mail-in office applies.
     */
    function gateProject(Business $business, string $state = 'KS', array $overrides = []): LienProject
    {
        $project = LienProject::factory()->forBusiness($business)->create(array_merge([
            'jobsite_state' => $state,
            'jobsite_county' => 'Sedgwick',
            'claimant_type' => ClaimantType::Gc,
            'property_class' => 'residential',
            'first_furnish_date' => '2026-07-01',
            'last_furnish_date' => '2026-08-14',
            'completion_date' => '2026-08-14',
        ], $overrides));

        app(DeadlineCalculator::class)->calculateForProject($project);

        LienParty::create([
            'business_id' => $business->id,
            'project_id' => $project->id,
            'role' => 'owner',
            'name' => 'Test Owner',
            'address1' => '123 Main St',
            'city' => 'Anytown',
            'state' => $state,
            'zip' => '12345',
        ]);

        return $project;
    }

    /**
     * The project's deadline for a document type and a full-service draft on it.
     *
     * @return array{0: LienProjectDeadline, 1: LienFiling}
     */
    function gateFiling(LienProject $project, string $slug, array $overrides = []): array
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

    /** Steps 1 and 2 are complete from the project; fill the rest of the Work step. */
    function gateWorkStep(LienProject $project, LienProjectDeadline $deadline): Testable
    {
        return Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->call('nextStep')
            ->call('nextStep')
            ->assertSet('step', 3)
            ->set('has_written_contract', '1')
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.');
    }

    /** The first state in notice_of_intent.php with this status, where we file liens online. */
    function gateNoiState(string $status, ?callable $also = null): ?string
    {
        $attorneyOnly = config('lien.attorney_referral_states', []);

        return collect(NoticeOfIntentStatePage::entries())
            ->filter(fn (array $entry, string $state) => $entry['status'] === $status
                && ! in_array($state, $attorneyOnly, true)
                && ($also === null || $also($state)))
            ->keys()
            ->first();
    }

    function gateNoiLeadDays(string $state): int
    {
        return (int) LienDeadlineRule::where('state', $state)
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'noi'))
            ->where('offset_days', '>', 0)
            ->max('offset_days');
    }
}

/** Dates well inside every lien window, for the notice-of-intent tests. */
const GATE_RECENT_DATES = [
    'first_furnish_date' => '2026-09-01',
    'last_furnish_date' => '2026-10-15',
    'completion_date' => '2026-10-15',
];

const GATE_PAST_DATES = [
    'first_furnish_date' => '2026-01-05',
    'last_furnish_date' => '2026-03-02',
    'completion_date' => '2026-03-02',
];

describe('deadline panel', function () {
    it('shows the deadline and the submit-by date for an e-recording county, and stores them at checkout', function () {
        // Johnson County, Kansas e-records. Deadline Monday December 14; one business day back.
        $project = gateProject($this->business, 'KS', ['jobsite_county' => 'Johnson County']);
        [$deadline, $filing] = gateFiling($project, 'mechanics_lien');

        gateWorkStep($project, $deadline)
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4)
            ->assertSee('Mechanics Lien deadline')
            ->assertSee('December 14, 2026')
            ->assertSee('We must submit it to the county by December 11, 2026.')
            ->assertSee('36 business days left.')
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        $filing->refresh();

        expect($filing->status)->toBe(FilingStatus::AwaitingPayment)
            ->and($filing->must_submit_by->toDateString())->toBe('2026-12-11')
            ->and($filing->deadline_business_days_left)->toBe(36)
            ->and($filing->deadline_block_shown_at)->toBeNull();
    });

    it('counts five business days back for a mail-in county', function () {
        $project = gateProject($this->business, 'KS');
        [$deadline, $filing] = gateFiling($project, 'mechanics_lien');

        gateWorkStep($project, $deadline)
            ->call('nextStep')
            ->assertSet('step', 4)
            ->assertSee('December 14, 2026')
            ->assertSee('We must submit it to the county by December 7, 2026.')
            ->assertSee('32 business days left.')
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertRedirect(route('lien.filings.checkout', $filing));

        expect($filing->fresh()->must_submit_by->toDateString())->toBe('2026-12-07')
            ->and($filing->fresh()->deadline_business_days_left)->toBe(32);
    });

    it('reads the lead times from config', function () {
        config(['lien.recording_lead_business_days.mail' => 10]);

        $project = gateProject($this->business, 'KS');
        [$deadline] = gateFiling($project, 'mechanics_lien');

        gateWorkStep($project, $deadline)
            ->call('nextStep')
            ->assertSee('We must submit it to the county by November 30, 2026.');
    });

    it('waits for the dates before promising a deadline', function () {
        $project = gateProject($this->business, 'KS', [
            'first_furnish_date' => null,
            'last_furnish_date' => null,
            'completion_date' => null,
        ]);
        [$deadline, $filing] = gateFiling($project, 'mechanics_lien');

        Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->set('step', 4)
            ->assertSee("We'll confirm the deadline once we have your dates.", false)
            ->assertDontSee('We must submit it')
            ->assertSee('Proceed to Payment')
            ->set('has_written_contract', '1')
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.')
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        expect($filing->fresh()->must_submit_by)->toBeNull()
            ->and($filing->fresh()->deadline_business_days_left)->toBeNull();
    });
});

describe('past the deadline', function () {
    it('blocks checkout and shows the dates used and the alternatives', function () {
        Price::create([
            'product_family' => 'lien',
            'product_key' => 'demand_letter',
            'variant_key' => 'full_service',
            'billing_type' => 'one_time',
            'amount_cents' => 4900,
            'currency' => 'usd',
            'active' => true,
        ]);

        // Last furnished March 2: the Kansas lien was due July 2.
        $project = gateProject($this->business, 'KS', GATE_PAST_DATES);
        [$deadline, $filing] = gateFiling($project, 'mechanics_lien');
        $demand = $project->getDeadlineForType('demand_letter');

        $component = gateWorkStep($project, $deadline)
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4)
            ->assertSee("We're unable to file this lien.", false)
            ->assertSee('Based on the dates you provided, our system calculated the recording deadline as July 2, 2026. That date has passed.')
            ->assertSee('Dates we used')
            ->assertSee('Last Furnish Date')
            ->assertSee('March 2, 2026')
            ->assertDontSee('First Furnish Date')
            ->assertSee('Check these dates')
            ->assertSee('These dates go on a sworn document. A wrong date can make the lien unenforceable.')
            ->assertSee('Demand letter')
            ->assertSee('$49.00')
            ->assertSee(route('lien.filings.start', ['project' => $project, 'deadline' => $demand]), false)
            ->assertSee("We don't offer a notice of intent here: it says a lien is coming, and the lien window has closed.", false)
            ->assertSee('Talk to us')
            ->assertSee(route('contact'), false)
            ->assertDontSee('Choose Your Service Level')
            ->assertDontSee('Proceed to Payment');

        $component->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasErrors(['deadline_passed' => fn (array $rules, array $messages) => $messages === ["We're unable to file this lien: the deadline has passed."]])
            ->assertNoRedirect();

        expect($filing->fresh()->status)->toBe(FilingStatus::Draft);

        $component->call('goToStep', 3)->assertSet('step', 3);
    });

    it('records the first time the block is shown, once', function () {
        $project = gateProject($this->business, 'KS', GATE_PAST_DATES);
        [$deadline, $filing] = gateFiling($project, 'mechanics_lien');

        $component = gateWorkStep($project, $deadline);
        expect($filing->fresh()->deadline_block_shown_at)->toBeNull();

        $component->call('nextStep')->assertSee('has passed');
        $shownAt = $filing->fresh()->deadline_block_shown_at;

        expect($shownAt)->not->toBeNull()
            ->and($shownAt->equalTo(now()))->toBeTrue();

        $this->travel(2)->hours();
        $component->call('$refresh')->assertSee('has passed');

        expect($filing->fresh()->deadline_block_shown_at->equalTo($shownAt))->toBeTrue();
    });

    it('warns about a preliminary notice past its usual deadline and leaves checkout open', function () {
        // Arizona preliminary notices are due 20 days after first furnishing.
        $project = gateProject($this->business, 'AZ', GATE_PAST_DATES + ['jobsite_county' => 'Maricopa']);
        [$deadline, $filing] = gateFiling($project, 'prelim_notice');

        $component = gateWorkStep($project, $deadline)
            ->call('nextStep')
            ->assertSet('step', 4)
            ->assertSee('This notice may be late.')
            ->assertSee('Based on the dates you provided, our system calculated the usual deadline for this notice as January 25, 2026, which has passed. A late notice may protect only part of your claim, or none, depending on the state. If the dates are right, you can still send it.')
            ->assertSee('Dates we used')
            ->assertSee('First Furnish Date')
            ->assertSee('January 5, 2026')
            ->assertDontSee('Last Furnish Date')
            ->assertSee('Check these dates')
            ->assertSee('Talk to us')
            ->assertDontSee("We're unable to", false)
            ->assertDontSee('We must send it by')
            ->assertSee('Choose Your Service Level')
            ->assertSee('Proceed to Payment');

        expect($filing->fresh()->deadline_block_shown_at)->toBeNull();

        // A date changed after the warning is not logged, and checkout goes through.
        $component->call('goToStep', 3)
            ->set('first_furnish_date', '2026-01-06')
            ->call('nextStep')
            ->assertSee('our system calculated the usual deadline for this notice as January 26, 2026, which has passed.')
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        expect($filing->events()->where('event_type', LienFilingEvent::DATES_CHANGED_AFTER_DEADLINE_BLOCK)->count())->toBe(0)
            ->and($filing->fresh()->deadline_block_shown_at)->toBeNull()
            ->and($filing->fresh()->status)->toBe(FilingStatus::AwaitingPayment);
    });

    it('blocks a notice of intent once the lien deadline has passed', function () {
        Price::create([
            'product_family' => 'lien',
            'product_key' => 'demand_letter',
            'variant_key' => 'full_service',
            'billing_type' => 'one_time',
            'amount_cents' => 4900,
            'currency' => 'usd',
            'active' => true,
        ]);

        // Arkansas liens are due 120 days after last furnishing: June 30 for March 2.
        $project = gateProject($this->business, 'AR', GATE_PAST_DATES + ['jobsite_county' => 'Pulaski']);
        [$deadline, $filing] = gateFiling($project, 'noi');

        gateWorkStep($project, $deadline)
            ->call('nextStep')
            ->assertSet('step', 4)
            ->assertSee("We're unable to send this notice of intent.", false)
            ->assertSee('Based on the dates you provided, our system calculated the lien deadline as June 30, 2026. That date has passed, so there is no lien left to announce.')
            ->assertSee('Dates we used')
            ->assertSee('Last Furnish Date')
            ->assertSee('March 2, 2026')
            ->assertSee('Check these dates')
            ->assertSee('These dates go on a sworn document. A wrong date can make the lien unenforceable.')
            ->assertSee('Demand letter')
            ->assertSee('$49.00')
            ->assertSee('Talk to us')
            ->assertDontSee("We don't offer a notice of intent here", false)
            ->assertDontSee('Proceed to Payment')
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasErrors(['deadline_passed' => fn (array $rules, array $messages) => $messages === ["We're unable to send this notice of intent: the lien deadline has passed."]])
            ->assertNoRedirect();

        expect($filing->fresh()->deadline_block_shown_at)->not->toBeNull()
            ->and($filing->fresh()->status)->toBe(FilingStatus::Draft);
    });

    it('does not block a notice of intent on its own deadline while the lien deadline is open', function () {
        // Last furnished June 27: the Arkansas lien is due October 25 and the
        // notice of intent, ten days earlier, on October 15 (passed).
        $project = gateProject($this->business, 'AR', [
            'jobsite_county' => 'Pulaski',
            'first_furnish_date' => '2026-06-01',
            'last_furnish_date' => '2026-06-27',
            'completion_date' => '2026-06-27',
        ]);
        [$deadline, $filing] = gateFiling($project, 'noi');

        gateWorkStep($project, $deadline)
            ->call('nextStep')
            ->assertSet('step', 4)
            ->assertSee('Notice of Intent to Lien deadline')
            ->assertSee('October 15, 2026')
            ->assertSee('We must send it by')
            ->assertDontSee('has passed')
            ->assertSee('Proceed to Payment')
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        expect($filing->fresh()->deadline_block_shown_at)->toBeNull();
    });

    it('does not block a notice of intent when the lien deadline is not known', function () {
        $project = gateProject($this->business, 'AR', [
            'jobsite_county' => 'Pulaski',
            'first_furnish_date' => null,
            'last_furnish_date' => null,
            'completion_date' => null,
        ]);
        [$deadline, $filing] = gateFiling($project, 'noi');

        Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->set('step', 4)
            ->assertSee("We'll confirm the deadline once we have your dates.", false)
            ->assertDontSee('has passed')
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors('deadline_passed');

        expect($filing->fresh()->deadline_block_shown_at)->toBeNull();
    });

    it('never blocks a demand letter or a lien release', function (string $slug) {
        $project = gateProject($this->business, 'KS', GATE_PAST_DATES);
        [$deadline, $filing] = gateFiling($project, $slug);

        Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->set('step', 4)
            ->assertDontSee('has passed')
            ->assertDontSee('Dates we used')
            ->assertDontSee('We must submit it')
            ->assertSee('Choose Your Service Level')
            ->assertSee('Proceed to Payment')
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors('deadline_passed');

        expect($filing->fresh()->deadline_block_shown_at)->toBeNull();
    })->with(['demand_letter', 'lien_release']);
});

describe('dates changed after a block', function () {
    it('logs a change only after a block, and only when a date changed', function () {
        $project = gateProject($this->business, 'KS', GATE_PAST_DATES);
        [$deadline, $filing] = gateFiling($project, 'mechanics_lien');
        $events = fn () => $filing->events()->where('event_type', LienFilingEvent::DATES_CHANGED_AFTER_DEADLINE_BLOCK);

        // Before any block, a changed date is not logged.
        $component = gateWorkStep($project, $deadline)
            ->set('first_furnish_date', '2026-01-06')
            ->call('nextStep')
            ->assertSee('has passed');

        expect($events()->count())->toBe(0);

        // Back to the Work step and on again with the same dates: nothing changed.
        $component->call('goToStep', 3)->call('nextStep')->assertSet('step', 4);

        expect($events()->count())->toBe(0);

        // A changed date after the block is logged with what it was and what it is.
        $component->call('goToStep', 3)
            ->set('last_furnish_date', '2026-10-01')
            ->call('nextStep')
            ->assertSet('step', 4)
            ->assertDontSee('has passed')
            ->assertSee('We must submit it to the county by');

        $event = $events()->sole();

        expect($event->payload_json)->toEqual([
            'before' => ['first_furnish_date' => '2026-01-06', 'last_furnish_date' => '2026-03-02', 'completion_date' => '2026-03-02'],
            'after' => ['first_furnish_date' => '2026-01-06', 'last_furnish_date' => '2026-10-01', 'completion_date' => '2026-03-02'],
        ])
            ->and($event->created_by)->toBe($this->user->id)
            ->and($event->description())->toBe('Dates changed after a deadline notice');
    });
});

describe('admin', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('lien.view', 'lien.update');
    });

    it('badges a filing whose dates changed after a deadline notice, on its page and on the board', function () {
        $project = gateProject($this->business, 'KS');
        [, $filing] = gateFiling($project, 'mechanics_lien', ['status' => FilingStatus::Paid, 'paid_at' => now()]);

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertDontSee('Dates changed after deadline notice');
        Livewire::test(LienBoard::class)
            ->assertSee($this->business->name)
            ->assertDontSee('Dates changed after deadline notice');

        $filing->events()->create([
            'business_id' => $filing->business_id,
            'event_type' => LienFilingEvent::DATES_CHANGED_AFTER_DEADLINE_BLOCK,
            'payload_json' => ['before' => [], 'after' => []],
        ]);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
            ->assertSee('Dates changed after deadline notice');
        Livewire::test(LienBoard::class)
            ->assertSee('Dates changed after deadline notice');
    });

    it('shows the customer\'s notice-of-intent answer and keeps it when staff edit the details', function () {
        $project = gateProject($this->business, 'KS');
        [, $filing] = gateFiling($project, 'mechanics_lien', [
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
            'document_details_json' => [
                'noi_served_on' => '2026-10-01',
                'noi_served_to' => 'owner',
                'noi_confirmed_at' => '2026-10-02T14:00:00+00:00',
            ],
        ]);

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('NOI served')
            ->assertSee('Oct 1, 2026 to the owner (customer confirmed Oct 2, 2026)');

        $changes = app(UpdateLienDocumentDetails::class)->execute($filing->fresh(), ['signer_name' => 'Kathryn Melendez']);

        expect($changes)->toBe(['signer_name' => ['from' => null, 'to' => 'Kathryn Melendez']])
            ->and($filing->fresh()->document_details_json)->toEqual([
                'signer_name' => 'Kathryn Melendez',
                'noi_served_on' => '2026-10-01',
                'noi_served_to' => 'owner',
                'noi_confirmed_at' => '2026-10-02T14:00:00+00:00',
            ]);
    });
});

describe('notice of intent served', function () {
    it('asks on a mechanics lien only where the state requires a notice of intent', function (string $status, bool $asks) {
        $state = gateNoiState($status);
        expect($state)->not->toBeNull();

        $project = gateProject($this->business, $state, GATE_RECENT_DATES);
        [$deadline] = gateFiling($project, 'mechanics_lien');

        $component = gateWorkStep($project, $deadline);

        if ($asks) {
            $component->assertSee('Has the notice of intent been served?');
        } else {
            $component->assertDontSee('Has the notice of intent been served?');
        }

        if ($status === 'required_some') {
            $component->assertSee("This doesn't apply to me", false)
                ->assertSee(NoticeOfIntentStatePage::entries()[$state]['who']);
        } else {
            $component->assertDontSee("This doesn't apply to me", false);
        }
    })->with([
        'required' => ['required', true],
        'required for some' => ['required_some', true],
        'not required' => ['not_required', false],
    ]);

    it('does not ask on other documents', function () {
        $state = gateNoiState('required');
        $project = gateProject($this->business, $state, GATE_RECENT_DATES);
        [$deadline] = gateFiling($project, 'prelim_notice');

        gateWorkStep($project, $deadline)->assertDontSee('Has the notice of intent been served?');
    });

    it('needs an answer, and for Yes a past date, who received it and the confirmation', function () {
        $state = gateNoiState('required');
        $project = gateProject($this->business, $state, GATE_RECENT_DATES);
        [$deadline] = gateFiling($project, 'mechanics_lien');

        $component = gateWorkStep($project, $deadline)
            ->call('nextStep')
            ->assertHasErrors(['noi_served' => 'required'])
            ->assertSet('step', 3);

        expect($component->errors()->first('noi_served'))->toBe('Tell us whether the notice of intent has been served.');

        // Only a state that requires it of some claimants offers "doesn't apply".
        $component->set('noi_served', 'not_applicable')
            ->call('nextStep')
            ->assertHasErrors(['noi_served' => 'in'])
            ->set('noi_served', 'yes')
            ->set('noi_served_on', '2026-10-21')
            ->call('nextStep')
            ->assertHasErrors([
                'noi_served_on' => 'before_or_equal',
                'noi_served_to' => 'required',
                'noi_confirmed' => 'accepted',
            ])
            ->assertSet('step', 3);
    });

    it('stores a Yes answer on the filing and restores it', function () {
        $state = gateNoiState('required');
        $stateName = config("states.{$state}");
        $project = gateProject($this->business, $state, GATE_RECENT_DATES);
        [$deadline, $filing] = gateFiling($project, 'mechanics_lien');

        gateWorkStep($project, $deadline)
            ->set('noi_served', 'yes')
            ->assertSee("I confirm a notice of intent was served as {$stateName} law requires, on this date.")
            ->set('noi_served_on', '2026-10-01')
            ->set('noi_served_to', 'both')
            ->set('noi_confirmed', true)
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4)
            ->assertDontSee('requires a notice of intent before the lien')
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        $details = $filing->fresh()->document_details_json;

        expect($details['noi_served_on'])->toBe('2026-10-01')
            ->and($details['noi_served_to'])->toBe('both')
            ->and(CarbonImmutable::parse($details['noi_confirmed_at'])->equalTo(now()))->toBeTrue()
            ->and($details)->not->toHaveKey('noi_not_applicable_reason');

        Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->assertSet('noi_served', 'yes')
            ->assertSet('noi_served_on', '2026-10-01')
            ->assertSet('noi_served_to', 'both')
            ->assertSet('noi_confirmed', true);
    });

    it('blocks checkout until the notice of intent is served', function () {
        $state = gateNoiState('required');
        $message = config("states.{$state}").' requires a notice of intent before the lien. Order the notice of intent first.';
        $project = gateProject($this->business, $state, GATE_RECENT_DATES);
        [$deadline, $filing] = gateFiling($project, 'mechanics_lien');
        $noiUrl = route('lien.filings.start', ['project' => $project, 'deadline' => $project->getDeadlineForType('noi')]);

        gateWorkStep($project, $deadline)
            ->set('noi_served', 'no')
            ->assertSee($message)
            ->assertSee($noiUrl, false)
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4)
            ->assertSee($message)
            ->assertSee($noiUrl, false)
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasErrors('noi_block')
            ->assertNoRedirect();

        expect($filing->fresh()->status)->toBe(FilingStatus::Draft)
            ->and($filing->fresh()->document_details_json)->toBeNull();
    });

    it('lets a claimant say the notice does not apply where it is required of some', function () {
        $state = gateNoiState('required_some');
        $project = gateProject($this->business, $state, GATE_RECENT_DATES);
        [$deadline, $filing] = gateFiling($project, 'mechanics_lien');

        gateWorkStep($project, $deadline)
            ->set('noi_served', 'not_applicable')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4)
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        expect($filing->fresh()->document_details_json)->toBe(['noi_not_applicable_reason' => 'customer_says_not_applicable']);
    });

    it('blocks a notice served too late for the state\'s lead time', function () {
        $state = gateNoiState('required', fn (string $state) => gateNoiLeadDays($state) > 3);
        $stateName = config("states.{$state}");
        $lead = gateNoiLeadDays($state);
        $project = gateProject($this->business, $state, GATE_RECENT_DATES);
        [$deadline, $filing] = gateFiling($project, 'mechanics_lien');

        $component = gateWorkStep($project, $deadline)
            ->set('noi_served', 'yes')
            ->set('noi_served_on', '2026-10-01')
            ->set('noi_served_to', 'owner')
            ->set('noi_confirmed', true)
            ->call('nextStep')
            ->assertSet('step', 4)
            ->assertDontSee('days between the notice and the lien');

        // Three days before the lien deadline, a notice served today is too late.
        $due = CarbonImmutable::parse($deadline->fresh()->due_date->format('Y-m-d'));
        $servedOn = $due->subDays(3);
        $this->travelTo(CarbonImmutable::parse($servedOn->toDateString().' 12:00:00', 'America/New_York')->utc());

        $component->set('noi_served_on', $servedOn->toDateString())
            ->assertSee("The notice of intent was served on {$servedOn->format('F j, Y')}. {$stateName} requires {$lead} days between the notice and the lien, which runs past the lien deadline of {$due->format('F j, Y')}.")
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasErrors('noi_block')
            ->assertNoRedirect();

        expect($filing->fresh()->status)->toBe(FilingStatus::Draft);
    });

    it('skips the lead-time check in Kentucky, whose noi rows count the wrong way (EREG-91)', function () {
        // The rows carry a day count; it is the wizard that ignores it.
        expect(gateNoiLeadDays('KY'))->toBeGreaterThan(3);

        $project = gateProject($this->business, 'KY', GATE_RECENT_DATES);
        [$deadline, $filing] = gateFiling($project, 'mechanics_lien');

        // "No" still blocks.
        $component = gateWorkStep($project, $deadline)
            ->set('noi_served', 'no')
            ->assertSee('Kentucky requires a notice of intent before the lien. Order the notice of intent first.')
            ->set('noi_served', 'yes')
            ->set('noi_served_on', '2026-10-01')
            ->set('noi_served_to', 'owner')
            ->set('noi_confirmed', true)
            ->call('nextStep')
            ->assertSet('step', 4);

        // Served three days before the lien deadline: not blocked here.
        $due = CarbonImmutable::parse($deadline->fresh()->due_date->format('Y-m-d'));
        $servedOn = $due->subDays(3);
        $this->travelTo(CarbonImmutable::parse($servedOn->toDateString().' 12:00:00', 'America/New_York')->utc());

        $component->set('noi_served_on', $servedOn->toDateString())
            ->assertDontSee('days between the notice and the lien')
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));
    });

    it('skips the lead-time check where the rules carry no day count', function () {
        $state = gateNoiState('required_some', fn (string $state) => gateNoiLeadDays($state) === 0)
            ?? gateNoiState('required', fn (string $state) => gateNoiLeadDays($state) === 0);

        if ($state === null) {
            $this->markTestSkipped('Every state that requires a notice of intent has a lead time.');
        }

        $project = gateProject($this->business, $state, GATE_RECENT_DATES);
        [$deadline, $filing] = gateFiling($project, 'mechanics_lien');

        $component = gateWorkStep($project, $deadline)
            ->set('noi_served', 'yes')
            ->set('noi_served_on', '2026-10-01')
            ->set('noi_served_to', 'owner')
            ->set('noi_confirmed', true)
            ->call('nextStep')
            ->assertSet('step', 4);

        $due = CarbonImmutable::parse($deadline->fresh()->due_date->format('Y-m-d'));
        $this->travelTo(CarbonImmutable::parse($due->toDateString().' 12:00:00', 'America/New_York')->utc());

        $component->set('noi_served_on', $due->toDateString())
            ->assertDontSee('days between the notice and the lien')
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));
    });
});

describe('date checks', function () {
    beforeEach(function () {
        // Arizona liens run from completion, so the Work step asks for it.
        $this->project = gateProject($this->business, 'AZ', GATE_RECENT_DATES + ['jobsite_county' => 'Maricopa']);
        [$this->deadline, $this->filing] = gateFiling($this->project, 'mechanics_lien');
    });

    it('warns, without blocking, when the last furnish date is after the completion date', function () {
        $warning = 'Your last furnish date is after the completion date. Check both dates.';

        $component = gateWorkStep($this->project, $this->deadline)
            ->assertSee('Completion Date *')
            ->set('last_furnish_date', '2026-10-10')
            ->set('completion_date', '2026-10-05')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4)
            ->assertSee($warning);

        $component->call('goToStep', 3)
            ->set('completion_date', '2026-10-10')
            ->call('nextStep')
            ->assertSet('step', 4)
            ->assertDontSee($warning)
            ->call('goToStep', 3)
            ->set('completion_date', '2026-10-05')
            ->call('nextStep')
            ->assertSee($warning)
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $this->filing));
    });

    it('rejects a date more than three years back', function () {
        $component = gateWorkStep($this->project, $this->deadline)
            ->set('first_furnish_date', '2023-10-19')
            ->call('nextStep')
            ->assertHasErrors('first_furnish_date')
            ->assertSet('step', 3);

        expect($component->errors()->first('first_furnish_date'))->toBe('That date is more than 3 years ago. Check the year.');

        $component->set('first_furnish_date', '2023-10-20')
            ->call('nextStep')
            ->assertHasNoErrors('first_furnish_date');
    });
});
