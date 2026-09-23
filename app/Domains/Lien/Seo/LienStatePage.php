<?php

namespace App\Domains\Lien\Seo;

use App\Domains\Lien\Models\LienStateRule;
use App\Support\Seo\States;
use App\Support\Seo\Text;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Plain-English view model for the public "/liens/{state}" pages. Everything
 * is derived from LienStateRule and the deadline rules so the marketing copy
 * can never drift from what the lien engine actually enforces. Rules are
 * static reference data, so the page can be cached for a day.
 */
final class LienStatePage
{
    public readonly string $name;

    public readonly string $slug;

    /** @var array<int, string> */
    public readonly array $statutes;

    public readonly ?string $notes;

    /** @var array<string, array<int, array{who: string, when: string, scope: string}>> keyed by document type slug */
    public readonly array $deadlines;

    private function __construct(
        public readonly string $code,
        public readonly LienStateRule $rule,
    ) {
        $this->name = States::name($code);
        $this->slug = States::slug($this->name);
        $this->statutes = self::decodeStatutes($rule->getRawOriginal('statute_references'));
        $this->notes = Text::fixMojibake($rule->notes);
        $this->deadlines = $this->buildDeadlines();
    }

    public static function forCode(string $code): ?self
    {
        $code = strtoupper($code);
        if (States::name($code) === null) {
            return null;
        }

        $rule = LienStateRule::find($code);

        return $rule ? new self($code, $rule) : null;
    }

    public function url(): string
    {
        return route('liens.state', ['state' => $this->slug]);
    }

    public function title(): string
    {
        return "{$this->name} Mechanics Lien Deadlines & Filing Requirements";
    }

    public function metaDescription(): string
    {
        $r = $this->rule;
        $prelim = ! $r->pre_notice_required ? 'no preliminary notice' : match ($r->pre_notice_required_for) {
            'everyone' => 'preliminary notice for everyone',
            'subsubs, suppliers' => 'preliminary notice for sub-subs and suppliers',
            default => 'preliminary notice for subs and suppliers',
        };
        $lien = $this->headlineLienDeadline(short: true);
        $enforce = $this->enforcementSentence();

        $parts = array_filter([
            $lien ? "lien deadline {$lien}" : null,
            $r->notarization_required ? 'notarized' : 'no notary',
            $enforce ? "enforce {$enforce}" : null,
            $r->efile_allowed ? 'e-recording available' : null,
        ]);

        $text = "{$this->name} mechanics lien rules: {$prelim}";
        foreach ($parts as $part) {
            if (mb_strlen("{$text}, {$part}.") > 160) {
                continue;
            }
            $text .= ", {$part}";
        }

        return $text.'.';
    }

    /* ---------------------------------------------------------------- facts */

    /** @return array<int, array{label: string, value: string, detail?: string}> */
    public function keyFacts(): array
    {
        $r = $this->rule;
        $facts = [
            ['label' => 'Preliminary notice', 'value' => $this->prelimSummary()],
            ['label' => 'Lien filing deadline', 'value' => $this->headlineLienDeadline() ?? 'See the deadline table below'],
            ['label' => 'Notarization', 'value' => $r->notarization_required ? 'Required' : 'Not required', 'detail' => $this->verificationLabel()],
            ['label' => 'Where to record', 'value' => $this->filingLocationLabel()],
            ['label' => 'E-recording', 'value' => $r->efile_allowed ? 'Available in participating counties' : 'Not generally available; plan on paper recording'],
            ['label' => 'Enforcement deadline', 'value' => $this->enforcementSentence() ? ucfirst($this->enforcementSentence()) : 'See statute'],
        ];

        return $facts;
    }

    /** @return array<int, array{role: string, rights: bool}> */
    public function lienRights(): array
    {
        $r = $this->rule;

        return [
            ['role' => 'General contractor (direct contract with the owner)', 'rights' => (bool) $r->gc_has_lien_rights],
            ['role' => 'Subcontractor (hired by the general contractor)', 'rights' => (bool) $r->sub_has_lien_rights],
            ['role' => 'Sub-subcontractor (hired by a subcontractor)', 'rights' => (bool) $r->subsub_has_lien_rights],
            ['role' => 'Material supplier to the owner', 'rights' => (bool) $r->supplier_owner_has_lien_rights],
            ['role' => 'Material supplier to the general contractor', 'rights' => (bool) $r->supplier_gc_has_lien_rights],
            ['role' => 'Material supplier to a subcontractor', 'rights' => (bool) $r->supplier_sub_has_lien_rights],
        ];
    }

