<?php

namespace App\Domains\Lien\Documents;

use App\Domains\Lien\Models\LienStateRule;
use App\Domains\Lien\Waivers\WaiverStateRegistry;

/**
 * Per-state rules for the generated lien documents (preliminary notice, notice
 * of intent, mechanics lien, lien release), loaded from
 * database/data/lien_documents/{xx}.php with county facts layered on from
 * database/data/lien_counties/{xx}/{county-slug}.php.
 *
 * Every state starts from defaults(): the generic bodies, the recorder
 * conventions the strict recorders want (3" first-page space, 1" margins,
 * 10pt minimum, page numbers) and the execution/service facts seeded in
 * lien_state_rules (notarization, verification type, who gets served and
 * how many days after recording). A state data file overrides only what the
 * statute makes different; a county file overrides only `recording` and
 * appends `notes`.
 *
 * Data file contract (every key optional; missing keys inherit the defaults):
 *
 *   'state' => 'FL', 'state_name' => 'Florida',
 *   'attorney_only' => false,             // default: config('lien.attorney_referral_states')
 *   'recording' => [                      // statewide recorder conventions
 *       'filing_office' => ['label' => 'Clerk of the Circuit Court', 'method' => 'erecord|mail|either',
 *                           'address_lines' => [], 'vendor' => null],
 *       'top_margin_in' => 3.0, 'other_margin_in' => 1.0, 'min_font_pt' => 10, 'page_numbers' => true,
 *       'side_margin_in' => null,          // left/right margins; null = other_margin_in (Indiana: 2in top/bottom, 1in sides)
 *       'caption' => 'state_county|docket', // PA files with a court docket caption
 *       'index_line' => true,              // compact claimant / owner / amount / parcel line on page 1
 *       'index_block' => false,            // MO-style grantor/grantee recording block on page 1
 *       'index_roles' => null,             // e.g. ['grantor' => 'owner', 'grantee' => 'claimant']
 *       'preparer_in_space' => true,       // preparer block in the left half of the page-1 space (false: below it, MO)
 *       'legend' => null,                  // submitter legend printed under the rule (e-recording MOUs, e.g. Johnson County KS)
 *       'cover_sheet' => false,            // mail-in office wants eRegister's filing cover sheet
 *       'parcel_label' => 'Parcel ID',     // APN / PIN / Prop ID / Tax ID / PCN / Folio
 *       'fee_note' => null, 'adds_cover_page' => false,
 *       'notes' => [],                     // admin hints on the Documents card; never printed
 *   ],
 *   'execution' => [                      // instrument defaults; a kind may override
 *       'verification' => 'sworn|verified|acknowledged|none',
 *       'notary' => true, 'notary_form' => 'jurat|acknowledgment|null',
 *       'notary_variant' => null,          // 'fl' | 'ca' | 'nc' for state-specific certificate wording
 *       'notary_county_line' => false,     // generic certificate adds a "Notary's county of commission" line (IN)
 *       'statement' => true,               // print the sworn/verified statement above the signature
 *                                          // (false when the body is itself the sworn statement, FL)
 *       'witness' => false,
 *   ],
 *   'service' => [                        // defaults for every kind; a kind may override
 *       'recipients' => ['owner', 'gc', 'lender'], // PartyRole values
 *       'days_after' => 15,                // days after recording / sending to serve copies
 *       'method' => 'certified_mail',
 *       'proof' => 'declaration|affidavit', // affidavit = notarized proof of service
 *       'certificate_on_instrument' => false, // CA: proof of service printed with the lien
 *       'perjury_state' => null,           // state whose law the proof of service is declared under
 *                                          // (null: where staff sign it, config lien.documents.server_state;
 *                                          // CA: 'CA', since CCP § 2015.5 declarations recite California law)
 *   ],
 *   'kinds' => [
 *       'mechanics_lien' => [
 *           'enabled' => true, 'disabled_reason' => null,
 *           'title' => 'Claim of Lien', 'statute' => 'Fla. Stat. § 713.08',
 *           'body' => 'documents.lien.instruments.bodies.fl-claim-of-lien',
 *           'template_version' => 1,
 *           'sections' => [...],           // see defaults(); toggles for the generic bodies
 *           'clauses' => ['notice_box' => null, 'bold_statement' => null, 'after_property' => [],
 *                         'before_signature' => [], 'affirmations' => [], 'after_execution' => [], 'demand' => null],
 *           // a clause is a plain paragraph string, or a view name when it has structure
 *           'execution' => [], 'service' => [], // per-kind overrides of the state-level blocks
 *           'attachments' => [], 'notes' => [],
 *       ],
 *       'lien_release' => [...], 'prelim_notice' => [...], 'noi' => [...],
 *   ],
 *
 * Statutory clause text in these files and in resources/views/documents/lien/
 * is legally load-bearing: never edit it without re-checking the statute.
 */
