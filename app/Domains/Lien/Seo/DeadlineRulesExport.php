<?php

namespace App\Domains\Lien\Seo;

use App\Domains\Lien\Engine\RuleDateMath;
use App\Support\Seo\States;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The lien deadline rules for one state as a compact JSON structure the free
 * deadline calculator evaluates in the browser
 * (resources/js/lien-deadline-calculator.js). evaluate() is the same
 * algorithm in PHP; tests/Feature/Lien/DeadlineCalculatorContractTest.php
 * checks it against DeadlineCalculator for every state, so the public tool
 * shows exactly what the engine computes. Keep the three in step.
 *
 * Date fields use short keys (first_furnish, not first_furnish_date): the
 * blob is inlined in public pages, which must not leak raw column names.
 */
final class DeadlineRulesExport
{
    /** Bump when the shape changes: cached exports are replaced on the next request. */
    public const CACHE_VERSION = 1;

    /** Rule and project date fields => JSON keys. */
    public const DATE_KEYS = [
        'first_furnish_date' => 'first_furnish',
        'last_furnish_date' => 'last_furnish',
        'completion_date' => 'completion',
        'contract_date' => 'contract',
        'noc_filed_date' => 'noc',
        'noc_recorded_date' => 'noc_recorded',
        'special_fab_delivery_date' => 'special_fab',
        'contract_terminated_date' => 'contract_terminated',
        'lien_recorded_date' => 'lien_recorded',
        'lien_filing_date' => 'lien_filing',
    ];

    /** Claimant type => the LienStateRule lien-rights column, in the order the calculator lists roles. */
    public const LIEN_RIGHTS = [
        'gc' => 'gc_has_lien_rights',
        'subcontractor' => 'sub_has_lien_rights',
        'sub_sub_contractor' => 'subsub_has_lien_rights',
        'supplier_to_owner' => 'supplier_owner_has_lien_rights',
        'supplier_to_contractor' => 'supplier_gc_has_lien_rights',
        'supplier_to_subcontractor' => 'supplier_sub_has_lien_rights',
    ];

    /** Document types the engine checks lien rights for. */
    private const LIEN_DOCUMENTS = ['mechanics_lien', 'lien_enforcement'];

    public static function cacheKey(string $code): string
    {
        return 'seo.lien-deadline-rules.v'.self::CACHE_VERSION.'.'.strtoupper($code);
    }

    public static function cached(string $code): ?array
    {
        return Cache::remember(self::cacheKey($code), now()->addDay(), fn () => self::forState($code));
    }

    /** @return array<string, array> code => export, for every state with rules */
    public static function allCached(): array
    {
        $out = [];
        foreach (array_keys(States::names()) as $code) {
            if ($export = self::cached($code)) {
                $out[$code] = $export;
            }
        }

        return $out;
    }