    public function prelimSummary(): string
    {
        $r = $this->rule;
        if (! $r->pre_notice_required) {
            return 'Not required to preserve lien rights';
        }

        return match ($r->pre_notice_required_for) {
            'everyone' => 'Required for every claimant, including the general contractor',
            'subs, subsubs, suppliers' => 'Required for subcontractors, sub-subcontractors, and suppliers',
            'subsubs, suppliers' => 'Required for sub-subcontractors and suppliers',
            default => 'Required for some claimants',
        };
    }

    public function prelimDeliveryLabel(): ?string
    {
        return match ($this->rule->prelim_delivery_method) {
            'certified_mail' => 'certified mail',
            'registered_mail' => 'registered mail',
            'personal' => 'personal delivery',
            'any' => 'any method that proves receipt',
            default => null,
        };
    }

    public function prelimRecipientsLabel(): ?string
    {
        return match ($this->rule->prelim_recipients) {
            'owner' => 'the property owner',
            'owner_gc' => 'the property owner and the general contractor',
            'owner_gc_lender' => 'the property owner, the general contractor, and the construction lender',
            'owner_lender' => 'the property owner and the construction lender',
            'parish_recorder' => 'the parish recorder',
            'owner_county_recorder' => 'the property owner, with a copy recorded with the county',
            'lien_agent' => 'the project lien agent',
            'state_registry' => 'the state construction registry',
            default => null,
        };
    }

    public function postLienNoticeSentence(): ?string
    {
        $r = $this->rule;
        if (! $r->post_lien_notice_required) {
            return null;
        }

        $to = match ($r->post_lien_notice_recipients) {
            'owner' => 'the property owner',
            'owner_gc' => 'the property owner and the general contractor',
            'owner_gc_lender' => 'the property owner, the general contractor, and the lender',
            default => 'the property owner',
        };

        $within = $r->post_lien_notice_days ? "within {$r->post_lien_notice_days} days of recording" : 'promptly after recording';

        return "A copy of the recorded lien must be served on {$to} {$within}.";
    }

    public function noiSentence(): ?string
    {
        $days = $this->rule->noi_lead_time_days;
        if ($days === null) {
            return null;
        }

        $to = "{$this->name} requires a notice of intent to lien before the lien itself is recorded";

        return $days > 0 ? "{$to}, served at least {$days} days ahead of filing." : "{$to}.";
    }

    public function enforcementSentence(): ?string
    {
        $r = $this->rule;
        $trigger = match ($r->enforcement_deadline_trigger) {
            'lien_recorded_date' => 'the lien is recorded',
            'last_furnish_date' => 'last furnishing labor or materials',
            'completion_date' => 'project completion',
            'contract_date' => 'the contract date',
            default => null,
        };

        if ($r->enforcement_calc_method === 'months_after_date' && $r->enforcement_deadline_months) {
            $months = (float) $r->enforcement_deadline_months;
            $amount = $months >= 12 && fmod($months, 12) == 0
                ? (($months / 12) == 1 ? '1 year' : ($months / 12).' years')
                : rtrim(rtrim(number_format($months, 3, '.', ''), '0'), '.').' months';

            return $trigger ? "within {$amount} after {$trigger}" : "within {$amount}";
        }

        if ($r->enforcement_deadline_days) {
            return $trigger ? "within {$r->enforcement_deadline_days} days after {$trigger}" : "within {$r->enforcement_deadline_days} days";
        }

        return null;
    }

    public function verificationLabel(): ?string
    {
        return match ($this->rule->verification_type) {
            'sworn' => 'The lien claim must be sworn to under oath',
            'verified' => 'The lien claim must be verified by the claimant',
            'notarized' => 'The claimant\'s signature must be notarized',
            'none' => 'No sworn statement or verification is required',
            default => null,
        };
    }

    public function filingLocationLabel(): string
    {
        return match ($this->rule->filing_location) {
            'county_recorder' => 'County recorder where the property sits',
            'circuit_clerk' => 'Circuit court clerk for the county',
            'register_of_deeds' => 'Register of deeds for the county',
            'town_clerk' => 'Town or city clerk',
            default => (string) $this->rule->filing_location,
        };
    }

