<?php

namespace Database\Seeders;

use App\Domains\Lien\Models\LienDeadlineRule;
use App\Domains\Lien\Models\LienDocumentType;
use Illuminate\Database\Seeder;

class LienDeadlineRuleSeeder extends Seeder
{
    /**
     * Preliminary Notice rules by state (from CSV).
     * States not listed here do not require pre-notice.
     */
    private const PRELIM_NOTICE_RULES = [
        'AZ' => ['days' => 20, 'required_for' => 'subs, suppliers'],
        'AR' => ['days' => 10, 'required_for' => 'subs (residential)'],
        'CA' => ['days' => 20, 'required_for' => 'subs, suppliers'],
        'FL' => ['days' => 45, 'required_for' => 'subs, suppliers'],
        'GA' => ['days' => 30, 'required_for' => 'subs, suppliers'],
        'ID' => ['days' => 30, 'required_for' => 'subs (residential)'],
        'IN' => ['days' => 30, 'required_for' => 'subs (1-2 family)'],
        'IA' => ['days' => 30, 'required_for' => 'everyone'],
        'KY' => ['days' => 75, 'required_for' => 'subs'],
        'LA' => ['days' => 10, 'required_for' => 'subs, suppliers'],
        'MI' => ['days' => 20, 'required_for' => 'subs, suppliers'],
        'MN' => ['days' => 45, 'required_for' => 'everyone'],
        'NV' => ['days' => 31, 'required_for' => 'subs, suppliers'],
        'NM' => ['days' => 60, 'required_for' => 'subs, suppliers'],
        'NC' => ['days' => 15, 'required_for' => 'subs, suppliers'],
        'OH' => ['days' => 21, 'required_for' => 'subs, suppliers'],
        'OK' => ['days' => 75, 'required_for' => 'subs, suppliers'],
        'OR' => ['days' => 8, 'required_for' => 'everyone'],
        'RI' => ['days' => 10, 'required_for' => 'prime'],
        'TN' => ['days' => 90, 'required_for' => 'subs'],
        'TX' => ['days' => 15, 'required_for' => 'subs, suppliers'],
        'UT' => ['days' => 20, 'required_for' => 'everyone'],
        'WA' => ['days' => 60, 'required_for' => 'subs, suppliers'],
        'WY' => ['days' => 30, 'required_for' => 'everyone'],
    ];

