<?php

namespace Tests\Feature\Forms\Support;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;

/**
 * Builds a MultiStateFormRunner-ready FormApplication for tests.
 *
 * Replaces four ad-hoc bootXRunner free functions that all did the same
 * thing: create a User + Business + FormApplication + FormApplicationState,
 * then `actingAs` the user with a `current_business_id` session entry.
 *
 * Sensible defaults give you a sales_tax_permit application on the
 * tax_identification step with one selected state (CA) and a coreData
 * blob containing every field needed to pass earlier-step validation.
 * Override per test with the fluent setters.
 *
 * Usage:
 *   $application = RunnerTestFactory::make()
 *       ->coreData(['entity_type' => 'sole_prop', 'individual_ssn' => '123-45-6789'])
 *       ->onStep('tax_identification')
 *       ->forStates(['CA'])
 *       ->boot();
 */
final class RunnerTestFactory
{
    private string $formType = 'sales_tax_permit';

    private string $phase = 'core';

    private string $stepKey = 'tax_identification';

    private int $stateIndex = 0;

    /** @var array<int, string> */
    private array $selectedStates = ['CA'];

    /** @var array<string, mixed> */
    private array $coreDataOverrides = [];

    /** @var array<string, array<string, mixed>> */
    private array $stateDataOverrides = [];

    private ?string $businessName = null;

    private ?string $legalName = null;

    public static function make(): self
    {
        return new self;
    }

    public function formType(string $formType): self
    {
        $this->formType = $formType;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function coreData(array $data): self
    {
        $this->coreDataOverrides = array_merge($this->coreDataOverrides, $data);

        return $this;
    }

    public function onStep(string $key): self
    {
        $this->stepKey = $key;

        return $this;
    }

    public function inPhase(string $phase): self
    {
        $this->phase = $phase;

        return $this;
    }

    public function atStateIndex(int $index): self
    {
        $this->stateIndex = $index;

        return $this;
    }

    /**
     * @param  array<int, string>  $codes
     */
    public function forStates(array $codes): self
    {
        $this->selectedStates = $codes;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function withStateData(string $code, array $data): self
    {
        $this->stateDataOverrides[$code] = array_merge(
            $this->stateDataOverrides[$code] ?? [],
            $data
        );

        return $this;
    }

    public function withBusinessName(string $name): self
    {
        $this->businessName = $name;

        return $this;
    }

    public function withLegalName(string $legalName): self
    {
        $this->legalName = $legalName;

        return $this;
    }

    public function boot(): FormApplication
    {
        $user = User::factory()->create();

        $business = Business::create([
            'name' => $this->businessName ?? 'Form Runner Test',
            'legal_name' => $this->legalName ?? 'Form Runner Test LLC',
            'onboarding_completed_at' => now(),
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $application = FormApplication::create([
            'business_id' => $business->id,
            'form_type' => $this->formType,
            'definition_version' => 1,
            'selected_states' => $this->selectedStates,
            'status' => 'draft',
            'current_phase' => $this->phase,
            'current_step_key' => $this->stepKey,
            'current_state_index' => $this->stateIndex,
            'core_data' => $this->buildCoreData(),
            'created_by_user_id' => $user->id,
            'paid_at' => now(),
        ]);

        foreach ($this->selectedStates as $code) {
            FormApplicationState::create([
                'form_application_id' => $application->id,
                'state_code' => $code,
                'status' => 'pending',
                'data' => $this->stateDataOverrides[$code] ?? [],
            ]);
        }

        // Pest's global test() returns the active TestCase so callers
        // get the same effect as $this->actingAs($user) inside a class
        // test method.
        test()->actingAs($user)->withSession(['current_business_id' => $business->id]);

        return $application;
    }

    /**
     * Sensible defaults that satisfy every required field through the
     * tax_identification step so tests positioned on later steps don't
     * fail validation on prior-step lookups.
     *
     * @return array<string, mixed>
     */
    private function buildCoreData(): array
    {
        $entityType = $this->coreDataOverrides['entity_type'] ?? 'corporation';
        $primaryState = $this->selectedStates[0] ?? 'CA';

        $defaults = [
            'legal_name' => $this->legalName ?? 'Form Runner Test LLC',
            'entity_type' => $entityType,
            'formation_state' => $primaryState,
            'business_email' => 'owner@example.com',
            'business_phone' => '(555) 555-1234',
            'business_address' => [
                'line1' => '1 Test St',
                'city' => 'Testville',
                'state' => $primaryState,
                'zip' => '00000',
            ],
            'mailing_address_same' => '1',
            'naics_code' => '541512',
            'business_description' => 'Software',
            'reason_for_applying' => 'new_business',
            'business_start_date' => '2020-01-01',
            'fein' => '12-3456789',
        ];

        if ($entityType === 'sole_prop') {
            // Sole props see SSN on tax_identification; seed it so tests
            // positioned past identity don't fail on missing PII.
            $defaults['individual_ssn'] = '123-45-6789';
        }

        return array_merge($defaults, $this->coreDataOverrides);
    }
}
