<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingShow;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingEvent;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Services\LienPaymentService;
use App\Enums\PaymentStatus;
use App\Mail\PropertyDescriptionEntered;
use App\Mail\PropertyDescriptionSteps;
use App\Models\EmailSequence;
use App\Models\Payment;
use App\Models\SentEmail;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/*
| EREG-136: every mechanics lien and lien release names the property by its
| legal description or parcel number. "I need help finding it" lets the
| customer pay now; payment moves the filing to Awaiting Client, emails the
| steps, and the filing page asks the customer to enter it. Staff never
| look it up or type it in.
*/

beforeEach(function () {
    // Tuesday, October 20, 2026, noon Eastern.
    $this->travelTo(CarbonImmutable::parse('2026-10-20 12:00:00', 'America/New_York')->utc());
    Storage::fake('s3');
    Storage::fake('local');
    Mail::fake();
    LienDocumentRegistry::flush();

    $this->user = User::factory()->create(['first_name' => 'Dana', 'last_name' => 'Builder', 'email' => 'dana@example.test']);
    $this->business = Business::factory()->create(['lien_onboarding_completed_at' => now(), 'name' => 'Dana Drywall LLC']);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

afterEach(fn () => LienDocumentRegistry::flush());

if (! function_exists('propertyIdProject')) {
    /**
     * A general contractor's Kansas project with an owner, no legal
     * description or parcel number, and its deadlines: the lien is due
     * December 14, 2026, well clear of any rush fee.
     */
    function propertyIdProject(Business $business, array $overrides = []): LienProject
    {
        $project = LienProject::factory()->forBusiness($business)->create(array_merge([
            'name' => 'Maple Street Remodel',
            'jobsite_address1' => '100 Maple St',
            'jobsite_city' => 'Wichita',
            'jobsite_state' => 'KS',
            'jobsite_county' => 'Sedgwick',
            'jobsite_zip' => '67202',
            'claimant_type' => ClaimantType::Gc,
            'property_class' => 'residential',
            'first_furnish_date' => '2026-07-01',
            'last_furnish_date' => '2026-08-14',
            'completion_date' => '2026-08-14',
            'legal_description' => null,
            'apn' => null,
        ], $overrides));

        app(DeadlineCalculator::class)->calculateForProject($project);

        LienParty::create([
            'business_id' => $business->id,
            'project_id' => $project->id,
            'role' => 'owner',
            'name' => 'Test Owner',
            'address1' => '123 Main St',
            'city' => 'Wichita',
            'state' => $project->jobsite_state,
            'zip' => '67202',
        ]);

        return $project;
    }

    /**
     * The project's deadline for a document and a draft on it.
     *
     * @return array{0: LienProjectDeadline, 1: LienFiling}
     */
    function propertyIdDraft(LienProject $project, string $slug = 'mechanics_lien', array $overrides = []): array
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

    /**
     * Through the wizard to the review step: the Property step as given,
     * the Parties step from the project, the Work step filled in. A lien
     * release also gets the lien being released.
     *
     * @param  array<string, mixed>  $property
     */
    function propertyIdReview(LienProject $project, LienProjectDeadline $deadline, array $property = [], string $serviceLevel = 'full_service'): Testable
    {
        $component = Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline]);

        foreach ($property as $field => $value) {
            $component->set($field, $value);
        }

        $component->call('nextStep')
            ->call('nextStep')
            ->assertSet('step', 3)
            ->set('has_written_contract', '1')
            ->fill(['base_contract_amount' => '5000', 'credits_deductions' => '0', 'payments_received' => '0'])
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for drywall.')
            ->set('stateAnswers', wizardStateAnswers());

        if ($deadline->documentType->slug === 'lien_release') {
            $component->set('original_lien_recorded_at', '2026-09-15')
                ->set('original_lien_reference', '2026-004512');
        }

        return $component->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4)
            ->set('service_level', $serviceLevel)
            ->set('disclaimerAccepted', true)
            ->set('claimAttested', true);
    }

    /** A paid-for order waiting for its Stripe confirmation. */
    function propertyIdPayment(LienFiling $filing): Payment
    {
        return Payment::factory()->forPurchasable($filing)->create([
            'amount_cents' => 29900,
            'status' => PaymentStatus::Initiated,
        ]);
    }

    function propertyIdMarkPaid(Payment $payment): void
    {
        $intent = new \Stripe\PaymentIntent('pi_test_ereg136');
        $intent->latest_charge = 'ch_test_ereg136';
        $intent->amount_received = $payment->amount_cents;
        $intent->currency = 'usd';

        app(LienPaymentService::class)->markSucceeded($payment, $intent);
    }

    /** A paid lien whose customer chose "I need help finding it", now Awaiting Client. */
    function propertyIdAwaiting(LienProject $project, string $serviceLevel = 'full_service'): LienFiling
    {
        [, $filing] = propertyIdDraft($project, 'mechanics_lien', [
            'status' => FilingStatus::AwaitingPayment,
            'service_level' => $serviceLevel,
            'property_help_requested_at' => now()->subHour(),
        ]);

        propertyIdMarkPaid(propertyIdPayment($filing));

        return $filing->fresh();
    }
}