    /**
     * Mechanics Lien rules by state (from CSV).
     * All states have lien rules. Special calc_methods for TX, NY, VA.
     */
    private const MECHANICS_LIEN_RULES = [
        'AL' => ['days' => 180, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'AK' => ['days' => 120, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'AZ' => ['days' => 120, 'trigger' => 'completion_date', 'calc_method' => 'days_after_date'],
        'AR' => ['days' => 120, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'CA' => ['days' => 90, 'trigger' => 'completion_date', 'calc_method' => 'days_after_date'],
        'CO' => ['days' => 120, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'CT' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'DE' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'FL' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'GA' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'HI' => ['days' => 45, 'trigger' => 'completion_date', 'calc_method' => 'days_after_date'],
        'ID' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'IL' => ['days' => 120, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'IN' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'IA' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'KS' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'KY' => ['days' => 180, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'LA' => ['days' => 60, 'trigger' => 'completion_date', 'calc_method' => 'days_after_date'],
        'ME' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'MD' => ['days' => 180, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'MA' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'MI' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'MN' => ['days' => 120, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'MS' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'MO' => ['days' => 120, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'MT' => ['days' => 90, 'trigger' => 'completion_date', 'calc_method' => 'days_after_date'],
        'NE' => ['days' => 120, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'NV' => ['days' => 90, 'trigger' => 'completion_date', 'calc_method' => 'days_after_date'],
        'NH' => ['days' => 120, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'NJ' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'NM' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        // NY uses months_after_date - handled separately
        'NC' => ['days' => 120, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'ND' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'OH' => ['days' => 75, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'OK' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'OR' => ['days' => 75, 'trigger' => 'completion_date', 'calc_method' => 'days_after_date'],
        'PA' => ['days' => 180, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'RI' => ['days' => 200, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'SC' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'SD' => ['days' => 120, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'TN' => ['days' => 90, 'trigger' => 'completion_date', 'calc_method' => 'days_after_date'],
        // TX uses month_day_after_month_of_date - handled separately
        'UT' => ['days' => 180, 'trigger' => 'completion_date', 'calc_method' => 'days_after_date'],
        'VT' => ['days' => 180, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        // VA uses days_after_end_of_month_of_date - handled separately
        'WA' => ['days' => 90, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'WV' => ['days' => 100, 'trigger' => 'completion_date', 'calc_method' => 'days_after_date'],
        'WI' => ['days' => 180, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
        'WY' => ['days' => 120, 'trigger' => 'last_furnish_date', 'calc_method' => 'days_after_date'],
    ];

    public function run(): void
    {
        $prelimNotice = LienDocumentType::where('slug', 'prelim_notice')->first();
        $mechanicsLien = LienDocumentType::where('slug', 'mechanics_lien')->first();
        $lienRelease = LienDocumentType::where('slug', 'lien_release')->first();

        if (! $prelimNotice || ! $mechanicsLien || ! $lienRelease) {
            $this->command->error('Document types not found. Run LienDocumentTypeSeeder first.');

            return;
        }

        // Clear existing rules (disable FK checks for truncate)
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        LienDeadlineRule::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Seed Preliminary Notice rules
        $this->seedPreliminaryNoticeRules($prelimNotice);

        // Seed Mechanics Lien rules (standard states)
        $this->seedMechanicsLienRules($mechanicsLien);

        // Seed special state rules (TX, NY, VA)
        $this->seedTexasRules($mechanicsLien);
        $this->seedNewYorkRules($mechanicsLien);
        $this->seedVirginiaRules($mechanicsLien);

        // Seed Lien Release rules (optional, no deadline)
        $this->seedLienReleaseRules($lienRelease);

        $this->command->info('Seeded deadline rules for all 50 states.');
    }

    private function seedPreliminaryNoticeRules(LienDocumentType $docType): void
    {
        foreach (self::PRELIM_NOTICE_RULES as $state => $data) {
            LienDeadlineRule::create([
                'state' => $state,
                'document_type_id' => $docType->id,
                'claimant_type' => 'any',
                'trigger_event' => 'first_furnish_date',
                'calc_method' => 'days_after_date',
                'offset_days' => $data['days'],
                'offset_months' => null,
                'day_of_month' => null,
                'is_required' => true,
                'effective_scope' => 'both',
                'is_placeholder' => false,
                'conditions_json' => null,
                'notes' => "Required for: {$data['required_for']}",
                'data_source' => 'csv_v1',
            ]);
        }
    }

    private function seedMechanicsLienRules(LienDocumentType $docType): void
    {
        foreach (self::MECHANICS_LIEN_RULES as $state => $data) {
            LienDeadlineRule::create([
                'state' => $state,
                'document_type_id' => $docType->id,
                'claimant_type' => 'any',
                'trigger_event' => $data['trigger'],
                'calc_method' => $data['calc_method'],
                'offset_days' => $data['days'],
                'offset_months' => null,
                'day_of_month' => null,
                'is_required' => true,
                'effective_scope' => 'both',
                'is_placeholder' => false,
                'conditions_json' => null,
                'notes' => null,
                'data_source' => 'csv_v1',
            ]);
        }
    }

    /**
     * Texas uses "15th of the Nth month" calculation.
     * Residential: 3rd month, Commercial: 4th month.
     * Sub/Supplier uses later_of anchor for special fab delivery.
     */
    private function seedTexasRules(LienDocumentType $docType): void
    {
        // TX Sub/Supplier - Residential (15th of 3rd month)
        LienDeadlineRule::create([
            'state' => 'TX',
            'document_type_id' => $docType->id,
            'claimant_type' => 'any',
            'trigger_event' => 'last_furnish_date',
            'calc_method' => 'month_day_after_month_of_date',
            'offset_days' => null,
            'offset_months' => 3,
            'day_of_month' => 15,
            'is_required' => true,
            'effective_scope' => 'residential',
            'is_placeholder' => false,
            'conditions_json' => ['anchor' => 'later_of', 'dates' => ['last_furnish_date', 'special_fab_delivery_date']],
            'notes' => 'Lien affidavit must be filed by the 15th of the 3rd month after indebtedness accrues.',
            'data_source' => 'csv_v1',
        ]);

        // TX Sub/Supplier - Commercial (15th of 4th month)
        LienDeadlineRule::create([
            'state' => 'TX',
            'document_type_id' => $docType->id,
            'claimant_type' => 'any',
            'trigger_event' => 'last_furnish_date',
            'calc_method' => 'month_day_after_month_of_date',
            'offset_days' => null,
            'offset_months' => 4,
            'day_of_month' => 15,
            'is_required' => true,
            'effective_scope' => 'commercial',
            'is_placeholder' => false,
            'conditions_json' => ['anchor' => 'later_of', 'dates' => ['last_furnish_date', 'special_fab_delivery_date']],
            'notes' => 'Lien affidavit must be filed by the 15th of the 4th month after indebtedness accrues.',
            'data_source' => 'csv_v1',
        ]);

        // TX Original Contractor - Residential (15th of 3rd month from completion)
        LienDeadlineRule::create([
            'state' => 'TX',
            'document_type_id' => $docType->id,
            'claimant_type' => 'gc',
            'trigger_event' => 'completion_date',
            'calc_method' => 'month_day_after_month_of_date',
            'offset_days' => null,
            'offset_months' => 3,
            'day_of_month' => 15,
            'is_required' => true,
            'effective_scope' => 'residential',
            'is_placeholder' => false,
            'conditions_json' => null,
            'notes' => 'Original contractor: 15th of 3rd month after completion/termination.',
            'data_source' => 'csv_v1',
        ]);

        // TX Original Contractor - Commercial (15th of 4th month from completion)
        LienDeadlineRule::create([
            'state' => 'TX',
            'document_type_id' => $docType->id,
            'claimant_type' => 'gc',
            'trigger_event' => 'completion_date',
            'calc_method' => 'month_day_after_month_of_date',
            'offset_days' => null,
            'offset_months' => 4,
            'day_of_month' => 15,
            'is_required' => true,
            'effective_scope' => 'commercial',
            'is_placeholder' => false,
            'conditions_json' => null,
            'notes' => 'Original contractor: 15th of 4th month after completion/termination.',
            'data_source' => 'csv_v1',
        ]);
    }

    /**
     * New York uses months_after_date calculation.
     * Residential (single-family): 4 months, Commercial: 8 months.
     */
    private function seedNewYorkRules(LienDocumentType $docType): void
    {
        // NY Residential (single-family dwelling) - 4 months
        LienDeadlineRule::create([
            'state' => 'NY',
            'document_type_id' => $docType->id,
            'claimant_type' => 'any',
            'trigger_event' => 'last_furnish_date',
            'calc_method' => 'months_after_date',
            'offset_days' => null,
            'offset_months' => 4,
            'day_of_month' => null,
            'is_required' => true,
            'effective_scope' => 'residential',
            'is_placeholder' => false,
            'conditions_json' => null,
            'notes' => 'Lien must be filed within 4 months for single-family dwelling.',
            'data_source' => 'csv_v1',
        ]);

        // NY Commercial - 8 months
        LienDeadlineRule::create([
            'state' => 'NY',
            'document_type_id' => $docType->id,
            'claimant_type' => 'any',
            'trigger_event' => 'last_furnish_date',
            'calc_method' => 'months_after_date',
            'offset_days' => null,
            'offset_months' => 8,
            'day_of_month' => null,
            'is_required' => true,
            'effective_scope' => 'commercial',
            'is_placeholder' => false,
            'conditions_json' => null,
            'notes' => 'Lien must be filed within 8 months of completion.',
            'data_source' => 'csv_v1',
        ]);
    }

    /**
     * Virginia uses "90 days from end of month of last work".
     */
    private function seedVirginiaRules(LienDocumentType $docType): void
    {
        LienDeadlineRule::create([
            'state' => 'VA',
            'document_type_id' => $docType->id,
            'claimant_type' => 'any',
            'trigger_event' => 'last_furnish_date',
            'calc_method' => 'days_after_end_of_month_of_date',
            'offset_days' => 90,
            'offset_months' => null,
            'day_of_month' => null,
            'is_required' => true,
            'effective_scope' => 'both',
            'is_placeholder' => false,
            'conditions_json' => null,
            'notes' => 'Lien must be filed within 90 days of the last day of the month in which work was performed.',
            'data_source' => 'csv_v1',
        ]);
    }

    /**
     * Lien Release rules - optional, file when paid.
     */
    private function seedLienReleaseRules(LienDocumentType $docType): void
    {
        $states = array_keys(self::MECHANICS_LIEN_RULES);
        $states = array_merge($states, ['NY', 'TX', 'VA']); // Add special states
        $states = array_unique($states);

        foreach ($states as $state) {
            LienDeadlineRule::create([
                'state' => $state,
                'document_type_id' => $docType->id,
                'claimant_type' => 'any',
                'trigger_event' => 'last_furnish_date',
                'calc_method' => 'days_after_date',
                'offset_days' => 365,
                'offset_months' => null,
                'day_of_month' => null,
                'is_required' => false,
                'effective_scope' => 'both',
                'is_placeholder' => true,
                'conditions_json' => null,
                'notes' => 'File after payment received. No specific deadline.',
                'data_source' => 'csv_v1',
            ]);
        }
    }
}
