<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LienStateRuleSeeder extends Seeder
{
    /**
     * Seed the lien_state_rules table from CSV.
     */
    public function run(): void
    {
        $csvPath = database_path('seeders/data/lien_rules_by_state_v3_checked_csv.csv');

        if (! file_exists($csvPath)) {
            $this->command->error("CSV file not found: {$csvPath}");

            return;
        }

        // Truncate table (FK checks disabled by LienDeadlineRuleSeeder if run together)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('lien_state_rules')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Read CSV content and convert from Windows-1252 to UTF-8
        $csvContent = file_get_contents($csvPath);
        $csvContent = mb_convert_encoding($csvContent, 'UTF-8', 'Windows-1252');

        // Parse the UTF-8 content
        $lines = explode("\n", $csvContent);
        $headers = str_getcsv(array_shift($lines));

        $count = 0;
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $row = str_getcsv($line);
            if (count($row) !== count($headers)) {
                continue;
            }
            $data = array_combine($headers, $row);

            DB::table('lien_state_rules')->insert([
                'state' => $data['state_code'],

                // Pre-notice flags (derived from any prelim_*_required = yes)
                'pre_notice_required' => $this->anyPrelimRequired($data),
                'pre_notice_required_for' => $this->getPrelimRequiredFor($data),
                'noi_lead_time_days' => $this->getMaxNoiLeadTime($data),

                // Preliminary Notice Metadata
                'prelim_delivery_method' => $this->emptyToDefault($data['prelim_delivery_method'], 'any'),
                'prelim_recipients' => $this->emptyToDefault($data['prelim_recipients'], 'owner'),

                // Notice of Completion Effects
                'noc_shortens_deadline' => $this->toBool($data['noc_shortens_deadline']),
                'lien_after_noc_days' => $this->toNullableInt($data['lien_after_noc_days']),
                'noc_requires_prior_prelim' => $this->toBool($data['noc_requires_prior_prelim']),
                'noc_eliminates_rights_if_no_prelim' => $this->toBool($data['noc_eliminates_rights_if_no_prelim']),

                // Post-Lien Notice
                'post_lien_notice_required' => $this->toBool($data['post_lien_notice_required']),
                'post_lien_notice_days' => $this->toNullableInt($data['post_lien_notice_days']),
                'post_lien_notice_recipients' => $this->emptyToNull($data['post_lien_notice_recipients']),

                // Enforcement
                'enforcement_calc_method' => $this->emptyToDefault($data['enforcement_calc_method'], 'months_after_date'),
                'enforcement_deadline_days' => $this->toIntOrZero($data['enforcement_days']),
                'enforcement_deadline_months' => $this->toNullableDecimal($data['enforcement_months']),
                'enforcement_deadline_trigger' => $this->emptyToDefault($data['enforcement_trigger'], 'lien_recorded_date'),

                // Owner-Occupied/Tenant
                'owner_occupied_special_rules' => $this->toBool($data['owner_occupied_special']),
                'owner_occupied_restriction_type' => $this->emptyToDefault($data['owner_occupied_restriction_type'], 'none'),
                'tenant_project_lien_allowed' => $this->toBool($data['tenant_project_lien_allowed']),
                'tenant_project_restrictions' => $this->emptyToDefault($data['tenant_project_restrictions'], 'none'),

                // Filing Requirements
                'notarization_required' => $this->toBool($data['notarization_required']),
                'verification_type' => $this->emptyToDefault($data['verification_type'], 'sworn'),
                'efile_allowed' => $this->toBool($data['efile_allowed']),
                'filing_location' => $this->emptyToDefault($data['filing_location'], 'county_recorder'),
                'wrongful_lien_penalty' => $this->emptyToDefault($data['wrongful_lien_penalty'], 'none'),
                'penalty_details' => $this->cleanUtf8($this->emptyToNull($data['penalty_details'])),

                // Lien Anchor Logic
                'lien_anchor_logic' => $this->emptyToDefault($data['lien_anchor_logic'], 'single'),
                'lien_anchor_alt_field' => $this->emptyToNull($data['lien_anchor_alt_field']),

                // Lien Rights by Claimant Type
                'gc_has_lien_rights' => $this->toBool($data['gc_has_lien_rights']),
                'sub_has_lien_rights' => $this->toBool($data['sub_has_lien_rights']),
                'subsub_has_lien_rights' => $this->toBool($data['subsub_has_lien_rights']),
                'supplier_owner_has_lien_rights' => $this->toBool($data['supplier_owner_has_lien_rights']),
                'supplier_gc_has_lien_rights' => $this->toBool($data['supplier_gc_has_lien_rights']),
                'supplier_sub_has_lien_rights' => $this->toBool($data['supplier_sub_has_lien_rights']),

                // References
                'statute_references' => $this->toJsonArray($data['statute_reference']),
                'statute_url' => $this->emptyToNull($data['statute_url']),
                'notes' => $this->cleanUtf8($this->emptyToNull($data['notes'])),

                'data_source' => 'csv_v3',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
        }

        $this->command->info("Seeded lien state rules for {$count} states from CSV.");
    }

    /**
     * Check if any prelim_*_required field is "yes".
     */
    private function anyPrelimRequired(array $data): bool
    {
        $fields = [
            'prelim_gc_required',
            'prelim_sub_required',
            'prelim_subsub_required',
            'prelim_supplier_owner_required',
            'prelim_supplier_gc_required',
            'prelim_supplier_sub_required',
        ];

        foreach ($fields as $field) {
            if (strtolower(trim($data[$field] ?? '')) === 'yes') {
                return true;
            }
        }

        return false;
    }

    /**
     * Derive a human-readable "required for" string from prelim fields.
     */
    private function getPrelimRequiredFor(array $data): string
    {
        $required = [];

        if ($this->toBool($data['prelim_gc_required'] ?? 'no')) {
            $required[] = 'gc';
        }
        if ($this->toBool($data['prelim_sub_required'] ?? 'no')) {
            $required[] = 'subs';
        }
        if ($this->toBool($data['prelim_subsub_required'] ?? 'no')) {
            $required[] = 'subsubs';
        }
        if ($this->toBool($data['prelim_supplier_owner_required'] ?? 'no') ||
            $this->toBool($data['prelim_supplier_gc_required'] ?? 'no') ||
            $this->toBool($data['prelim_supplier_sub_required'] ?? 'no')) {
            $required[] = 'suppliers';
        }

        if (empty($required)) {
            return 'none';
        }

        if (count($required) >= 4 || in_array('gc', $required)) {
            return 'everyone';
        }

        return implode(', ', $required);
    }

    /**
     * Get the maximum NOI lead time from all claimant types.
     */
    private function getMaxNoiLeadTime(array $data): ?int
    {
        $fields = [
            'noi_gc_lead_time_days',
            'noi_sub_lead_time_days',
            'noi_subsub_lead_time_days',
            'noi_supplier_owner_lead_time_days',
            'noi_supplier_gc_lead_time_days',
            'noi_supplier_sub_lead_time_days',
        ];

        $max = null;
        foreach ($fields as $field) {
            $val = $this->toNullableInt($data[$field] ?? '');
            if ($val !== null && ($max === null || $val > $max)) {
                $max = $val;
            }
        }

        return $max;
    }

    private function toBool(string $value): bool
    {
        return strtolower(trim($value)) === 'yes';
    }

    private function toNullableInt(string $value): ?int
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : (int) $trimmed;
    }

    private function toIntOrZero(string $value): int
    {
        $trimmed = trim($value);

        return $trimmed === '' ? 0 : (int) $trimmed;
    }

    private function toNullableDecimal(string $value): ?float
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : (float) $trimmed;
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

    private function toJsonArray(string $value): string
    {
        $cleaned = $this->cleanUtf8(trim($value));

        if ($cleaned === '' || $cleaned === null) {
            return '[]';
        }

        return json_encode([$cleaned], JSON_UNESCAPED_UNICODE) ?: '[]';
    }

    private function cleanUtf8(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        // Already converted to UTF-8 at source, just trim
        return trim($value);
    }
}
