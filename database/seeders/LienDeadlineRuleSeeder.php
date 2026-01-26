<?php

namespace Database\Seeders;

use App\Domains\Lien\Models\LienDocumentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LienDeadlineRuleSeeder extends Seeder
{
    /**
     * Claimant types to process from the CSV.
     * Maps CSV column prefix to database/enum value.
     */
    private const CLAIMANT_TYPES = [
        'gc' => 'gc',
        'sub' => 'subcontractor',
        'subsub' => 'sub_sub_contractor',
        'supplier_owner' => 'supplier_to_owner',
        'supplier_gc' => 'supplier_to_contractor',
        'supplier_sub' => 'supplier_to_subcontractor',
    ];

    /**
     * Seed the lien_deadline_rules table from CSV.
     */
    public function run(): void
    {
        $csvPath = database_path('seeders/data/lien_rules_by_state_v3_checked_csv.csv');

        if (! file_exists($csvPath)) {
            $this->command->error("CSV file not found: {$csvPath}");

            return;
        }

        // Get document type IDs
        $prelimNotice = LienDocumentType::where('slug', 'prelim_notice')->first();
        $noi = LienDocumentType::where('slug', 'noi')->first();
        $mechanicsLien = LienDocumentType::where('slug', 'mechanics_lien')->first();
        $lienRelease = LienDocumentType::where('slug', 'lien_release')->first();

        if (! $prelimNotice || ! $mechanicsLien) {
            $this->command->error('Required document types not found. Run LienDocumentTypeSeeder first.');

            return;
        }

        // Truncate table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('lien_deadline_rules')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Read CSV content and convert from Windows-1252 to UTF-8
        $csvContent = file_get_contents($csvPath);
        $csvContent = mb_convert_encoding($csvContent, 'UTF-8', 'Windows-1252');

        // Parse the UTF-8 content
        $lines = explode("\n", $csvContent);
        $headers = str_getcsv(array_shift($lines));

        $prelimCount = 0;
        $noiCount = 0;
        $lienCount = 0;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $row = str_getcsv($line);
            if (count($row) !== count($headers)) {
                continue;
            }
            $data = array_combine($headers, $row);
            $state = $data['state_code'];

            // Seed Preliminary Notice rules
            $prelimCount += $this->seedPreliminaryNoticeRules($data, $state, $prelimNotice->id);

            // Seed NOI rules (if noi document type exists)
            if ($noi) {
                $noiCount += $this->seedNoiRules($data, $state, $noi->id);
            }

            // Seed Mechanics Lien rules (residential + commercial for each claimant type)
            $lienCount += $this->seedMechanicsLienRules($data, $state, $mechanicsLien->id);
        }

        $this->command->info("Seeded {$prelimCount} preliminary notice rules.");
        $this->command->info("Seeded {$noiCount} NOI rules.");
        $this->command->info("Seeded {$lienCount} mechanics lien rules.");
    }

    /**
     * Seed Preliminary Notice rules from CSV row.
     */
    private function seedPreliminaryNoticeRules(array $data, string $state, int $docTypeId): int
    {
        $count = 0;

        foreach (self::CLAIMANT_TYPES as $csvPrefix => $dbValue) {
            $requiredField = "prelim_{$csvPrefix}_required";
            $daysField = "prelim_{$csvPrefix}_deadline_days";
            $triggerField = "prelim_{$csvPrefix}_trigger";

            if (strtolower(trim($data[$requiredField] ?? '')) !== 'yes') {
                continue;
            }

            $offsetDays = $this->toNullableInt($data[$daysField] ?? '');
            $trigger = $this->emptyToDefault($data[$triggerField] ?? '', 'first_furnish_date');

            DB::table('lien_deadline_rules')->insert([
                'state' => $state,
                'document_type_id' => $docTypeId,
                'claimant_type' => $dbValue,
                'trigger_event' => $trigger,
                'calc_method' => 'days_after_date',
                'offset_days' => $offsetDays,
                'offset_months' => null,
                'day_of_month' => null,
                'is_required' => true,
                'effective_scope' => 'both',
                'is_placeholder' => false,
                'conditions_json' => null,
                'notes' => null,
                'data_source' => 'csv_v3',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * Seed NOI (Notice of Intent) rules from CSV row.
     */
    private function seedNoiRules(array $data, string $state, int $docTypeId): int
    {
        $count = 0;

        foreach (self::CLAIMANT_TYPES as $csvPrefix => $dbValue) {
            $requiredField = "noi_{$csvPrefix}_required";
            $daysField = "noi_{$csvPrefix}_lead_time_days";

            if (strtolower(trim($data[$requiredField] ?? '')) !== 'yes') {
                continue;
            }

            $leadTimeDays = $this->toNullableInt($data[$daysField] ?? '');

            // NOI is typically X days BEFORE filing lien, so we use a different approach
            // We'll store the lead time as offset_days (positive = days before lien deadline)
            DB::table('lien_deadline_rules')->insert([
                'state' => $state,
                'document_type_id' => $docTypeId,
                'claimant_type' => $dbValue,
                'trigger_event' => 'lien_filing_date', // NOI is relative to lien filing
                'calc_method' => 'days_before_date',
                'offset_days' => $leadTimeDays,
                'offset_months' => null,
                'day_of_month' => null,
                'is_required' => true,
                'effective_scope' => 'both',
                'is_placeholder' => false,
                'conditions_json' => null,
                'notes' => "NOI must be sent {$leadTimeDays} days before filing lien",
                'data_source' => 'csv_v3',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * Seed Mechanics Lien rules from CSV row.
     * Creates separate rules for residential and commercial scopes.
     */
    private function seedMechanicsLienRules(array $data, string $state, int $docTypeId): int
    {
        $count = 0;
        $scopes = ['res' => 'residential', 'com' => 'commercial'];

        foreach (self::CLAIMANT_TYPES as $csvPrefix => $dbValue) {
            foreach ($scopes as $scopeKey => $scopeValue) {
                $triggerField = "lien_{$csvPrefix}_{$scopeKey}_trigger";
                $calcMethodField = "lien_{$csvPrefix}_{$scopeKey}_calc_method";
                $offsetDaysField = "lien_{$csvPrefix}_{$scopeKey}_offset_days";
                $offsetMonthsField = "lien_{$csvPrefix}_{$scopeKey}_offset_months";
                $dayOfMonthField = "lien_{$csvPrefix}_{$scopeKey}_day_of_month";

                $trigger = $this->emptyToNull($data[$triggerField] ?? '');
                $calcMethod = $this->emptyToDefault($data[$calcMethodField] ?? '', 'days_after_date');

                // Skip if no trigger defined (means this claimant/scope combo doesn't have a rule)
                if ($trigger === null) {
                    continue;
                }

                $offsetDays = $this->toNullableInt($data[$offsetDaysField] ?? '');
                $offsetMonths = $this->toNullableInt($data[$offsetMonthsField] ?? '');
                $dayOfMonth = $this->toNullableInt($data[$dayOfMonthField] ?? '');

                // Build conditions_json for special anchor logic
                $conditions = null;
                if ($data['lien_anchor_logic'] === 'later_of' && ! empty($data['lien_anchor_alt_field'])) {
                    $conditions = json_encode([
                        'anchor' => 'later_of',
                        'dates' => [$trigger, $data['lien_anchor_alt_field']],
                    ]);
                }

                DB::table('lien_deadline_rules')->insert([
                    'state' => $state,
                    'document_type_id' => $docTypeId,
                    'claimant_type' => $dbValue,
                    'trigger_event' => $trigger,
                    'calc_method' => $calcMethod,
                    'offset_days' => $offsetDays,
                    'offset_months' => $offsetMonths,
                    'day_of_month' => $dayOfMonth,
                    'is_required' => true,
                    'effective_scope' => $scopeValue,
                    'is_placeholder' => false,
                    'conditions_json' => $conditions,
                    'notes' => null,
                    'data_source' => 'csv_v3',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $count++;
            }
        }

        return $count;
    }

    private function toNullableInt(string $value): ?int
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : (int) $trimmed;
    }

    private function emptyToNull(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function emptyToDefault(string $value, string $default): string
    {
        $trimmed = trim($value);

        return $trimmed === '' ? $default : $trimmed;
    }
}