    public function ownerOccupiedSentence(): ?string
    {
        $r = $this->rule;
        if (! $r->owner_occupied_special_rules) {
            return null;
        }

        return match ($r->owner_occupied_restriction_type) {
            'enhanced_notice' => "Owner-occupied residential projects carry extra notice requirements in {$this->name}; missing them can cost the lien entirely.",
            'direct_contract_only' => "On owner-occupied homes in {$this->name}, only parties with a direct contract with the owner can lien.",
            'no_sub_liens' => "{$this->name} does not allow subcontractor liens on owner-occupied residences.",
            'multiple' => "{$this->name} layers several restrictions on owner-occupied residential projects; read the statute before relying on a lien.",
            default => "{$this->name} treats owner-occupied residential projects differently from commercial work.",
        };
    }

    public function tenantSentence(): string
    {
        $r = $this->rule;
        if (! $r->tenant_project_lien_allowed) {
            return "Work ordered by a tenant generally cannot support a lien against the landlord's fee interest in {$this->name}.";
        }

        return $r->tenant_project_restrictions === 'owner_consent_required'
            ? "Tenant-ordered work can reach the owner's interest in {$this->name} only where the owner consented to or required the improvements."
            : "Tenant-ordered improvements can support a lien in {$this->name}.";
    }

    public function penaltySentence(): ?string
    {
        $r = $this->rule;
        $lead = match ($r->wrongful_lien_penalty) {
            'damages' => "{$this->name} exposes claimants who file an exaggerated or baseless lien to damages.",
            'treble_damages' => "{$this->name} allows treble damages against claimants who file a wrongful lien.",
            'fees' => "In {$this->name}, a wrongful lien claimant can be ordered to pay the owner's attorney fees and costs.",
            'criminal' => "Filing a knowingly false lien in {$this->name} can carry criminal penalties as well as civil liability.",
            default => null,
        };

        $details = Text::fixMojibake($r->penalty_details);

        return trim(implode(' ', array_filter([$lead, $details ? rtrim($details, '.').'.' : null]))) ?: null;
    }

    public function headlineLienDeadline(bool $short = false): ?string
    {
        $rows = $this->deadlines['mechanics_lien'] ?? [];
        if (! $rows) {
            return null;
        }

        $whens = array_values(array_unique(array_column($rows, 'when')));
        $first = $short ? preg_replace('/\s*\(or .*?\)/', '', $whens[0]) : $whens[0];
        $variance = $this->lienDeadlineVariance();

        return $variance ? "{$first} (varies by {$variance})" : $first;
    }

    /** What the mechanics lien deadline depends on: "project type", "role", or null when uniform. */
    public function lienDeadlineVariance(): ?string
    {
        $rows = $this->deadlines['mechanics_lien'] ?? [];
        if (count(array_unique(array_column($rows, 'when'))) <= 1) {
            return null;
        }

        return count(array_unique(array_column($rows, 'who'))) === 1 ? 'project type' : 'role';
    }

    /* ------------------------------------------------------------- pricing */

    public function selfServePrice(): int
    {
        return (int) round(config('lien.pricing.mechanics_lien.self_serve', 9900) / 100);
    }

    public function fullServicePrice(): int
    {
        $override = config("lien.state_pricing.{$this->code}.mechanics_lien.full_service");

        return (int) round(($override ?? config('lien.pricing.mechanics_lien.full_service', 29900)) / 100);
    }

    /* ----------------------------------------------------------------- faq */

