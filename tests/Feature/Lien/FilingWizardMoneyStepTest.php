<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

const MONEY_NOTE = 'Include only labor, materials and equipment you provided for this property.';
const MONEY_ATTESTATION = 'The amount claimed is for labor, materials or equipment only, with no fees, interest or penalties added';

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

if (! function_exists('moneyProject')) {
    /**
     * A general contractor's project with an owner and its deadlines, its
     * dates well inside every window. Kansas for liens, Arkansas for notices
     * of intent, Arizona for preliminary notices.
     */
    function moneyProject(Business $business, string $state = 'KS', array $overrides = []): LienProject
    {
        $project = LienProject::factory()->forBusiness($business)->create(array_merge([
            'jobsite_state' => $state,
            'jobsite_county' => match ($state) {
                'AR' => 'Pulaski',
                'AZ' => 'Maricopa',
                default => 'Sedgwick',
            },
            'claimant_type' => ClaimantType::Gc,
            'property_class' => 'residential',
            'first_furnish_date' => '2026-10-05',
            'last_furnish_date' => '2026-10-15',
            'completion_date' => '2026-10-15',
            // Liens and releases need a legal description or parcel number (EREG-136).
            'apn' => '087-123-04-0-11-02-005.00',
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
     * The project's deadline for a document type and a full-service draft on
     * it, with no amount yet.
     *
     * @return array{0: LienProjectDeadline, 1: LienFiling}
     */
    function moneyFiling(LienProject $project, string $slug = 'mechanics_lien', array $overrides = []): array
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
            'amount_claimed_cents' => null,
            'created_by_user_id' => auth()->id(),
        ], $overrides));

        return [$deadline, $filing];
    }

    /** Steps 1 and 2 are complete from the project; on to the Work step. */
    function moneyWorkStep(LienProject $project, LienProjectDeadline $deadline): Testable
    {
        return Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->call('nextStep')
            ->call('nextStep')
            ->assertSet('step', 3)
            ->set('has_written_contract', '0')
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.');
    }
}