const PROPERTY_ID_ERROR = 'Add the legal description or the parcel number on the Property step. If you can\'t find either, choose "I need help finding it" there.';

describe('the review step', function () {
    it('requires a legal description or parcel number for every lien and release, at every service level', function (string $slug, string $serviceLevel) {
        $project = propertyIdProject($this->business);
        [$deadline, $filing] = propertyIdDraft($project, $slug);

        $component = propertyIdReview($project, $deadline, [], $serviceLevel)
            ->call('proceedToCheckout')
            ->assertHasErrors('property_identification')
            ->assertNoRedirect()
            ->assertSee('Go to the Property step');

        expect($component->errors()->first('property_identification'))->toBe(PROPERTY_ID_ERROR)
            ->and($filing->fresh()->status)->toBe(FilingStatus::Draft)
            ->and($filing->fresh()->property_help_requested_at)->toBeNull();
    })->with([
        'lien, full service' => ['mechanics_lien', 'full_service'],
        'lien, self-serve' => ['mechanics_lien', 'self_serve'],
        'release, full service' => ['lien_release', 'full_service'],
        'release, self-serve' => ['lien_release', 'self_serve'],
    ]);

    it('takes either one', function (array $property) {
        $project = propertyIdProject($this->business);
        [$deadline, $filing] = propertyIdDraft($project);

        propertyIdReview($project, $deadline, $property)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        expect($filing->fresh()->property_help_requested_at)->toBeNull();
    })->with([
        'legal description' => [['has_legal_description' => 'yes', 'legal_description' => 'Lot 12, Block 3, Sunset Hills Subdivision']],
        'parcel number' => [['has_apn' => 'yes', 'apn' => '087-123-04-0-11-02-005.00']],
    ]);

    it('does not count an answer of yes left blank', function () {
        $project = propertyIdProject($this->business);
        [$deadline] = propertyIdDraft($project);

        propertyIdReview($project, $deadline, ['has_legal_description' => 'yes', 'legal_description' => '   '])
            ->call('proceedToCheckout')
            ->assertHasErrors('property_identification');
    });

    it('leaves it optional on preliminary notices and notices of intent', function (string $slug) {
        $project = propertyIdProject($this->business);
        [$deadline] = propertyIdDraft($project, $slug);

        Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->assertDontSee('I need help finding it')
            ->assertSee('Optional. It helps identify the property.')
            ->set('step', 4)
            ->call('proceedToCheckout')
            ->assertHasNoErrors('property_identification');
    })->with(['prelim_notice', 'noi']);

    it('lets a customer who needs help finding it through to payment', function (string $slug, string $serviceLevel) {
        $project = propertyIdProject($this->business);
        [$deadline, $filing] = propertyIdDraft($project, $slug);

        propertyIdReview($project, $deadline, ['has_legal_description' => 'no', 'has_apn' => 'no', 'property_help' => true], $serviceLevel)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        $filing->refresh();

        expect($filing->status)->toBe(FilingStatus::AwaitingPayment)
            ->and($filing->property_help_requested_at?->toIso8601String())->toBe(now()->toIso8601String())
            ->and($project->fresh()->legal_description)->toBeNull()
            ->and($project->fresh()->apn)->toBeNull();
    })->with([
        'lien, full service' => ['mechanics_lien', 'full_service'],
        'lien, self-serve' => ['mechanics_lien', 'self_serve'],
        'release, self-serve' => ['lien_release', 'self_serve'],
    ]);

    it('still blocks a lien past its deadline when the customer needs help', function () {
        // Kansas: 4 months after last furnishing, so September 1, 2026.
        $project = propertyIdProject($this->business, [
            'first_furnish_date' => '2026-04-01',
            'last_furnish_date' => '2026-05-01',
            'completion_date' => '2026-05-01',
        ]);
        [$deadline, $filing] = propertyIdDraft($project);

        propertyIdReview($project, $deadline, ['property_help' => true])
            ->assertSee('has passed')
            ->assertDontSee('Proceed to Payment')
            ->call('proceedToCheckout')
            ->assertHasErrors('deadline_passed')
            ->assertHasNoErrors('property_identification')
            ->assertNoRedirect();

        expect($filing->fresh()->status)->toBe(FilingStatus::Draft);
    });
});