    /** @return array<int, array{q: string, a: string}> */
    public function faq(): array
    {
        $r = $this->rule;
        $items = [];

        $lien = $this->headlineLienDeadline();
        if ($lien) {
            $variance = match ($this->lienDeadlineVariance()) {
                'project type' => 'That is the residential deadline; commercial projects follow the schedule in the table above.',
                'role' => 'The deadline differs by claimant role and project type, so check the table above for your situation.',
                default => 'The same deadline applies to every claimant.',
            };
            $items[] = [
                'q' => "How long do I have to file a mechanics lien in {$this->name}?",
                'a' => "The {$this->name} lien must be recorded ".preg_replace('/\s*\(varies by .*?\)/', '', $lien).". {$variance}",
            ];
        }

        $items[] = [
            'q' => "Do I need to send a preliminary notice in {$this->name}?",
            'a' => $r->pre_notice_required
                ? $this->prelimSummary().'. '.($this->prelimRecipientsLabel() ? 'Serve it on '.$this->prelimRecipientsLabel().($this->prelimDeliveryLabel() ? ' by '.$this->prelimDeliveryLabel() : '').'.' : '').' Missing it usually forfeits lien rights for the unnoticed work.'
                : "No. {$this->name} does not require a preliminary notice to preserve lien rights, although sending one is still an effective way to get paid before a lien becomes necessary.",
        ];

        $items[] = [
            'q' => "Does a {$this->name} mechanics lien need to be notarized?",
            'a' => ($r->notarization_required
                ? 'Yes. The lien must be notarized before it is recorded.'
                : "No. {$this->name} does not require a notary on the lien itself.")
                .($this->verificationLabel() ? ' '.$this->verificationLabel().'.' : ''),
        ];

        $items[] = [
            'q' => "Where is a mechanics lien filed in {$this->name}?",
            'a' => 'With the '.lcfirst($this->filingLocationLabel()).'. '.($r->efile_allowed
                ? 'Electronic recording is available in participating counties, which typically cuts the turnaround to a day or two.'
                : 'Paper filing is the norm, so allow time for mailing and the recorder\'s processing queue when working back from your deadline.'),
        ];

        if ($this->enforcementSentence()) {
            $items[] = [
                'q' => "How long is a {$this->name} mechanics lien valid?",
                'a' => "A {$this->name} lien must be enforced through a foreclosure lawsuit {$this->enforcementSentence()}. If no suit is filed by then, the lien expires and can no longer be used to force payment.",
            ];
        }

        if ($this->noiSentence()) {
            $items[] = [
                'q' => "Is a notice of intent to lien required in {$this->name}?",
                'a' => $this->noiSentence(),
            ];
        }

        $items[] = [
            'q' => "Can a subcontractor file a mechanics lien in {$this->name}?",
            'a' => $r->sub_has_lien_rights
                ? "Yes. Subcontractors have lien rights in {$this->name}".($r->subsub_has_lien_rights ? ', and so do sub-subcontractors' : ', although sub-subcontractors do not').'.'.($r->pre_notice_required && $r->pre_notice_required_for !== 'none' ? ' They must follow the preliminary notice rules to keep those rights.' : '')
                : "Subcontractors generally do not have direct lien rights in {$this->name}; payment protection runs through other remedies.",
        ];

        return $items;
    }

    /* ------------------------------------------------------------ deadlines */

    public function hasRoleVariance(string $documentType): bool
    {
        $rows = $this->deadlines[$documentType] ?? [];

        return count(array_unique(array_column($rows, 'when'))) > 1;
    }

    /** @return array<string, array<int, array{who: string, when: string, scope: string}>> */
    private function buildDeadlines(): array
    {
        $rows = DB::table('lien_deadline_rules')
            ->join('lien_document_types', 'lien_document_types.id', '=', 'lien_deadline_rules.document_type_id')
            ->where('lien_deadline_rules.state', $this->code)
            ->where('lien_deadline_rules.is_placeholder', false)
            ->orderBy('lien_deadline_rules.id')
            ->get([
                'lien_document_types.slug as document_type',
                'lien_deadline_rules.claimant_type',
                'lien_deadline_rules.trigger_event',
                'lien_deadline_rules.calc_method',
                'lien_deadline_rules.offset_days',
                'lien_deadline_rules.offset_months',
                'lien_deadline_rules.day_of_month',
                'lien_deadline_rules.effective_scope',
                'lien_deadline_rules.conditions_json',
            ]);

        $out = [];
        foreach ($rows->groupBy('document_type') as $type => $group) {
            $out[$type] = $this->collapse($group);
        }

        return $out;
    }