    public static function forState(string $code): ?array
    {
        $code = strtoupper($code);
        $name = States::name($code);
        $state = $name ? DB::table('lien_state_rules')->where('state', $code)->first() : null;
        if (! $state) {
            return null;
        }

        $rows = DB::table('lien_deadline_rules')
            ->join('lien_document_types', 'lien_document_types.id', '=', 'lien_deadline_rules.document_type_id')
            ->where('lien_deadline_rules.state', $code)
            ->orderBy('lien_deadline_rules.id')
            ->get([
                'lien_document_types.slug as document_type',
                'lien_deadline_rules.claimant_type',
                'lien_deadline_rules.effective_scope',
                'lien_deadline_rules.trigger_event',
                'lien_deadline_rules.calc_method',
                'lien_deadline_rules.offset_days',
                'lien_deadline_rules.offset_months',
                'lien_deadline_rules.day_of_month',
                'lien_deadline_rules.is_required',
                'lien_deadline_rules.conditions_json',
            ]);

        $rules = [];
        foreach ($rows as $row) {
            $conditions = is_string($row->conditions_json) ? json_decode($row->conditions_json, true) : null;
            $display = $conditions['display'] ?? null;
            $rule = [
                'calc_method' => $row->calc_method,
                'trigger_event' => $row->trigger_event,
                'offset_days' => self::int($row->offset_days),
                'offset_months' => self::int($row->offset_months),
                'day_of_month' => self::int($row->day_of_month),
                'conditions' => $conditions,
            ];
            $anchor = RuleDateMath::anchorSpec($rule, $state->lien_anchor_logic);

            $rules[] = array_filter([
                'doc' => $row->document_type,
                'claimant' => $row->claimant_type,
                'scope' => $row->effective_scope,
                'calc_method' => $row->calc_method,
                'trigger_event' => self::key($row->trigger_event),
                'offset_days' => $rule['offset_days'],
                'offset_months' => $rule['offset_months'],
                'day_of_month' => $rule['day_of_month'],
                // The anchor the engine will use: the rule's own, else the state's.
                'anchor' => $anchor['logic'] === 'single' ? null : $anchor['logic'],
                'dates' => $anchor['logic'] === 'single' ? null : array_map(self::key(...), $anchor['fields']),
                'display' => $display,
                'is_required' => (bool) $row->is_required,
                'inputs' => $display ? null : array_map(self::key(...), RuleDateMath::inputs($rule, $state->lien_anchor_logic)),
                'when' => $display ? null : LienStatePage::describe($row),
            ], fn ($value) => $value !== null && $value !== []);
        }

        $rights = [];
        foreach (self::LIEN_RIGHTS as $claimant => $column) {
            $rights[$claimant] = (bool) $state->{$column};
        }

        return [
            'code' => $code,
            'name' => $name,
            'url' => route('liens.state', ['state' => States::slug($name)], absolute: false),
            'attorney_referral' => in_array($code, config('lien.attorney_referral_states', []), true),
            'pre_notice_required_for' => $state->pre_notice_required ? $state->pre_notice_required_for : 'none',
            'noi_lead_time_days' => self::int($state->noi_lead_time_days),
            'noc_shortens_deadline' => (bool) $state->noc_shortens_deadline,
            'lien_after_noc_days' => self::int($state->lien_after_noc_days),
            // A notice of completion filed before the preliminary notice ends lien rights.
            'noc_requires_prior_prelim' => (bool) ($state->noc_requires_prior_prelim && $state->noc_eliminates_rights_if_no_prelim),
            'lien_rights' => $rights,
            'rules' => $rules,
        ];
    }