describe('the Property step', function () {
    it('explains where to find each one and offers help on a lien', function () {
        $project = propertyIdProject($this->business);
        [$deadline] = propertyIdDraft($project);

        Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->assertSee('A mechanics lien must name the property by its legal description or its parcel number. Add at least one.')
            ->assertSee('The legal description is on the deed.')
            ->assertSee('The parcel number is on the property tax bill and the county assessor')
            ->assertSee('I need help finding it')
            // No assessor link in the Kansas or Sedgwick data, so none is shown.
            ->assertDontSee('Search the county assessor')
            // Kansas just says "Parcel ID".
            ->assertDontSee('In Kansas it is called');
    });

    it('uses the state word for the parcel number', function () {
        $project = propertyIdProject($this->business, ['jobsite_state' => 'GA', 'jobsite_county' => 'Fulton']);
        [$deadline] = propertyIdDraft($project);

        Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->assertSee('In Georgia it is called the Tax Parcel ID.')
            ->assertSee('Do you have the parcel number (Tax Parcel ID)?')
            ->set('has_apn', 'yes')
            ->assertSee('Parcel number (Tax Parcel ID)');
    });

    it('stores the help choice, and drops it once the customer has one', function () {
        $project = propertyIdProject($this->business);
        [$deadline, $filing] = propertyIdDraft($project);

        $component = Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->set('property_help', true)
            ->call('nextStep')
            ->assertSet('step', 2);

        $chosenAt = $filing->fresh()->property_help_requested_at;
        expect($chosenAt)->not->toBeNull();

        // Coming back later keeps the choice and its time.
        $this->travel(2)->hours();

        Livewire::test(FilingWizard::class, ['project' => $project->fresh(), 'deadline' => $deadline])
            ->assertSet('property_help', true)
            ->call('nextStep');

        expect($filing->fresh()->property_help_requested_at->equalTo($chosenAt))->toBeTrue();

        $component->call('goToStep', 1)
            ->set('has_apn', 'yes')
            ->assertSet('property_help', false)
            ->assertDontSee('I need help finding it')
            ->set('apn', '087-123-04-0-11-02-005.00')
            ->call('nextStep');

        expect($filing->fresh()->property_help_requested_at)->toBeNull()
            ->and($project->fresh()->apn)->toBe('087-123-04-0-11-02-005.00');
    });
});

