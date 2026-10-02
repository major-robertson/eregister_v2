<?php

namespace App\Domains\Lien\Engine;

use App\Domains\Lien\Enums\CalcMethod;
use App\Domains\Lien\Models\LienDeadlineRule;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Closure;

/**
 * Pure date math for one lien deadline rule: which project dates it reads,
 * how it picks its anchor, and the due date it produces. DeadlineCalculator
 * runs every rule through here, and the public deadline calculator
 * (resources/js/lien-deadline-calculator.js) is a line-by-line port of it, so
 * the free tool and the paid product cannot compute different dates.
 *
 * A rule is a plain array so the engine (model rows) and the JSON export
 * (DeadlineRulesExport) can share it:
 *   calc_method (string|CalcMethod|null), trigger_event (string),
 *   offset_days, offset_months, day_of_month (int|null),
 *   conditions (array|null: conditions_json, e.g. anchor: later_of + dates).
 *
 * Dates are either an array keyed by field name (Y-m-d strings or Carbon
 * instances) or a closure that resolves one field, which is how the engine
 * passes its project lookups.
 */
final class RuleDateMath
{
    /** The project dates a rule can be anchored on. */
    public const DATE_FIELDS = [
        'first_furnish_date',
        'last_furnish_date',
        'completion_date',
        'contract_date',
        'lien_recorded_date',
        'noc_filed_date',
        'special_fab_delivery_date',
    ];

    /** Rules with calc_method days_before_date count back from this computed date. */
    public const LIEN_DEADLINE = 'lien_filing_deadline';

    /** @return array{calc_method: ?CalcMethod, trigger_event: string, offset_days: ?int, offset_months: ?int, day_of_month: ?int, conditions: ?array} */
    public static function fromModel(LienDeadlineRule $rule): array
    {
        return [
            'calc_method' => $rule->calc_method,
            'trigger_event' => $rule->trigger_event->value,
            'offset_days' => $rule->offset_days,
            'offset_months' => $rule->offset_months,
            'day_of_month' => $rule->day_of_month,
            'conditions' => $rule->conditions_json,
        ];
    }

    /**
     * How a rule finds its anchor date: the rule's own conditions_json anchor
     * wins, then the state's lien_anchor_logic, then the trigger event alone.
     * A later_of/earlier_of anchor reads only conditions_json.dates; with no
     * dates listed it can never resolve (the engine's behaviour, kept as is).
     *
     * @return array{logic: string, fields: array<int, string>}
     */
    public static function anchorSpec(array $rule, ?string $stateAnchorLogic = null): array
    {
        $conditions = (array) ($rule['conditions'] ?? []);
        $logic = $conditions['anchor'] ?? $stateAnchorLogic ?? 'single';

        if ($logic === 'later_of' || $logic === 'earlier_of') {
            return ['logic' => $logic, 'fields' => array_values($conditions['dates'] ?? [])];
        }

        return ['logic' => 'single', 'fields' => [$rule['trigger_event']]];
    }

    /**
     * The project dates the rule reads. Rules that count back from the lien
     * filing deadline read none: their anchor is computed from the lien rule.
     *
     * @return array<int, string>
     */
    public static function inputs(array $rule, ?string $stateAnchorLogic = null): array
    {
        if (self::method($rule) === CalcMethod::DaysBeforeDate) {
            return [];
        }

        return self::anchorSpec($rule, $stateAnchorLogic)['fields'];
    }

    public static function resolveAnchor(array $rule, ?string $stateAnchorLogic, array|Closure $dates): ?CarbonInterface
    {
        $lookup = self::lookup($dates);
        $spec = self::anchorSpec($rule, $stateAnchorLogic);

        if ($spec['logic'] === 'single') {
            return $lookup($spec['fields'][0]);
        }

        $found = array_values(array_filter(array_map($lookup, $spec['fields'])));
        if (! $found) {
            return null;
        }

        $later = $spec['logic'] === 'later_of';

        return array_reduce($found, fn (?CarbonInterface $carry, CarbonInterface $date) => $carry === null
            || ($later ? $date->greaterThan($carry) : $date->lessThan($carry)) ? $date : $carry);
    }