describe('liens and notices of intent', function () {
    it('shows the amount breakdown open, with the note and the balance due', function (string $state, string $slug) {
        $project = moneyProject($this->business, $state);
        [$deadline] = moneyFiling($project, $slug);

        moneyWorkStep($project, $deadline)
            ->assertSee(MONEY_NOTE)
            ->assertSee('Do not add eRegister fees, interest, late fees or penalties. Most states do not allow them in a lien.')
            ->assertSee('Original contract amount *')
            ->assertSee('What you agreed to be paid before any changes.')
            ->assertSee('Change orders, net')
            ->assertSee('Extra work agreed, minus work removed. Can be negative.')
            ->assertSee('Value of work not done')
            ->assertSee('Only if the contract was not finished.')
            ->assertSee('Credits and deductions *')
            ->assertSee('Payments received *')
            ->assertSee('Balance due')
            ->assertSee('Amount you are claiming *')
            ->assertDontSee('Optional: Amount Breakdown (advanced)')
            ->assertDontSee('Why is the amount claimed different from the balance due?');
    })->with([
        'mechanics lien' => ['KS', 'mechanics_lien'],
        'notice of intent' => ['AR', 'noi'],
    ]);

    it('requires the contract amount, credits and payments, but not change orders or work not done', function (string $state, string $slug) {
        $project = moneyProject($this->business, $state);
        [$deadline] = moneyFiling($project, $slug);

        $component = moneyWorkStep($project, $deadline)
            ->call('nextStep')
            ->assertHasErrors([
                'base_contract_amount' => 'required',
                'credits_deductions' => 'required',
                'payments_received' => 'required',
                'amount_claimed' => 'required',
            ])
            ->assertHasNoErrors(['change_orders', 'uncompleted_work', 'claim_difference_reason'])
            ->assertSet('step', 3);

        expect($component->errors()->first('base_contract_amount'))->toBe('Enter the original contract amount.')
            ->and($component->errors()->first('payments_received'))->toBe('Enter the payments received. Enter 0 if there are none.')
            ->and($component->errors()->first('credits_deductions'))->toBe('Enter the credits and deductions. Enter 0 if there are none.');

        $component->set('base_contract_amount', '8000')
            ->set('credits_deductions', '0')
            ->set('payments_received', '0')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);
    })->with([
        'mechanics lien' => ['KS', 'mechanics_lien'],
        'notice of intent' => ['AR', 'noi'],
    ]);

    it('works out the balance due and fills the claim with it until the customer changes the claim', function () {
        $project = moneyProject($this->business);
        [$deadline] = moneyFiling($project);

        moneyWorkStep($project, $deadline)
            ->assertSet('amount_claimed', null)
            ->set('base_contract_amount', '10000')
            ->assertSet('amount_claimed', 10000.0)
            ->set('change_orders', '500')
            ->set('uncompleted_work', '200')
            ->set('credits_deductions', '100')
            ->set('payments_received', '3000')
            // 10,000 + 500 - 200 - 100 - 3,000
            ->assertSee('$7,200.00')
            ->assertSet('amount_claimed', 7200.0)
            ->set('change_orders', '-500')
            ->assertSee('$6,200.00')
            ->assertSet('amount_claimed', 6200.0)
            ->set('amount_claimed', 6000)
            ->set('payments_received', '3500')
            ->assertSee('$5,700.00')
            ->assertSet('amount_claimed', 6000.0);
    });

    it('starts from the amounts already on the project', function () {
        $project = moneyProject($this->business, 'KS', [
            'base_contract_amount_cents' => 1000000,
            'change_orders_cents' => 50000,
            'uncompleted_work_cents' => 0,
            'credits_deductions_cents' => 10000,
            'payments_received_cents' => 300000,
        ]);
        [$deadline] = moneyFiling($project);

        moneyWorkStep($project, $deadline)
            ->assertSet('base_contract_amount', '10000.00')
            ->assertSet('change_orders', '500.00')
            ->assertSet('uncompleted_work', '0.00')
            ->assertSet('credits_deductions', '100.00')
            ->assertSet('payments_received', '3000.00')
            ->assertSet('amount_claimed', 7400.0)
            ->assertSee('$7,400.00');
    });

    it('asks why only when the claim differs from the balance due by more than a cent', function () {
        $project = moneyProject($this->business);
        [$deadline] = moneyFiling($project);

        $component = moneyWorkStep($project, $deadline)
            ->fill(['base_contract_amount' => '7200', 'credits_deductions' => '0', 'payments_received' => '0'])
            ->set('amount_claimed', 7200.01)
            ->assertDontSee('Why is the amount claimed different from the balance due?')
            ->assertDontSee('We can only file for what is actually owed for the work.')
            ->set('amount_claimed', 7500)
            ->assertSee('Why is the amount claimed different from the balance due?')
            ->assertSee('We can only file for what is actually owed for the work.')
            ->call('nextStep')
            ->assertHasErrors(['claim_difference_reason' => 'required'])
            ->assertSet('step', 3);

        expect($component->errors()->first('claim_difference_reason'))
            ->toBe('Tell us why the amount claimed is different from the balance due.');

        $component->set('claim_difference_reason', str_repeat('a', 1001))
            ->call('nextStep')
            ->assertHasErrors(['claim_difference_reason' => 'max'])
            ->set('claim_difference_reason', ' The owner agreed in writing to pay for the extra trip. ')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        expect($project->fresh()->claim_difference_reason)->toBe('The owner agreed in writing to pay for the extra trip.');

        // Back to the balance: no reason needed, and the old one is cleared.
        $component->call('goToStep', 3)
            ->set('amount_claimed', 7200)
            ->assertDontSee('Why is the amount claimed different from the balance due?')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        expect($project->fresh()->claim_difference_reason)->toBeNull();
    });

    it('saves the amounts in cents on the project and the claim on the filing', function () {
        $project = moneyProject($this->business);
        [$deadline, $filing] = moneyFiling($project);

        moneyWorkStep($project, $deadline)
            ->set('base_contract_amount', '11019.99')
            ->set('change_orders', '')
            ->set('uncompleted_work', '')
            ->set('credits_deductions', '100.55')
            ->set('payments_received', '900.15')
            ->assertSet('amount_claimed', 10019.29)
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        $project->refresh();

        expect($project->base_contract_amount_cents)->toBe(1101999)
            ->and($project->change_orders_cents)->toBe(0)
            ->and($project->uncompleted_work_cents)->toBe(0)
            ->and($project->credits_deductions_cents)->toBe(10055)
            ->and($project->payments_received_cents)->toBe(90015)
            ->and($project->balanceDueCents())->toBe(1001929)
            ->and($project->claim_difference_reason)->toBeNull()
            ->and($filing->fresh()->amount_claimed_cents)->toBe(1001929);
    });

    it('requires the no-fees attestation at review and records when it was given', function () {
        $project = moneyProject($this->business);
        [$deadline, $filing] = moneyFiling($project);

        $component = moneyWorkStep($project, $deadline)
            ->fill(['base_contract_amount' => '5000', 'credits_deductions' => '0', 'payments_received' => '0'])
            ->call('nextStep')
            ->assertSet('step', 4)
            ->assertSee(MONEY_ATTESTATION.'.')
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasErrors(['claimAttested' => 'accepted'])
            ->assertNoRedirect();

        expect($component->errors()->first('claimAttested'))->toBe('Confirm the amount claimed has no fees, interest or penalties added.')
            ->and($filing->fresh()->status)->toBe(FilingStatus::Draft)
            ->and($filing->fresh()->claim_attested_at)->toBeNull();

        $component->set('claimAttested', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        $filing->refresh();

        expect($filing->status)->toBe(FilingStatus::AwaitingPayment)
            ->and($filing->claim_attested_at->equalTo(now()))->toBeTrue()
            ->and($filing->amount_claimed_cents)->toBe(500000)
            ->and($filing->payload_json['amount_breakdown'])->toMatchArray([
                'base_contract_amount' => '5000',
                'credits_deductions' => '0',
                'payments_received' => '0',
                'claim_difference_reason' => null,
            ]);
    });

    it('allows what a written contract allows, in the note and the attestation', function () {
        $project = moneyProject($this->business);
        [$deadline] = moneyFiling($project);

        moneyWorkStep($project, $deadline)
            ->assertDontSee('unless your written contract allows them')
            ->set('has_written_contract', '1')
            ->assertSee('Do not add eRegister fees, interest, late fees or penalties unless your written contract allows them. Most states do not allow them in a lien.')
            ->fill(['base_contract_amount' => '5000', 'credits_deductions' => '0', 'payments_received' => '0'])
            ->call('nextStep')
            ->assertSet('step', 4)
            ->assertSee(MONEY_ATTESTATION.' unless my written contract allows them.');
    });
});