describe('after payment', function () {
    it('moves the filing to Awaiting Client, starts the reminders and emails the steps', function (string $serviceLevel) {
        $project = propertyIdProject($this->business);
        $filing = propertyIdAwaiting($project, $serviceLevel);

        expect($filing->status)->toBe(FilingStatus::AwaitingClient)
            ->and($filing->paid_at)->not->toBeNull();

        $note = $filing->events()->where('event_type', 'status_changed')->get()
            ->first(fn (LienFilingEvent $event) => ($event->payload_json['to'] ?? null) === FilingStatus::AwaitingClient->value);

        expect($note?->payload_json['from'])->toBe(FilingStatus::Paid->value)
            ->and($note?->payload_json['meta']['note'])->toBe('Legal description needed');

        $sequence = EmailSequence::where('sequence_type', 'filing_action_reminder')
            ->where('sequenceable_id', $filing->id)
            ->first();

        expect($sequence)->not->toBeNull()
            ->and($sequence->trigger_status)->toBe(FilingStatus::AwaitingClient->value)
            ->and($sequence->suppressed_at)->toBeNull();

        Mail::assertQueued(PropertyDescriptionSteps::class, fn (PropertyDescriptionSteps $mail) => $mail->hasTo('dana@example.test'));
        Mail::assertQueued(PropertyDescriptionSteps::class, 1);

        expect(SentEmail::where('email_type', 'property_description_steps')->where('emailable_id', $filing->id)->count())->toBe(1);
    })->with(['full_service', 'self_serve']);

    it('keeps the full-service fulfillment task', function () {
        $filing = propertyIdAwaiting(propertyIdProject($this->business));

        expect($filing->fulfillmentTask)->not->toBeNull();
    });

    it('writes the steps for the customer\'s county and links the filing page', function () {
        $project = propertyIdProject($this->business, ['jobsite_state' => 'GA', 'jobsite_county' => 'Fulton']);
        $filing = propertyIdAwaiting($project);

        $mail = new PropertyDescriptionSteps($filing);

        $mail->assertHasSubject('Action required: the legal description for your mechanics lien — eRegister')
            ->assertHasReplyTo(config('mail.support_address', 'contact@eregister.com'))
            ->assertSeeInHtml('Hi Dana,')
            ->assertSeeInHtml('To record your mechanics lien we need the legal description or parcel number of the property.')
            ->assertSeeInHtml('100 Maple St')
            ->assertSeeInHtml('Look up the property on the county assessor site and copy the parcel number (Tax Parcel ID).')
            ->assertSeeInHtml('The county recorder', false)
            ->assertSeeInHtml('Enter it on your filing page.')
            ->assertSeeInHtml('Lot 12, Block 3, Sunset Hills Subdivision')
            ->assertSeeInHtml('Beginning at the northeast corner of Section 14')
            ->assertSeeInHtml('Enter it now')
            ->assertSeeInHtml(route('lien.filings.show', $filing))
            ->assertDontSeeInHtml('look it up for you')
            ->assertDontSeeInHtml('we can look');

        // The county assessor link, when the data has one.
        $linked = new PropertyDescriptionSteps($filing);
        $linked->assessorUrl = 'https://county.example.gov/parcel-search';
        $linked->assertSeeInHtml('href="https://county.example.gov/parcel-search"', false);
    });

    it('leaves a filing alone when the customer did not ask for help', function () {
        $project = propertyIdProject($this->business, ['apn' => '087-123-04-0-11-02-005.00']);
        [, $filing] = propertyIdDraft($project, 'mechanics_lien', ['status' => FilingStatus::AwaitingPayment]);

        propertyIdMarkPaid(propertyIdPayment($filing));

        expect($filing->fresh()->status)->toBe(FilingStatus::InFulfillment);
        Mail::assertNotQueued(PropertyDescriptionSteps::class);
    });

    it('leaves it alone when the project has the parcel number by the time they pay', function () {
        $project = propertyIdProject($this->business);
        [, $filing] = propertyIdDraft($project, 'mechanics_lien', [
            'status' => FilingStatus::AwaitingPayment,
            'service_level' => 'self_serve',
            'property_help_requested_at' => now()->subDay(),
        ]);
        $project->update(['apn' => '087-123-04-0-11-02-005.00']);

        propertyIdMarkPaid(propertyIdPayment($filing));

        expect($filing->fresh()->status)->toBe(FilingStatus::Paid);
        Mail::assertNotQueued(PropertyDescriptionSteps::class);
    });
});

