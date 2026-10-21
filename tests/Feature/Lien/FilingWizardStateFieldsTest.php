<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Actions\UpdateLienDocumentDetails;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\NocStatus;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Models\LienStateRule;
use App\Domains\Lien\Support\WizardFields;
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

if (! function_exists('stateFieldsProject')) {
    /**
     * A project with an owner (and, for a claimant below the general
     * contractor, the general contractor), its dates well inside every
     * window, and its deadlines.
     */
    function stateFieldsProject(Business $business, string $state, ClaimantType $type = ClaimantType::Gc, array $overrides = []): LienProject
    {
        $project = LienProject::factory()->forBusiness($business)->create(array_merge([
            'jobsite_address1' => '100 Jobsite Rd',
            'jobsite_address2' => null,
            'jobsite_city' => 'Springfield',
            'jobsite_state' => $state,
            'jobsite_county' => 'Sedgwick',
            'jobsite_zip' => '67202',
            'claimant_type' => $type,
            'hired_by' => null,
            'property_class' => 'commercial',
            'first_furnish_date' => '2026-09-01',
            'last_furnish_date' => '2026-10-15',
            'completion_date' => '2026-10-15',
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

        if ($type->requiresGcParty()) {
            LienParty::create([
                'business_id' => $business->id,
                'project_id' => $project->id,
                'role' => 'gc',
                'name' => 'Acme Builders',
                'address1' => '500 Builder Blvd',
                'city' => 'Capital City',
                'state' => $state,
                'zip' => '54321',
            ]);
        }

        return $project;
    }

    /**
     * The project's deadline for a document type and a full-service draft on it.
     *
     * @return array{0: LienProjectDeadline, 1: LienFiling}
     */
    function stateFieldsFiling(LienProject $project, string $slug = 'mechanics_lien', array $overrides = []): array
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

    /** Steps 1 and 2 are complete from the project; fill the rest of the Work step. */
    function stateFieldsWorkStep(LienProject $project, LienProjectDeadline $deadline): Testable
    {
        $component = Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->call('nextStep')
            ->call('nextStep')
            ->assertSet('step', 3)
            ->set('has_written_contract', '1')
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for roofing.');

        return $deadline->documentType->slug === 'prelim_notice'
            ? $component->set('amount_claimed', 5000.00)
            : $component->fill(['base_contract_amount' => '5000', 'credits_deductions' => '0', 'payments_received' => '0'])
                ->set('amount_claimed', 5000.00);
    }
}

describe('the catalogue and the state data', function () {
    it('gives every field a type, a label, a one-line hint and whether it is required', function () {
        $catalogue = config('lien.wizard_fields');

        expect(array_keys($catalogue))->toEqualCanonicalizing([
            'license_number', 'contract_date', 'contract_type', 'notice_served_at', 'notice_served_method',
            'noc_status', 'months_of_work', 'owner_interest',
        ]);

        foreach ($catalogue as $key => $field) {
            expect($field['type'])->toBeIn(WizardFields::TYPES)
                ->and($field['label'])->toBeString()->not->toBeEmpty()
                ->and($field['hint'])->toBeString()->not->toBeEmpty()->not->toContain("\n")
                ->and($field['required'])->toBeBool();

            if (in_array($field['type'], ['select', 'radio'], true)) {
                expect(WizardFields::options($key))->not->toBeEmpty();
            }

            if (isset($field['required_with'])) {
                expect($catalogue)->toHaveKey($field['required_with']);
            }

            expect($field['when'] ?? 'prelim_required')->toBe('prelim_required');
        }

        expect(WizardFields::options('contract_type'))->toBe(['written' => 'Written', 'oral' => 'Oral'])
            ->and(WizardFields::options('noc_status'))->toBe(['yes' => 'Yes', 'no' => 'No', 'unknown' => 'Not sure'])
            ->and(array_keys(WizardFields::options('notice_served_method')))->toBe(UpdateLienDocumentDetails::NOTICE_METHODS);
    });

    it('lists only catalogue fields, for document kinds that exist, in every state file', function () {
        $catalogue = config('lien.wizard_fields');
        $files = glob(database_path('data/lien_documents/*.php'));
        $listing = 0;

        expect($files)->not->toBeEmpty();

        foreach ($files as $file) {
            $data = require $file;
            $name = basename($file);

            expect($data['wizard_fields'] ?? [])->toBeArray();

            foreach ($data['wizard_fields'] ?? [] as $kind => $keys) {
                $listing++;

                expect(in_array($kind, LienDocumentRegistry::KINDS, true))->toBeTrue("{$name} lists questions for unknown document kind {$kind}")
                    ->and($keys)->toBeArray()->not->toBeEmpty()
                    ->and(array_values(array_unique($keys)))->toBe($keys);

                foreach ($keys as $key) {
                    expect(array_key_exists($key, $catalogue))->toBeTrue("{$name} lists {$key}, which is not in config lien.wizard_fields");
                }
            }
        }

        expect($listing)->toBeGreaterThan(0);
    });

    it('lists a field only where the state\'s document prints it, and the notice of completion only where it moves the deadline', function () {
        $section = [
            'license_number' => 'license',
            'contract_date' => 'contract_date',
            'contract_type' => 'contract_type',
            'notice_served_at' => 'prior_notice',
            'notice_served_method' => 'prior_notice',
            'months_of_work' => 'months_of_work',
            'owner_interest' => 'owner_interest',
        ];

        foreach (glob(database_path('data/lien_documents/*.php')) as $file) {
            $state = strtoupper(basename($file, '.php'));
            $rules = LienDocumentRegistry::for($state);

            foreach ($rules['wizard_fields'] as $kind => $keys) {
                $form = $rules['kinds'][$kind];
                $body = file_get_contents(resource_path('views/'.str_replace('.', '/', $form['body']).'.blade.php'));

                foreach ($keys as $key) {
                    if ($key === 'noc_status') {
                        expect(LienStateRule::find($state)?->noc_shortens_deadline)->toBeTrue("{$state} asks about a notice of completion, but one does not shorten its deadline");

                        continue;
                    }

                    // The generic bodies print a fact when its section is on; a
                    // state's own body prints the detail it names.
                    $printed = str_contains($form['body'], '.generic-')
                        ? ! empty($form['sections'][$section[$key]])
                        : str_contains($body, $key);

                    expect($printed)->toBeTrue("{$state} {$kind} asks for {$key}, which its document does not print");
                }
            }
        }
    });

    it('stores every document detail under the key the admin Document details card edits', function () {
        $project = stateFieldsProject($this->business, 'KS');
        [, $filing] = stateFieldsFiling($project);
        $samples = ['text' => 'Sample', 'date' => '2026-06-15'];

        foreach (config('lien.wizard_fields') as $key => $field) {
            if (in_array($key, WizardFields::PROJECT_FIELDS, true)) {
                continue;
            }

            $value = $samples[$field['type']] ?? array_key_first(WizardFields::options($key));

            app(UpdateLienDocumentDetails::class)->execute($filing->fresh(), [$key => $value]);

            expect($filing->fresh()->document_details_json)->toBe([$key => $value]);
        }
    });

    it('asks a claimant about the preliminary notice only where the state requires one from them', function () {
        // Florida: subcontractors, sub-subcontractors and suppliers. Arizona: everyone.
        $flSub = stateFieldsProject($this->business, 'FL', ClaimantType::Subcontractor);
        $flGc = stateFieldsProject($this->business, 'FL');
        $azGc = stateFieldsProject($this->business, 'AZ');

        expect(array_keys(WizardFields::forProject($flSub, 'mechanics_lien')))->toBe(['notice_served_at', 'notice_served_method'])
            ->and(WizardFields::forProject($flGc, 'mechanics_lien'))->toBe([])
            ->and(array_keys(WizardFields::forProject($azGc, 'mechanics_lien')))
            ->toBe(['license_number', 'contract_date', 'notice_served_at', 'notice_served_method', 'noc_status'])
            ->and(WizardFields::forProject($flSub, 'mechanics_lien')['notice_served_at']['label'])->toBe('Date your Notice to Owner was served')
            ->and(WizardFields::forProject($azGc, 'mechanics_lien')['notice_served_at']['label'])->toBe('Date your Arizona Preliminary Twenty Day Lien Notice was served');

        // The "who hired you" answer wins over the claimant type, as on the documents.
        $flSub->update(['hired_by' => 'owner']);

        expect(WizardFields::forProject($flSub->fresh(), 'mechanics_lien'))->toBe([]);
    });
});

describe('the Work step', function () {
    it('asks the state\'s questions for the document after the dates', function () {
        $project = stateFieldsProject($this->business, 'KS');
        [$deadline] = stateFieldsFiling($project);

        stateFieldsWorkStep($project, $deadline)
            ->assertSeeInOrder(['Last Furnish Date', 'Details for Kansas', 'Contractor license number', 'Contract date *', 'Amount & Contract'])
            ->assertSee('We need these for your mechanics lien in Kansas.')
            ->assertSee('Leave it blank if your work does not need a license.')
            ->assertSee('If it was not written, the day you agreed to do the work.')
            ->assertSeeHtml('wire:model="stateAnswers.license_number"')
            ->assertSeeHtml('maxlength="100"')
            ->assertSeeHtml('wire:model.self="stateAnswers.contract_date"')
            ->assertDontSee('Contractor license number *');
    });

    it('asks nothing extra for a document or a state that lists nothing', function () {
        $kansas = stateFieldsProject($this->business, 'KS');
        [$release] = stateFieldsFiling($kansas, 'lien_release');

        Livewire::test(FilingWizard::class, ['project' => $kansas, 'deadline' => $release])
            ->set('step', 3)
            ->assertSee('The lien being released')
            ->assertDontSee('Details for Kansas')
            ->assertDontSee('Contractor license number');

        $georgia = stateFieldsProject($this->business, 'GA', ClaimantType::Gc, ['jobsite_county' => 'Fulton']);
        [$lien] = stateFieldsFiling($georgia);

        stateFieldsWorkStep($georgia, $lien)->assertDontSee('Details for Georgia');
    });

    it('asks a Florida subcontractor for the notice to owner, and not a contractor hired by the owner', function () {
        $sub = stateFieldsProject($this->business, 'FL', ClaimantType::Subcontractor, ['jobsite_county' => 'Pinellas']);
        [$deadline] = stateFieldsFiling($sub);

        stateFieldsWorkStep($sub, $deadline)
            ->assertSee('Details for Florida')
            ->assertSee('Date your Notice to Owner was served')
            ->assertSee('How your Notice to Owner was served')
            ->assertSeeHtml('wire:model="stateAnswers.notice_served_method"')
            ->assertSeeHtml('value="certified_mail"')
            ->assertSee('Certified mail, return receipt requested');

        $gc = stateFieldsProject($this->business, 'FL', ClaimantType::Gc, ['jobsite_county' => 'Pinellas']);
        [$gcDeadline] = stateFieldsFiling($gc);

        stateFieldsWorkStep($gc, $gcDeadline)
            ->assertDontSee('Details for Florida')
            ->assertDontSee('Notice to Owner was served');
    });

    it('asks Texas for the months of work on the notice of claim', function () {
        $project = stateFieldsProject($this->business, 'TX', ClaimantType::Subcontractor, ['jobsite_county' => 'Travis']);
        [$deadline] = stateFieldsFiling($project, 'prelim_notice');

        stateFieldsWorkStep($project, $deadline)
            ->assertSee('Months you did the work *')
            ->assertDontSee('Notice of Claim for Unpaid Labor or Materials was served');
    });
});

describe('required answers', function () {
    it('needs the required ones, not the optional ones, and no future dates', function () {
        $project = stateFieldsProject($this->business, 'KS');
        [$deadline] = stateFieldsFiling($project);

        $component = stateFieldsWorkStep($project, $deadline)
            ->call('nextStep')
            ->assertHasErrors(['stateAnswers.contract_date' => 'required'])
            ->assertHasNoErrors(['stateAnswers.license_number'])
            ->assertSet('step', 3);

        expect($component->errors()->first('stateAnswers.contract_date'))->toBe('Enter the contract date.');

        $component->set('stateAnswers.contract_date', '2026-10-21')
            ->call('nextStep')
            ->assertHasErrors(['stateAnswers.contract_date' => 'before_or_equal'])
            ->assertSet('step', 3);

        expect($component->errors()->first('stateAnswers.contract_date'))->toBe("This date can't be in the future.");

        $component->set('stateAnswers.contract_date', '2026-08-28')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);
    });

    it('needs how the notice was served once its date is entered', function () {
        $project = stateFieldsProject($this->business, 'FL', ClaimantType::Subcontractor, ['jobsite_county' => 'Pinellas']);
        [$deadline] = stateFieldsFiling($project);

        $component = stateFieldsWorkStep($project, $deadline)
            ->set('stateAnswers.notice_served_at', '2026-09-10')
            ->call('nextStep')
            ->assertHasErrors(['stateAnswers.notice_served_method' => 'required_with'])
            ->assertSet('step', 3);

        expect($component->errors()->first('stateAnswers.notice_served_method'))->toBe('Choose an answer.');

        $component->set('stateAnswers.notice_served_method', 'fax')
            ->call('nextStep')
            ->assertHasErrors(['stateAnswers.notice_served_method' => 'in'])
            ->set('stateAnswers.notice_served_method', 'certified_mail')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);
    });

    it('lets a claimant leave the optional notice questions blank', function () {
        $project = stateFieldsProject($this->business, 'FL', ClaimantType::Subcontractor, ['jobsite_county' => 'Pinellas']);
        [$deadline, $filing] = stateFieldsFiling($project);

        stateFieldsWorkStep($project, $deadline)
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        expect($filing->fresh()->document_details_json)->toBeNull();
    });
});

describe('notice of completion', function () {
    it('asks for the status, and the recorded date for a Yes, and saves both on the project', function () {
        $project = stateFieldsProject($this->business, 'CA', ClaimantType::Gc, ['jobsite_county' => 'Los Angeles', 'noc_status' => NocStatus::Unknown]);
        [$deadline] = stateFieldsFiling($project);

        $component = stateFieldsWorkStep($project, $deadline)
            ->assertSee('Details for California')
            ->assertSee('Has a notice of completion been recorded for this project? *')
            ->assertSee('Not sure')
            ->assertDontSee('Date the notice of completion was recorded')
            // "Not sure" saved by the project form without asking is asked again.
            ->assertSet('stateAnswers.noc_status', null)
            ->call('nextStep')
            ->assertHasErrors(['stateAnswers.noc_status' => 'required'])
            ->set('stateAnswers.noc_status', 'yes')
            ->assertSee('Date the notice of completion was recorded *')
            ->call('nextStep')
            ->assertHasErrors(['noc_recorded_at' => 'required'])
            ->assertSet('step', 3);

        expect($component->errors()->first('noc_recorded_at'))->toBe('Enter the date the notice of completion was recorded.');

        $component->set('noc_recorded_at', '2026-10-16')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        $project->refresh();

        expect($project->noc_status)->toBe(NocStatus::Yes)
            ->and($project->noc_recorded_at->toDateString())->toBe('2026-10-16');

        // Back on the Work step the answer is there; a No drops the date.
        Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->assertSet('stateAnswers.noc_status', 'yes')
            ->assertSet('noc_recorded_at', '2026-10-16')
            ->set('step', 3)
            ->set('stateAnswers.noc_status', 'no')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        expect($project->fresh()->noc_status)->toBe(NocStatus::No)
            ->and($project->fresh()->noc_recorded_at)->toBeNull()
            ->and(LienFiling::where('project_id', $project->id)->first()->document_details_json)->toBeNull();
    });

    it('leaves the project\'s notice of completion alone where the state does not ask', function () {
        $project = stateFieldsProject($this->business, 'KS', ClaimantType::Gc, ['noc_status' => NocStatus::Yes, 'noc_recorded_at' => '2026-10-01']);
        [$deadline] = stateFieldsFiling($project);

        stateFieldsWorkStep($project, $deadline)
            ->set('stateAnswers.contract_date', '2026-08-28')
            ->set('stateAnswers.noc_status', 'no')
            ->call('nextStep')
            ->assertSet('step', 4);

        expect($project->fresh()->noc_status)->toBe(NocStatus::Yes)
            ->and($project->fresh()->noc_recorded_at->toDateString())->toBe('2026-10-01');
    });
});

describe('saving the answers', function () {
    it('merges them into the document details, keeping admin values and the notice-of-intent answer', function () {
        $project = stateFieldsProject($this->business, 'KS');
        [$deadline, $filing] = stateFieldsFiling($project, 'mechanics_lien', [
            'document_details_json' => [
                'signer_name' => 'Kathryn Melendez',
                'license_number' => 'KS-ADMIN-1',
                'noi_served_on' => '2026-10-01',
                'noi_served_to' => 'owner',
                'noi_confirmed_at' => '2026-10-02T14:00:00+00:00',
            ],
        ]);

        // The admin's license number shows; the customer clears it and enters the contract date.
        stateFieldsWorkStep($project, $deadline)
            ->assertSet('stateAnswers.license_number', 'KS-ADMIN-1')
            ->set('stateAnswers.license_number', '  ')
            ->set('stateAnswers.contract_date', '2026-08-28')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        expect($filing->fresh()->document_details_json)->toEqual([
            'signer_name' => 'Kathryn Melendez',
            'license_number' => 'KS-ADMIN-1',
            'noi_served_on' => '2026-10-01',
            'noi_served_to' => 'owner',
            'noi_confirmed_at' => '2026-10-02T14:00:00+00:00',
            'contract_date' => '2026-08-28',
            LienFiling::WIZARD_FIELDS_ENTERED_AT => ['contract_date' => now()->toIso8601String()],
        ]);

        // Later the customer enters their own license number, then takes it back out.
        $this->travel(5)->minutes();

        $component = Livewire::test(FilingWizard::class, ['project' => $project->fresh(), 'deadline' => $deadline])
            ->assertSet('stateAnswers.contract_date', '2026-08-28')
            ->set('step', 3)
            ->set('stateAnswers.license_number', ' KS-12345 ')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        $details = $filing->fresh()->document_details_json;

        expect($details['license_number'])->toBe('KS-12345')
            ->and($details['contract_date'])->toBe('2026-08-28')
            ->and($details[LienFiling::WIZARD_FIELDS_ENTERED_AT])->toEqual([
                // Unchanged, so still the first time.
                'contract_date' => now()->subMinutes(5)->toIso8601String(),
                'license_number' => now()->toIso8601String(),
            ]);

        $component->set('step', 3)
            ->set('stateAnswers.license_number', '')
            ->call('nextStep')
            ->assertSet('step', 4);

        $details = $filing->fresh()->document_details_json;

        expect($details)->not->toHaveKey('license_number')
            ->and(array_keys($details[LienFiling::WIZARD_FIELDS_ENTERED_AT]))->toBe(['contract_date'])
            ->and($details['signer_name'])->toBe('Kathryn Melendez')
            ->and($details['noi_served_on'])->toBe('2026-10-01');
    });

    it('fills in the business\'s license number and the date we sent the preliminary notice', function () {
        $this->business->update(['contractor_license_number' => 'AZ-ROC-777']);
        $project = stateFieldsProject($this->business, 'AZ', ClaimantType::Gc, [
            'jobsite_county' => 'Maricopa',
            'prelim_notice_sent_at' => CarbonImmutable::parse('2026-09-03 21:00:00', 'America/New_York')->utc(),
        ]);
        [$deadline, $filing] = stateFieldsFiling($project);

        stateFieldsWorkStep($project, $deadline)
            ->assertSet('stateAnswers.license_number', 'AZ-ROC-777')
            ->assertSet('stateAnswers.notice_served_at', '2026-09-03')
            ->set('stateAnswers.notice_served_method', 'certified_mail')
            ->set('stateAnswers.contract_date', '2026-08-28')
            ->set('stateAnswers.noc_status', 'unknown')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        $details = $filing->fresh()->document_details_json;

        expect($details)->toMatchArray([
            'license_number' => 'AZ-ROC-777',
            'contract_date' => '2026-08-28',
            'notice_served_at' => '2026-09-03',
            'notice_served_method' => 'certified_mail',
        ])
            ->and(array_keys($details[LienFiling::WIZARD_FIELDS_ENTERED_AT]))->toEqualCanonicalizing(['license_number', 'contract_date', 'notice_served_at', 'notice_served_method'])
            ->and($details)->not->toHaveKey('noc_status')
            ->and($project->fresh()->noc_status)->toBe(NocStatus::Unknown);
    });

    it('keeps the estimate on a preliminary notice', function () {
        $project = stateFieldsProject($this->business, 'TX', ClaimantType::Subcontractor, ['jobsite_county' => 'Travis']);
        [$deadline, $filing] = stateFieldsFiling($project, 'prelim_notice');

        stateFieldsWorkStep($project, $deadline)
            ->call('nextStep')
            ->assertHasErrors(['stateAnswers.months_of_work' => 'required'])
            ->set('stateAnswers.months_of_work', 'September 2026, October 2026')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        expect($filing->fresh()->document_details_json)->toEqual([
            'estimated_price_cents' => 500000,
            'months_of_work' => 'September 2026, October 2026',
            LienFiling::WIZARD_FIELDS_ENTERED_AT => ['months_of_work' => now()->toIso8601String()],
        ]);
    });
});

describe('admin Document details card', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('lien.view', 'lien.update');
    });

    it('marks what the customer entered, until staff change it', function () {
        $project = stateFieldsProject($this->business, 'KS');
        [$deadline, $filing] = stateFieldsFiling($project);

        stateFieldsWorkStep($project, $deadline)
            ->set('stateAnswers.license_number', 'KS-12345')
            ->set('stateAnswers.contract_date', '2026-08-28')
            ->call('nextStep')
            ->assertSet('step', 4);

        $filing->update(['status' => FilingStatus::Paid, 'paid_at' => now()]);

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
            ->assertSee('KS-12345 (from the customer)')
            ->assertSee('Aug 28, 2026 (from the customer)');

        // Staff correct the license number a minute later; the contract date is untouched.
        $this->travel(1)->minutes();

        $changes = app(UpdateLienDocumentDetails::class)->execute($filing->fresh(), [
            'license_number' => 'KS-99999',
            'contract_date' => '2026-08-28',
        ]);

        expect($changes)->toBe(['license_number' => ['from' => 'KS-12345', 'to' => 'KS-99999']])
            ->and($filing->fresh()->document_details_json)->toHaveKey(LienFiling::WIZARD_FIELDS_ENTERED_AT)
            ->and($filing->fresh()->customerEnteredDetailKeys())->toBe(['contract_date']);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
            ->assertSee('KS-99999')
            ->assertDontSee('KS-99999 (from the customer)')
            ->assertSee('Aug 28, 2026 (from the customer)');
    });

    it('counts a staff change in the same second as the customer\'s entry as the staff\'s', function () {
        $project = stateFieldsProject($this->business, 'KS');
        [, $filing] = stateFieldsFiling($project, 'mechanics_lien', [
            'document_details_json' => [
                'contract_date' => '2026-08-28',
                LienFiling::WIZARD_FIELDS_ENTERED_AT => ['contract_date' => now()->toIso8601String()],
            ],
        ]);

        expect($filing->customerEnteredDetailKeys())->toBe(['contract_date']);

        app(UpdateLienDocumentDetails::class)->execute($filing->fresh(), ['contract_date' => '2026-08-27']);

        expect($filing->fresh()->customerEnteredDetailKeys())->toBe([]);
    });
});