class LienDocumentRegistry
{
    /** @var list<string> the four generated kinds, keyed like LienDocumentType.slug */
    public const KINDS = ['prelim_notice', 'noi', 'mechanics_lien', 'lien_release'];

    /** @var array<string, string> which layout each kind renders through */
    public const FAMILIES = [
        'prelim_notice' => 'letter',
        'noi' => 'letter',
        'mechanics_lien' => 'instrument',
        'lien_release' => 'instrument',
    ];

    /** @var array<string, array<string, mixed>> */
    private static array $cache = [];

    /** @var array<string, array<string, mixed>|null> */
    private static array $countyCache = [];

    /**
     * @return array<string, mixed>
     */
    public static function for(string $state): array
    {
        $state = strtoupper($state);

        if (isset(self::$cache[$state])) {
            return self::$cache[$state];
        }

        $path = database_path('data/lien_documents/'.strtolower($state).'.php');
        $overrides = is_file($path) ? require $path : [];

        return self::$cache[$state] = self::merge(self::defaults($state), $overrides);
    }

    /**
     * The county data file for a normalized county key, or null when the
     * county has no file (the common case: state defaults apply).
     *
     * @return array<string, mixed>|null
     */
    public static function county(string $state, ?string $key): ?array
    {
        if ($key === null || $key === '') {
            return null;
        }

        $state = strtoupper($state);
        $cacheKey = $state.'/'.$key;

        if (array_key_exists($cacheKey, self::$countyCache)) {
            return self::$countyCache[$cacheKey];
        }

        $path = database_path('data/lien_counties/'.strtolower($state).'/'.$key.'.php');
        $county = is_file($path) ? require $path : null;

        if (is_array($county)) {
            $county['state'] = $state;
            $county['key'] = $key;
            $county['recording'] = $county['recording'] ?? [];
            $county['notes'] = $county['notes'] ?? [];
        } else {
            $county = null;
        }

        return self::$countyCache[$cacheKey] = $county;
    }

    public static function isSupported(string $state): bool
    {
        return isset(WaiverStateRegistry::STATE_NAMES[strtoupper($state)]);
    }

    /**
     * Rules for every state; tests iterate this.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return collect(WaiverStateRegistry::STATE_NAMES)
            ->mapWithKeys(fn (string $name, string $code) => [$code => self::for($code)])
            ->all();
    }

    /** Used by tests to force re-reads of the data files. */
    public static function flush(): void
    {
        self::$cache = [];
        self::$countyCache = [];
    }