    /**
     * The browser algorithm in PHP. Given one state's export, a role, a
     * project type and the visitor's dates (short keys, Y-m-d), returns one
     * row per matching rule, in rule order:
     *   status   date | display | missing | no_rights | blocked
     *   date     Y-m-d when status is date
     *   missing  the date keys still needed when status is missing
     *
     * @param  array<string, string|null>  $dates
     * @return array<int, array<string, mixed>>
     */
    public static function evaluate(array $export, string $claimant, string $scope, array $dates, bool $prelimBeforeNoc = false): array
    {
        $rules = array_values(array_filter($export['rules'], fn (array $r) => in_array($r['claimant'], [$claimant, 'any'], true)
            && in_array($r['scope'], [$scope, 'both'], true)));

        $fields = [];
        foreach (self::DATE_KEYS as $field => $key) {
            $fields[$field] = ($dates[$key] ?? null) ?: null;
        }
        $noc = $fields['noc_filed_date'] ? RuleDateMath::resolveAnchor(['trigger_event' => 'noc_filed_date'], null, $fields) : null;

        // The notice of intent counts back from the lien deadline: the first
        // lien rule, shortened by a notice of completion but never blocked.
        $lienRule = collect($rules)->firstWhere('doc', 'mechanics_lien');
        $lienForNoi = null;
        if ($lienRule) {
            $due = RuleDateMath::dueDate(self::mathRule($lienRule), null, $fields);
            $lienForNoi = $due;
            if ($due['date']) {
                $nocResult = self::nocLogic($export, $noc, $due['date'], $prelimBeforeNoc);
                $lienForNoi['date'] = $nocResult['shortened'] ? $nocResult['deadline'] : $due['date'];
            }
        }

        $out = [];
        foreach ($rules as $rule) {
            $row = [
                'doc' => $rule['doc'],
                'claimant' => $rule['claimant'],
                'scope' => $rule['scope'],
                'required' => $rule['is_required'],
                'status' => 'date',
                'date' => null,
                'missing' => [],
            ];

            if (in_array($rule['doc'], self::LIEN_DOCUMENTS, true) && ! ($export['lien_rights'][$claimant] ?? true)) {
                $out[] = ['status' => 'no_rights'] + $row;

                continue;
            }

            if ($rule['doc'] === 'noi') {
                if (! $lienForNoi || ! $lienForNoi['date']) {
                    $missing = $lienForNoi ? array_map(self::key(...), $lienForNoi['missing']) : ['last_furnish'];
                    $out[] = ['status' => 'missing', 'missing' => $missing] + $row;

                    continue;
                }
                $noi = RuleDateMath::noiDueDate($lienForNoi['date'], $export['noi_lead_time_days'] ?? null);
                $out[] = ['date' => $noi['date']->toDateString(), 'lead_time_days' => $noi['lead_time_days'], 'lien_date' => $lienForNoi['date']->toDateString()] + $row;

                continue;
            }

            if (isset($rule['display'])) {
                $out[] = ['status' => 'display', 'display' => $rule['display']] + $row;

                continue;
            }

            $due = RuleDateMath::dueDate(self::mathRule($rule), null, $fields);
            if (! $due['date']) {
                $out[] = ['status' => 'missing', 'missing' => array_map(self::key(...), $due['missing'])] + $row;

                continue;
            }

            $date = $due['date'];
            if ($rule['doc'] === 'mechanics_lien') {
                $nocResult = self::nocLogic($export, $noc, $date, $prelimBeforeNoc);
                if ($nocResult['blocked']) {
                    $out[] = ['status' => 'blocked'] + $row;

                    continue;
                }
                if ($nocResult['shortened']) {
                    $row['noc_shortened'] = true;
                    $row['original_date'] = $date->toDateString();
                    $date = $nocResult['deadline'];
                }
            }

            $out[] = ['date' => $date->toDateString(), 'anchor' => $due['anchor']->toDateString()] + $row;
        }

        return $out;
    }

    /**
     * Notice of completion: in some states a notice filed before the
     * preliminary notice ends lien rights; in others the lien is due a set
     * number of days after the notice when that comes sooner.
     *
     * @return array{blocked: bool, shortened: bool, deadline: ?CarbonInterface}
     */
    private static function nocLogic(array $export, ?CarbonInterface $noc, CarbonInterface $base, bool $prelimBeforeNoc): array
    {
        if (! $noc) {
            return ['blocked' => false, 'shortened' => false, 'deadline' => $base];
        }

        if (($export['noc_requires_prior_prelim'] ?? false) && ! $prelimBeforeNoc) {
            return ['blocked' => true, 'shortened' => false, 'deadline' => null];
        }

        $days = $export['lien_after_noc_days'] ?? null;
        if (($export['noc_shortens_deadline'] ?? false) && $days) {
            $nocDeadline = $noc->copy()->addDays($days);
            if ($nocDeadline->lt($base)) {
                return ['blocked' => false, 'shortened' => true, 'deadline' => $nocDeadline];
            }
        }

        return ['blocked' => false, 'shortened' => false, 'deadline' => $base];
    }

    /** An export rule back in RuleDateMath's shape (long field names, anchor already resolved). */
    private static function mathRule(array $rule): array
    {
        $field = array_flip(self::DATE_KEYS);

        return [
            'calc_method' => $rule['calc_method'],
            'trigger_event' => $field[$rule['trigger_event']],
            'offset_days' => $rule['offset_days'] ?? null,
            'offset_months' => $rule['offset_months'] ?? null,
            'day_of_month' => $rule['day_of_month'] ?? null,
            'conditions' => isset($rule['anchor'])
                ? ['anchor' => $rule['anchor'], 'dates' => array_map(fn (string $key) => $field[$key], $rule['dates'] ?? [])]
                : null,
        ];
    }

    private static function key(string $field): string
    {
        return self::DATE_KEYS[$field] ?? $field;
    }

    private static function int(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}