describe('preliminary notices', function () {
    it('asks for the estimated total price only, and no attestation', function () {
        $project = moneyProject($this->business, 'AZ', [
            'payments_received_cents' => 12345,
        ]);
        [$deadline, $filing] = moneyFiling($project, 'prelim_notice', [
            'document_details_json' => ['signer_name' => 'Kathryn Melendez'],
        ]);

        $component = moneyWorkStep($project, $deadline)
            ->assertSee(MONEY_NOTE)
            ->assertSee('Estimated total price *')
            ->assertSee('What you expect to be paid in total for this job.')
            ->assertDontSee('Original contract amount')
            ->assertDontSee('Credits and deductions')
            ->assertDontSee('Payments received')
            ->assertDontSee('Amount you are claiming')
            ->assertDontSee('Optional: Amount Breakdown (advanced)')
            ->call('nextStep')
            ->assertHasErrors(['amount_claimed' => 'required'])
            ->assertHasNoErrors(['base_contract_amount', 'credits_deductions', 'payments_received']);

        expect($component->errors()->first('amount_claimed'))->toBe('Enter the estimated total price.');

        $component->set('amount_claimed', 25000)
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4)
            ->assertDontSee(MONEY_ATTESTATION)
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.filings.checkout', $filing));

        $filing->refresh();
        $project->refresh();

        expect($filing->amount_claimed_cents)->toBe(2500000)
            ->and($filing->document_details_json)->toEqual([
                'signer_name' => 'Kathryn Melendez',
                'estimated_price_cents' => 2500000,
            ])
            ->and($filing->claim_attested_at)->toBeNull()
            ->and($project->payments_received_cents)->toBe(12345)
            ->and($project->base_contract_amount_cents)->toBeNull();
    });

    it('starts the estimate from the contract and its change orders', function () {
        $project = moneyProject($this->business, 'AZ', [
            'base_contract_amount_cents' => 2000000,
            'change_orders_cents' => 150000,
            'payments_received_cents' => 500000,
        ]);
        [$deadline] = moneyFiling($project, 'prelim_notice');

        moneyWorkStep($project, $deadline)->assertSet('amount_claimed', 21500.0);
    });
});

describe('demand letters and releases', function () {
    it('keeps the amount and the optional breakdown', function (string $slug) {
        $project = moneyProject($this->business);
        [$deadline] = moneyFiling($project, $slug);

        Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->call('nextStep')
            ->call('nextStep')
            ->assertSet('step', 3)
            ->assertSee('Amount Claimed *')
            ->assertSee('Optional: Amount Breakdown (advanced)')
            ->assertDontSee(MONEY_NOTE)
            ->assertDontSee('Amount you are claiming')
            ->assertDontSee('Estimated total price')
            ->set('step', 4)
            ->assertDontSee(MONEY_ATTESTATION);
    })->with(['demand_letter', 'lien_release']);
});

describe('admin', function () {
    it('shows the amounts, the balance, the claim, the reason and the attestation', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $project = moneyProject($this->business, 'KS', [
            'base_contract_amount_cents' => 1000000,
            'change_orders_cents' => -50000,
            'uncompleted_work_cents' => 20000,
            'credits_deductions_cents' => 10000,
            'payments_received_cents' => 300000,
            'claim_difference_reason' => 'Two invoices are still being disputed.',
        ]);
        [, $filing] = moneyFiling($project, 'mechanics_lien', [
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
            'amount_claimed_cents' => 600000,
            'claim_attested_at' => now(),
        ]);

        $this->actingAs($admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Filing Summary')
            ->assertSee('$10,000.00')
            ->assertSee('-$500.00')
            ->assertSee('$200.00')
            ->assertSee('$100.00')
            ->assertSee('$3,000.00')
            // 10,000 - 500 - 200 - 100 - 3,000
            ->assertSee('$6,200.00')
            ->assertSee('$6,000.00')
            ->assertSee('Why the Claim Differs From the Balance Due')
            ->assertSee('Two invoices are still being disputed.')
            ->assertSee('No Fees in Claim (Attested)')
            ->assertSee('Oct 20, 2026 12:00 PM');
    });

    it('says when a lien has no attestation yet', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $project = moneyProject($this->business);
        [, $filing] = moneyFiling($project);

        $this->actingAs($admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('No Fees in Claim (Attested)')
            ->assertSee('Not yet')
            ->assertDontSee('Why the Claim Differs From the Balance Due');
    });
});