    /**
     * Turn one rule per (claimant, scope) into the fewest readable rows:
     * residential/commercial rows with the same text merge, and claimant
     * types that share a deadline are listed together.
     *
     * @return array<int, array{who: string, when: string, scope: string}>
     */
    private function collapse(Collection $group): array
    {
        $byClaimant = [];
        foreach ($group as $rule) {
            $byClaimant[$rule->claimant_type][$rule->effective_scope] = $this->describe($rule);
        }

        $rows = [];
        foreach ($byClaimant as $claimant => $scopes) {
            $unique = array_unique($scopes);
            if (count($unique) === 1) {
                $rows[] = ['claimant' => $claimant, 'scope' => 'All projects', 'when' => reset($unique)];

                continue;
            }
            foreach ($scopes as $scope => $when) {
                $rows[] = ['claimant' => $claimant, 'scope' => self::scopeLabel($scope), 'when' => $when];
            }
        }

        // Merge claimants that share both scope and deadline text.
        $merged = [];
        foreach ($rows as $row) {
            $key = $row['scope'].'|'.$row['when'];
            $merged[$key]['who'][] = self::claimantLabel($row['claimant']);
            $merged[$key]['scope'] = $row['scope'];
            $merged[$key]['when'] = $row['when'];
        }

        $allClaimants = count(array_unique(array_column($rows, 'claimant')));

        return array_values(array_map(function (array $row) use ($allClaimants) {
            $who = count($row['who']) === $allClaimants && $allClaimants > 1
                ? 'All claimants'
                : self::joinList($row['who']);

            return ['who' => $who, 'scope' => $row['scope'], 'when' => $row['when']];
        }, $merged));
    }

    private function describe(object $rule): string
    {
        $trigger = self::triggerLabel($rule->trigger_event);
        $days = (int) $rule->offset_days;
        $months = (int) $rule->offset_months;

        $text = match ($rule->calc_method) {
            'days_after_date' => $days > 0 ? "within {$days} days after {$trigger}" : "at {$trigger} (see the notes below for the statutory schedule)",
            'months_after_date' => 'within '.($months === 1 ? '1 month' : "{$months} months")." after {$trigger}",
            'month_day_after_month_of_date' => 'by the '.Text::ordinal((int) $rule->day_of_month).' day of the '.Text::ordinal($months)." month after the month of {$trigger}",
            'days_after_end_of_month_of_date' => "within {$days} days after the end of the month of {$trigger}",
            'days_before_date' => $days > 0 ? "at least {$days} days before {$trigger}" : "before {$trigger}",
            default => "see statute ({$trigger})",
        };

        $conditions = is_string($rule->conditions_json) ? json_decode($rule->conditions_json, true) : (array) $rule->conditions_json;
        if (($conditions['anchor'] ?? null) === 'later_of' && ! empty($conditions['dates'])) {
            $alts = array_values(array_filter($conditions['dates'], fn ($d) => $d !== $rule->trigger_event));
            if ($alts) {
                $text .= ' (or '.self::triggerLabel($alts[0]).', whichever is later)';
            }
        }

        return $text;
    }

    private static function triggerLabel(string $trigger): string
    {
        return match ($trigger) {
            'first_furnish_date' => 'first furnishing labor or materials',
            'last_furnish_date' => 'last furnishing labor or materials',
            'completion_date' => 'completion of the project',
            'noc_recorded_date' => 'a notice of completion is recorded',
            'contract_date' => 'the contract date',
            'lien_recorded_date' => 'the lien is recorded',
            'lien_filing_date' => 'filing the lien',
            'contract_terminated_date' => 'termination of the contract',
            'special_fab_delivery_date' => 'delivery of specially fabricated materials',
            default => str_replace('_', ' ', $trigger),
        };
    }

    private static function claimantLabel(string $type): string
    {
        return match ($type) {
            'gc' => 'General contractors',
            'subcontractor' => 'Subcontractors',
            'sub_sub_contractor' => 'Sub-subcontractors',
            'supplier_to_owner' => 'Suppliers to the owner',
            'supplier_to_contractor' => 'Suppliers to the general contractor',
            'supplier_to_subcontractor' => 'Suppliers to a subcontractor',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    private static function scopeLabel(string $scope): string
    {
        return match ($scope) {
            'residential' => 'Residential projects',
            'commercial' => 'Commercial projects',
            default => 'All projects',
        };
    }

    /** @param array<int, string> $items */
    private static function joinList(array $items): string
    {
        $items = array_values(array_unique($items));
        if (count($items) <= 1) {
            return $items[0] ?? '';
        }
        $items = array_map(fn ($item, $i) => $i === 0 ? $item : lcfirst($item), $items, array_keys($items));
        $last = array_pop($items);

        return implode(', ', $items).(count($items) > 1 ? ',' : '').' and '.$last;
    }

    /** @return array<int, string> */
    private static function decodeStatutes(?string $raw): array
    {
        $raw = Text::fixMojibake($raw);
        if (! $raw) {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? array_values(array_filter(array_map('trim', $decoded))) : [$raw];
    }
}