    /**
     * The due date for one rule, or null with the reason:
     *   missing_anchor          a date the rule reads is not known (see missing)
     *   missing_lien_deadline   a days_before_date rule with no lien deadline
     *
     * @return array{date: ?CarbonInterface, anchor: ?CarbonInterface, reason: ?string, missing: array<int, string>}
     */
    public static function dueDate(array $rule, ?string $stateAnchorLogic, array|Closure $dates): array
    {
        $method = self::method($rule);

        if ($method === CalcMethod::DaysBeforeDate) {
            $anchor = self::lookup($dates)(self::LIEN_DEADLINE);
            if (! $anchor) {
                return ['date' => null, 'anchor' => null, 'reason' => 'missing_lien_deadline', 'missing' => [self::LIEN_DEADLINE]];
            }
        } else {
            $anchor = self::resolveAnchor($rule, $stateAnchorLogic, $dates);
            if (! $anchor) {
                return ['date' => null, 'anchor' => null, 'reason' => 'missing_anchor', 'missing' => [$rule['trigger_event']]];
            }
        }

        return [
            'date' => self::apply($anchor, $method, $rule['offset_days'] ?? null, $rule['offset_months'] ?? null, $rule['day_of_month'] ?? null),
            'anchor' => $anchor,
            'reason' => null,
            'missing' => [],
        ];
    }

    /** The calendar arithmetic behind each calc_method. A null method counts days, as it always has. */
    public static function apply(CarbonInterface $anchor, ?CalcMethod $method, ?int $offsetDays, ?int $offsetMonths, ?int $dayOfMonth): CarbonInterface
    {
        return match ($method ?? CalcMethod::DaysAfterDate) {
            CalcMethod::DaysAfterDate => $anchor->copy()->addDays($offsetDays ?? 0),

            // No overflow: Jan 31 + 1 month is Feb 28/29, never Mar 2/3.
            CalcMethod::MonthsAfterDate => $anchor->copy()->addMonthsNoOverflow($offsetMonths ?? 0),

            // "15th of the Nth month after the month of X": from the first of
            // the anchor month, so the anchor's day never matters.
            CalcMethod::MonthDayAfterMonthOfDate => $anchor->copy()
                ->startOfMonth()
                ->addMonthsNoOverflow($offsetMonths ?? 0)
                ->setDay($dayOfMonth ?? 1),

            // VA: "90 days from the end of the month of last work".
            CalcMethod::DaysAfterEndOfMonthOfDate => $anchor->copy()->endOfMonth()->addDays($offsetDays ?? 0),

            // NOI: "N days before the lien filing deadline". A negative lead
            // would land after the lien deadline, so it counts as zero.
            CalcMethod::DaysBeforeDate => $anchor->copy()->subDays(max(0, $offsetDays ?? 0)),
        };
    }

    /**
     * Notice of intent due date: the state's lead time before the lien
     * deadline. The engine derives every NOI from the state's
     * noi_lead_time_days (each NOI row's offset_days carries the same number).
     *
     * @return array{date: CarbonInterface, lead_time_days: int}
     */
    public static function noiDueDate(CarbonInterface $lienDueDate, ?int $leadTimeDays): array
    {
        $lead = max(0, $leadTimeDays ?? 0);

        return [
            'date' => self::apply($lienDueDate, CalcMethod::DaysBeforeDate, $lead, null, null),
            'lead_time_days' => $lead,
        ];
    }

    private static function method(array $rule): ?CalcMethod
    {
        $method = $rule['calc_method'] ?? null;

        return $method instanceof CalcMethod || $method === null ? $method : CalcMethod::from($method);
    }

    /** @return Closure(string): ?CarbonInterface */
    private static function lookup(array|Closure $dates): Closure
    {
        if ($dates instanceof Closure) {
            return $dates;
        }

        return function (string $field) use ($dates): ?CarbonInterface {
            $value = $dates[$field] ?? null;

            // The engine has no contract date on a project; it reads the
            // first furnishing date instead.
            if ($value === null && $field === 'contract_date') {
                $value = $dates['first_furnish_date'] ?? null;
            }

            if ($value === null || $value === '') {
                return null;
            }

            return $value instanceof CarbonInterface ? $value : CarbonImmutable::parse($value)->startOfDay();
        };
    }
}