    /**
     * The generic definition every state starts from, seeded from the
     * lien_state_rules row where one exists.
     *
     * @return array<string, mixed>
     */
    private static function defaults(string $state): array
    {
        $rule = LienStateRule::query()->find($state);

        $notary = $rule?->notarization_required ?? true;
        $verification = self::verificationFromRule($rule?->verification_type);
        $notaryForm = $notary ? ($verification === 'acknowledged' ? 'acknowledgment' : 'jurat') : null;

        $execution = [
            'verification' => $verification,
            'notary' => $notary,
            'notary_form' => $notaryForm,
            'notary_variant' => null,
            'notary_county_line' => false,
            'statement' => true,
            'witness' => false,
        ];

        $instrumentService = [
            'recipients' => self::recipientsFromRule($rule?->post_lien_notice_recipients, ['owner']),
            'days_after' => $rule?->post_lien_notice_days,
            'method' => $rule?->prelim_delivery_method ?: 'certified_mail',
            'proof' => 'declaration',
            'certificate_on_instrument' => false,
            'perjury_state' => null,
        ];

        $noticeService = [
            'recipients' => self::recipientsFromRule($rule?->prelim_recipients, ['owner']),
            'days_after' => null,
            'method' => $rule?->prelim_delivery_method ?: 'certified_mail',
            'proof' => 'declaration',
            'certificate_on_instrument' => false,
            'perjury_state' => null,
        ];

        $noticeExecution = [
            'verification' => 'none',
            'notary' => false,
            'notary_form' => null,
            'notary_variant' => null,
            'notary_county_line' => false,
            'statement' => true,
            'witness' => false,
        ];

        $kinds = [
            'prelim_notice' => self::kindDefaults(
                title: 'Preliminary Notice',
                body: 'documents.lien.letters.bodies.generic-prelim',
                sections: ['amount' => 'estimate', 'first_furnish' => true, 'last_furnish' => false],
                execution: $noticeExecution,
                service: $noticeService,
            ),
            'noi' => self::kindDefaults(
                title: 'Notice of Intent to Lien',
                body: 'documents.lien.letters.bodies.generic-noi',
                sections: ['amount' => 'breakdown', 'demand_days' => 10],
                execution: $noticeExecution,
                service: $noticeService,
            ),
            'mechanics_lien' => self::kindDefaults(
                title: 'Claim of Lien',
                body: 'documents.lien.instruments.bodies.generic-lien',
                sections: ['amount' => 'breakdown'],
                execution: $execution,
                service: $instrumentService,
            ),
            'lien_release' => self::kindDefaults(
                title: 'Release of Lien',
                body: 'documents.lien.instruments.bodies.generic-release',
                sections: ['amount' => 'single', 'prior_notice' => false, 'first_furnish' => false, 'last_furnish' => false],
                execution: array_replace($execution, ['verification' => 'acknowledged', 'notary_form' => $notary ? 'acknowledgment' : null]),
                service: array_replace($instrumentService, ['recipients' => self::recipientsFromRule($rule?->post_lien_notice_recipients, ['owner'])]),
            ),
        ];

        return [
            'state' => $state,
            'state_name' => WaiverStateRegistry::STATE_NAMES[$state] ?? $state,
            'attorney_only' => in_array($state, config('lien.attorney_referral_states', []), true),
            'recording' => [
                'filing_office' => [
                    'label' => self::filingOfficeLabel($rule?->filing_location),
                    'method' => 'either',
                    'address_lines' => [],
                    'vendor' => null,
                ],
                'top_margin_in' => 3.0,
                'other_margin_in' => 1.0,
                'side_margin_in' => null,
                'min_font_pt' => 10,
                'page_numbers' => true,
                'caption' => 'state_county',
                'index_line' => true,
                'index_block' => false,
                'index_roles' => null,
                'preparer_in_space' => true,
                'legend' => null,
                'cover_sheet' => false,
                'parcel_label' => 'Parcel ID',
                'fee_note' => null,
                'adds_cover_page' => false,
                'notes' => [],
            ],
            'execution' => $execution,
            'service' => $instrumentService,
            'kinds' => $kinds,
        ];
    }

