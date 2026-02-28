<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSeedJson extends Command
{
    protected $signature = 'seed:generate-json';

    protected $description = 'Pre-process CSV lien rules into JSON for faster seeding';

    private const CLAIMANT_TYPES = [
        'gc' => 'gc',
        'sub' => 'subcontractor',
        'subsub' => 'sub_sub_contractor',
        'supplier_owner' => 'supplier_to_owner',
        'supplier_gc' => 'supplier_to_contractor',
        'supplier_sub' => 'supplier_to_subcontractor',
    ];

    public function handle(): int
    {
        $csvPath = database_path('seeders/data/lien_rules_by_state_v3_checked_csv.csv');

        if (! file_exists($csvPath)) {
            $this->error("CSV file not found: {$csvPath}");

            return 1;
        }

        $csvContent = file_get_contents($csvPath);
        $csvContent = mb_convert_encoding($csvContent, 'UTF-8', 'Windows-1252');

        $lines = explode("\n", $csvContent);
        $headers = str_getcsv(array_shift($lines));

        $stateRules = [];
        $deadlineRules = [];

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

            $stateRules[] = $this->buildStateRule($data);

            array_push($deadlineRules, ...$this->buildPreliminaryNoticeRules($data, $state));
            array_push($deadlineRules, ...$this->buildNoiRules($data, $state));
            array_push($deadlineRules, ...$this->buildMechanicsLienRules($data, $state));
        }

        $outputDir = database_path('seeders/data');

        file_put_contents(
            $outputDir.'/lien_state_rules.json',
            json_encode($stateRules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        file_put_contents(
            $outputDir.'/lien_deadline_rules.json',
            json_encode($deadlineRules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->info('Generated '.count($stateRules).' state rules → lien_state_rules.json');
        $this->info('Generated '.count($deadlineRules).' deadline rules → lien_deadline_rules.json');

        return 0;
    }

    private function buildStateRule(array $data): array
    {
        return [
            'state' => $data['state_code'],
            'pre_notice_required' => $this->anyPrelimRequired($data),
            'pre_notice_required_for' => $this->getPrelimRequiredFor($data),
            'noi_lead_time_days' => $this->getMaxNoiLeadTime($data),
            'prelim_delivery_method' => $this->emptyToDefault($data['prelim_delivery_method'], 'any'),
            'prelim_recipients' => $this->emptyToDefault($data['prelim_recipients'], 'owner'),
            'noc_shortens_deadline' => $this->toBool($data['noc_shortens_deadline']),
            'lien_after_noc_days' => $this->toNullableInt($data['lien_after_noc_days']),
            'noc_requires_prior_prelim' => $this->toBool($data['noc_requires_prior_prelim']),
            'noc_eliminates_rights_if_no_prelim' => $this->toBool($data['noc_eliminates_rights_if_no_prelim']),
            'post_lien_notice_required' => $this->toBool($data['post_lien_notice_required']),
            'post_lien_notice_days' => $this->toNullableInt($data['post_lien_notice_days']),
            'post_lien_notice_recipients' => $this->emptyToNull($data['post_lien_notice_recipients']),
            'enforcement_calc_method' => $this->emptyToDefault($data['enforcement_calc_method'], 'months_after_date'),
            'enforcement_deadline_days' => $this->toIntOrZero($data['enforcement_days']),
            'enforcement_deadline_months' => $this->toNullableDecimal($data['enforcement_months']),
            'enforcement_deadline_trigger' => $this->emptyToDefault($data['enforcement_trigger'], 'lien_recorded_date'),
            'owner_occupied_special_rules' => $this->toBool($data['owner_occupied_special']),
            'owner_occupied_restriction_type' => $this->emptyToDefault($data['owner_occupied_restriction_type'], 'none'),
            'tenant_project_lien_allowed' => $this->toBool($data['tenant_project_lien_allowed']),
            'tenant_project_restrictions' => $this->emptyToDefault($data['tenant_project_restrictions'], 'none'),
            'notarization_required' => $this->toBool($data['notarization_required']),
            'verification_type' => $this->emptyToDefault($data['verification_type'], 'sworn'),
            'efile_allowed' => $this->toBool($data['efile_allowed']),
            'filing_location' => $this->emptyToDefault($data['filing_location'], 'county_recorder'),
            'wrongful_lien_penalty' => $this->emptyToDefault($data['wrongful_lien_penalty'], 'none'),
            'penalty_details' => $this->cleanUtf8($this->emptyToNull($data['penalty_details'])),
            'lien_anchor_logic' => $this->emptyToDefault($data['lien_anchor_logic'], 'single'),
            'lien_anchor_alt_field' => $this->emptyToNull($data['lien_anchor_alt_field']),
            'gc_has_lien_rights' => $this->toBool($data['gc_has_lien_rights']),
            'sub_has_lien_rights' => $this->toBool($data['sub_has_lien_rights']),
            'subsub_has_lien_rights' => $this->toBool($data['subsub_has_lien_rights']),
            'supplier_owner_has_lien_rights' => $this->toBool($data['supplier_owner_has_lien_rights']),
            'supplier_gc_has_lien_rights' => $this->toBool($data['supplier_gc_has_lien_rights']),
            'supplier_sub_has_lien_rights' => $this->toBool($data['supplier_sub_has_lien_rights']),
            'statute_references' => $this->toJsonArray($data['statute_reference']),
            'statute_url' => $this->emptyToNull($data['statute_url']),
            'notes' => $this->cleanUtf8($this->emptyToNull($data['notes'])),
            'data_source' => 'csv_v3',
        ];
    }

    private function buildPreliminaryNoticeRules(array $data, string $state): array
    {
        $rules = [];

        foreach (self::CLAIMANT_TYPES as $csvPrefix => $dbValue) {
            $requiredField = "prelim_{$csvPrefix}_required";
            $daysField = "prelim_{$csvPrefix}_deadline_days";
            $triggerField = "prelim_{$csvPrefix}_trigger";

            if (strtolower(trim($data[$requiredField] ?? '')) !== 'yes') {
                continue;
            }

            $rules[] = [
                'state' => $state,
                'document_type_slug' => 'prelim_notice',
                'claimant_type' => $dbValue,
                'trigger_event' => $this->emptyToDefault($data[$triggerField] ?? '', 'first_furnish_date'),
                'calc_method' => 'days_after_date',
                'offset_days' => $this->toNullableInt($data[$daysField] ?? ''),
                'offset_months' => null,
                'day_of_month' => null,
                'is_required' => true,
                'effective_scope' => 'both',
                'is_placeholder' => false,
                'conditions_json' => null,
                'notes' => null,
                'data_source' => 'csv_v3',
            ];
        }

        return $rules;
    }

    private function buildNoiRules(array $data, string $state): array
    {
        $rules = [];

        foreach (self::CLAIMANT_TYPES as $csvPrefix => $dbValue) {
            $requiredField = "noi_{$csvPrefix}_required";
            $daysField = "noi_{$csvPrefix}_lead_time_days";

            if (strtolower(trim($data[$requiredField] ?? '')) !== 'yes') {
                continue;
            }

            $leadTimeDays = $this->toNullableInt($data[$daysField] ?? '');

            $rules[] = [
                'state' => $state,
                'document_type_slug' => 'noi',
                'claimant_type' => $dbValue,
                'trigger_event' => 'lien_filing_date',
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
            ];
        }

        return $rules;
    }

    private function buildMechanicsLienRules(array $data, string $state): array
    {
        $rules = [];
        $scopes = ['res' => 'residential', 'com' => 'commercial'];

        foreach (self::CLAIMANT_TYPES as $csvPrefix => $dbValue) {
            foreach ($scopes as $scopeKey => $scopeValue) {
                $triggerField = "lien_{$csvPrefix}_{$scopeKey}_trigger";
                $calcMethodField = "lien_{$csvPrefix}_{$scopeKey}_calc_method";
                $offsetDaysField = "lien_{$csvPrefix}_{$scopeKey}_offset_days";
                $offsetMonthsField = "lien_{$csvPrefix}_{$scopeKey}_offset_months";
                $dayOfMonthField = "lien_{$csvPrefix}_{$scopeKey}_day_of_month";

                $trigger = $this->emptyToNull($data[$triggerField] ?? '');
                if ($trigger === null) {
                    continue;
                }

                $conditions = null;
                if ($data['lien_anchor_logic'] === 'later_of' && ! empty($data['lien_anchor_alt_field'])) {
                    $conditions = json_encode([
                        'anchor' => 'later_of',
                        'dates' => [$trigger, $data['lien_anchor_alt_field']],
                    ]);
                }

                $rules[] = [
                    'state' => $state,
                    'document_type_slug' => 'mechanics_lien',
                    'claimant_type' => $dbValue,
                    'trigger_event' => $trigger,
                    'calc_method' => $this->emptyToDefault($data[$calcMethodField] ?? '', 'days_after_date'),
                    'offset_days' => $this->toNullableInt($data[$offsetDaysField] ?? ''),
                    'offset_months' => $this->toNullableInt($data[$offsetMonthsField] ?? ''),
                    'day_of_month' => $this->toNullableInt($data[$dayOfMonthField] ?? ''),
                    'is_required' => true,
                    'effective_scope' => $scopeValue,
                    'is_placeholder' => false,
                    'conditions_json' => $conditions,
                    'notes' => null,
                    'data_source' => 'csv_v3',
                ];
            }
        }

        return $rules;
    }

    private function anyPrelimRequired(array $data): bool
    {
        $fields = [
            'prelim_gc_required', 'prelim_sub_required', 'prelim_subsub_required',
            'prelim_supplier_owner_required', 'prelim_supplier_gc_required', 'prelim_supplier_sub_required',
        ];

        foreach ($fields as $field) {
            if (strtolower(trim($data[$field] ?? '')) === 'yes') {
                return true;
            }
        }

        return false;
    }

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

    private function getMaxNoiLeadTime(array $data): ?int
    {
        $fields = [
            'noi_gc_lead_time_days', 'noi_sub_lead_time_days', 'noi_subsub_lead_time_days',
            'noi_supplier_owner_lead_time_days', 'noi_supplier_gc_lead_time_days', 'noi_supplier_sub_lead_time_days',
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

        return trim($value);
    }
}