describe('the filing page', function () {
    it('asks the customer to enter it', function () {
        $filing = propertyIdAwaiting(propertyIdProject($this->business));

        Livewire::test(FilingShow::class, ['filing' => $filing])
            ->assertSee('Enter the legal description or parcel number below.')
            ->assertDontSee('Please check the request we emailed you and reply to it.')
            ->assertSee('Enter the legal description or parcel number')
            ->assertSee('To record your mechanics lien we need the legal description or parcel number of the property.')
            ->assertSee('100 Maple St')
            ->assertSee('Deed or tax bill page (optional)')
            ->assertSee('This is the property where I furnished labor or materials.');
    });

    it('does not ask before payment, or once the project has one', function () {
        $project = propertyIdProject($this->business);
        [, $unpaid] = propertyIdDraft($project, 'mechanics_lien', [
            'status' => FilingStatus::AwaitingPayment,
            'property_help_requested_at' => now(),
        ]);

        Livewire::test(FilingShow::class, ['filing' => $unpaid])
            ->assertDontSee('Enter the legal description or parcel number');

        $filing = propertyIdAwaiting(propertyIdProject($this->business));
        $filing->project->update(['legal_description' => 'Lot 12, Block 3, Sunset Hills Subdivision']);

        Livewire::test(FilingShow::class, ['filing' => $filing->fresh()])
            ->assertDontSee('Enter the legal description or parcel number')
            ->assertSee('Please check the request we emailed you and reply to it.');
    });

    it('requires the confirmation and one of the two', function () {
        $filing = propertyIdAwaiting(propertyIdProject($this->business));

        $component = Livewire::test(FilingShow::class, ['filing' => $filing])
            ->call('saveProperty')
            ->assertHasErrors(['propertyLegalDescription' => 'required_without', 'propertyConfirmed' => 'accepted']);

        expect($component->errors()->first('propertyLegalDescription'))->toBe('Enter the legal description or the parcel number.')
            ->and($component->errors()->first('propertyConfirmed'))->toBe('Tick the box to confirm this is the property where you furnished labor or materials.');

        $component->set('propertyApn', '087-123-04-0-11-02-005.00')
            ->call('saveProperty')
            ->assertHasErrors(['propertyConfirmed' => 'accepted'])
            ->assertHasNoErrors('propertyLegalDescription');

        expect($filing->fresh()->status)->toBe(FilingStatus::AwaitingClient)
            ->and($filing->project->fresh()->apn)->toBeNull()
            ->and($filing->events()->where('event_type', LienFilingEvent::PROPERTY_ENTERED)->exists())->toBeFalse();
        Mail::assertNotQueued(PropertyDescriptionEntered::class);
    });

    it('saves it, logs who entered it, moves to Needs Review and tells lien ops', function () {
        $filing = propertyIdAwaiting(propertyIdProject($this->business));
        $deed = UploadedFile::fake()->createWithContent('deed-page-1.pdf', "%PDF-1.4\n% test deed\n");

        Livewire::test(FilingShow::class, ['filing' => $filing])
            ->set('propertyLegalDescription', "  Lot 12, Block 3, Sunset Hills Subdivision,\nas recorded in Plat Book 44, Page 7  ")
            ->set('propertyApn', '087-123-04-0-11-02-005.00')
            ->set('propertyFile', $deed)
            ->set('propertyConfirmed', true)
            ->call('saveProperty')
            ->assertHasNoErrors()
            ->assertSee('Thanks. We have the property details and will keep going.')
            ->assertDontSee('Enter the legal description or parcel number below.');

        $filing->refresh();
        $project = $filing->project->fresh();
        $entry = $filing->latestPropertyEntry();
        $media = $filing->getFirstMedia('customer_uploads');

        expect($project->legal_description)->toBe("Lot 12, Block 3, Sunset Hills Subdivision,\nas recorded in Plat Book 44, Page 7")
            ->and($project->apn)->toBe('087-123-04-0-11-02-005.00')
            ->and($filing->status)->toBe(FilingStatus::NeedsReview)
            ->and($filing->property_help_requested_at)->not->toBeNull()
            ->and($media?->file_name)->toBe('deed-page-1.pdf')
            ->and($entry)->not->toBeNull()
            ->and($entry->created_by)->toBe($this->user->id)
            ->and($entry->payload_json['entered_by'])->toBe($this->user->id)
            ->and($entry->payload_json['entered_at'])->toBe(now()->toIso8601String())
            ->and($entry->payload_json['media_id'])->toBe($media->id)
            ->and($entry->payload_json['apn'])->toBe('087-123-04-0-11-02-005.00')
            ->and($entry->payload_json['confirmation'])->toBe('This is the property where I furnished labor or materials.')
            ->and($entry->description())->toBe('Property entered by customer');

        $review = $filing->events()->where('event_type', 'status_changed')->latest('id')->first();
        expect($review->payload_json)->toMatchArray([
            'from' => FilingStatus::AwaitingClient->value,
            'to' => FilingStatus::NeedsReview->value,
        ]);

        // The reminders stop.
        expect(EmailSequence::where('sequence_type', 'filing_action_reminder')->where('sequenceable_id', $filing->id)->first()->suppressed_at)->not->toBeNull();

        Mail::assertQueued(PropertyDescriptionEntered::class, function (PropertyDescriptionEntered $mail) use ($filing) {
            return $mail->hasTo('liens@eregister.com')
                && $mail->filingPublicId === $filing->public_id
                && $mail->apn === '087-123-04-0-11-02-005.00';
        });

        $ops = new PropertyDescriptionEntered($filing, $entry);
        $ops->assertHasSubject('Customer entered the property: Dana Drywall LLC — Mechanics Lien')
            ->assertSeeInHtml('Dana Builder')
            ->assertSeeInHtml('Lot 12, Block 3, Sunset Hills Subdivision,<br>as recorded in Plat Book 44, Page 7', false)
            ->assertSeeInHtml('deed-page-1.pdf')
            ->assertSeeInHtml('The filing is now in Needs Review.')
            ->assertSeeInHtml(route('admin.liens.show', $filing));
    });

    it('sends no ops email when the inbox is blank', function () {
        config(['lien.notifications.ops_email' => '']);
        $filing = propertyIdAwaiting(propertyIdProject($this->business));

        Livewire::test(FilingShow::class, ['filing' => $filing])
            ->set('propertyLegalDescription', 'Lot 12, Block 3, Sunset Hills Subdivision')
            ->set('propertyConfirmed', true)
            ->call('saveProperty')
            ->assertHasNoErrors();

        expect($filing->fresh()->status)->toBe(FilingStatus::NeedsReview);
        Mail::assertNotQueued(PropertyDescriptionEntered::class);
    });

    it('lets only the customer enter it, never staff', function () {
        $filing = propertyIdAwaiting(propertyIdProject($this->business));

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view', 'lien.update', 'lien.change_status');
        $this->actingAs($admin);
        session()->forget('current_business_id');

        Livewire::test(FilingShow::class, ['filing' => $filing])
            ->assertDontSee('Enter the legal description or parcel number')
            ->set('propertyLegalDescription', 'Lot 12, Block 3, Sunset Hills Subdivision')
            ->set('propertyConfirmed', true)
            ->call('saveProperty')
            ->assertForbidden();

        expect($filing->project->fresh()->legal_description)->toBeNull()
            ->and($filing->fresh()->status)->toBe(FilingStatus::AwaitingClient);
    });
});

describe('the admin page', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('lien.view', 'lien.update', 'lien.change_status');
    });

    it('shows the property is needed, then who entered it, with the upload', function () {
        $filing = propertyIdAwaiting(propertyIdProject($this->business));

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Legal description needed')
            ->assertSee('I need help finding it');

        $this->actingAs($this->user);
        session(['current_business_id' => $this->business->id]);

        Livewire::test(FilingShow::class, ['filing' => $filing])
            ->set('propertyLegalDescription', 'Lot 12, Block 3, Sunset Hills Subdivision')
            ->set('propertyFile', UploadedFile::fake()->createWithContent('tax-bill.pdf', "%PDF-1.4\n% test bill\n"))
            ->set('propertyConfirmed', true)
            ->call('saveProperty')
            ->assertHasNoErrors();

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
            ->assertSee('Lot 12, Block 3, Sunset Hills Subdivision')
            ->assertSee('entered by customer on Oct 20, 2026 (Dana Builder)')
            ->assertSee('tax-bill.pdf')
            ->assertSee('Property entered by customer')
            // The status note is in the activity log.
            ->assertSee('Legal description needed');
    });
});