    /**
     * @param  array<string, mixed>  $sections
     * @param  array<string, mixed>  $execution
     * @param  array<string, mixed>  $service
     * @return array<string, mixed>
     */
    private static function kindDefaults(string $title, string $body, array $sections, array $execution, array $service): array
    {
        return [
            'enabled' => true,
            'disabled_reason' => null,
            'title' => $title,
            'statute' => null,
            'body' => $body,
            'template_version' => 1,
            'sections' => array_replace([
                'amount' => 'breakdown',          // single | breakdown | itemized | estimate
                'amount_in_words' => false,
                'gc' => true,
                'lender' => false,
                'hiring_party' => true,
                'contract_date' => false,
                'contract_type' => false,
                'months_of_work' => false,
                'license' => false,
                'lien_agent' => false,
                'block_lot' => false,
                'owner_interest' => false,
                'prior_notice' => true,           // "notice served on … by …" sentence when a prior notice exists
                'first_furnish' => true,
                'last_furnish' => true,
                'cancellation_block' => false,
                'demand_days' => null,            // NOI: pay-within-N-days demand
            ], $sections),
            'clauses' => [
                'notice_box' => null,       // boxed statutory notice (string or view name)
                'bold_statement' => null,   // a sentence the statute wants in large bold type (GA 395-day statement)
                'after_property' => [],
                'before_signature' => [],
                'affirmations' => [],
                'after_execution' => [],    // e.g. NC's "Filed this ___ day of ___ / Clerk of Superior Court" lines
                'demand' => null,           // NOI demand paragraph override
            ],
            'execution' => $execution,
            'service' => $service,
            'attachments' => [],
            'notes' => [],
        ];
    }

    /**
     * Overrides win. `recording`, `execution` and `service` merge key by key;
     * kind entries merge per kind, and their `sections`, `clauses`,
     * `execution` and `service` sub-arrays merge key by key too, so a data
     * file can override a single toggle without restating the whole entry.
     *
     * @param  array<string, mixed>  $defaults
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private static function merge(array $defaults, array $overrides): array
    {
        $merged = array_replace($defaults, $overrides);

        foreach (['recording', 'execution', 'service'] as $block) {
            $merged[$block] = array_replace($defaults[$block], $overrides[$block] ?? []);
        }

        $merged['recording']['filing_office'] = array_replace(
            $defaults['recording']['filing_office'],
            $overrides['recording']['filing_office'] ?? [],
        );

        $kinds = $defaults['kinds'];

        foreach ($overrides['kinds'] ?? [] as $kind => $entry) {
            $base = $kinds[$kind] ?? $defaults['kinds']['mechanics_lien'];
            $kinds[$kind] = array_replace($base, $entry);

            foreach (['sections', 'clauses', 'execution', 'service'] as $sub) {
                $kinds[$kind][$sub] = array_replace($base[$sub], $entry[$sub] ?? []);
            }
        }

        // A state-level execution/service override applies to the instruments
        // (the notices keep their own no-notary defaults unless the kind says so).
        foreach (['mechanics_lien', 'lien_release'] as $kind) {
            foreach (['execution', 'service'] as $sub) {
                $kinds[$kind][$sub] = array_replace(
                    $defaults['kinds'][$kind][$sub],
                    $overrides[$sub] ?? [],
                    $overrides['kinds'][$kind][$sub] ?? [],
                );
            }
        }

        $merged['kinds'] = $kinds;

        return $merged;
    }

    private static function verificationFromRule(?string $type): string
    {
        return match ($type) {
            'verified' => 'verified',
            'notarized' => 'acknowledged',
            'none' => 'none',
            default => 'sworn',
        };
    }

    /**
     * lien_state_rules encodes recipients as "owner", "owner_gc",
     * "owner_gc_lender", "owner_lender"… Anything that isn't a party role
     * (lien_agent, state_registry, parish_recorder) is a separate flow.
     *
     * @param  list<string>  $fallback
     * @return list<string>
     */
    private static function recipientsFromRule(?string $spec, array $fallback): array
    {
        if ($spec === null || $spec === '') {
            return $fallback;
        }

        $roles = array_values(array_filter(
            explode('_', $spec),
            fn (string $token) => in_array($token, ['owner', 'gc', 'lender'], true),
        ));

        return $roles === [] ? $fallback : $roles;
    }

    private static function filingOfficeLabel(?string $location): string
    {
        return match ($location) {
            'county_recorder' => 'County recorder',
            'circuit_clerk' => 'Clerk of the circuit court',
            'register_of_deeds' => 'Register of deeds',
            'town_clerk' => 'Town or city clerk',
            null, '' => 'County recorder',
            default => $location,
        };
    }
}
