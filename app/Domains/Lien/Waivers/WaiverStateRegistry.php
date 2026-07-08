<?php

namespace App\Domains\Lien\Waivers;

/**
 * Per-state lien waiver rules, loaded from database/data/waiver_states/{xx}.php.
 *
 * Twelve states prescribe statutory waiver forms (AZ CA FL GA MI MS NV TX UT WY
 * + MA/MO special cases); each has a data file pointing at its statutory Blade
 * bodies and carrying execution rules (notary/witness, e-sign availability,
 * deemed-effective day counts). The other states fall back to the generic
 * house forms via defaults(), overridden only by an advisory data file.
 *
 * Data file contract (all keys optional except state/state_name; missing keys
 * inherit the generic defaults):
 *
 *   'state' => 'GA', 'state_name' => 'Georgia',
 *   'family' => 'statutory_two',           // statutory_four|statutory_two|statutory_single|safe_harbor|special|generic
 *   'statute' => 'O.C.G.A. § 44-14-366',
 *   'compliance_standard' => 'substantial', // substantial|verbatim|generic
 *   'notarization_required' => false,
 *   'witness_required' => true,
 *   'esign_allowed' => false,
 *   'esign_disabled_reason' => '…shown wherever the send button is hidden…',
 *   'deemed_effective_days' => 90,          // GA 90 / MS 60, else null
 *   'affidavit_of_nonpayment' => true,      // GA/MS preservation filing exists
 *   'advance_waiver_note' => '…',           // anti-waiver statute advisory
 *   'ui_notes' => ['…'],                    // banners shown in the wizard
 *   'kinds' => [
 *       'conditional_progress' => [
 *           'enabled' => true,
 *           'template' => 'documents.lien.waivers.bodies.ga-interim', // body partial
 *           'title' => 'Interim Waiver and Release Upon Payment',
 *           'template_version' => 1,
 *           'disabled_reason' => null,      // set when enabled=false
 *           'redirect_kind' => null,        // kind the UI should steer to instead
 *           'residential_template' => null, // MO: statutory body when property is residential
 *           'residential_title' => null,
 *       ],
 *       …one entry per canonical kind…
 *   ],
 *   'landing' => ['headline' => '…', 'summary' => '…'], // state SEO page copy
 */
class WaiverStateRegistry
{
    /** @var array<string, array<string, mixed>> */
    private static array $cache = [];

    /** @var array<string, string> */
    public const STATE_NAMES = [
        'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
        'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
        'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
        'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
        'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
        'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
        'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
        'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
        'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
        'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
        'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
        'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
        'WI' => 'Wisconsin', 'WY' => 'Wyoming',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function for(string $state): array
    {
        $state = strtoupper($state);

        if (isset(self::$cache[$state])) {
            return self::$cache[$state];
        }

        $path = database_path('data/waiver_states/'.strtolower($state).'.php');
        $overrides = is_file($path) ? require $path : [];

        return self::$cache[$state] = self::merge(self::defaults($state), $overrides);
    }

    public static function isSupported(string $state): bool
    {
        return isset(self::STATE_NAMES[strtoupper($state)]);
    }

    /**
     * Rules for every state; the landing-page index and tests iterate this.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return collect(self::STATE_NAMES)
            ->mapWithKeys(fn (string $name, string $code) => [$code => self::for($code)])
            ->all();
    }

    /** Used by tests to force re-reads of the data files. */
    public static function flush(): void
    {
        self::$cache = [];
    }

    /**
     * The generic house-form definition every state starts from.
     *
     * @return array<string, mixed>
     */
    private static function defaults(string $state): array
    {
        $kinds = [];

        foreach ([
            'conditional_progress' => 'Conditional Waiver and Release of Lien: Progress Payment',
            'unconditional_progress' => 'Unconditional Waiver and Release of Lien: Progress Payment',
            'conditional_final' => 'Conditional Waiver and Release of Lien: Final Payment',
            'unconditional_final' => 'Unconditional Waiver and Release of Lien: Final Payment',
        ] as $kind => $title) {
            $kinds[$kind] = [
                'enabled' => true,
                'template' => 'documents.lien.waivers.bodies.generic-'.str_replace('_', '-', $kind),
                'title' => $title,
                'template_version' => 1,
                'disabled_reason' => null,
                'redirect_kind' => null,
                'residential_template' => null,
                'residential_title' => null,
            ];
        }

        return [
            'state' => $state,
            'state_name' => self::STATE_NAMES[$state] ?? $state,
            'family' => 'generic',
            'statute' => null,
            'compliance_standard' => 'generic',
            'notarization_required' => false,
            'witness_required' => false,
            'esign_allowed' => true,
            'esign_disabled_reason' => null,
            'deemed_effective_days' => null,
            'affidavit_of_nonpayment' => false,
            'advance_waiver_note' => null,
            'ui_notes' => [],
            'kinds' => $kinds,
            'landing' => [],
        ];
    }

    /**
     * Overrides win; kind entries merge per-key so a data file can override
     * just a template or title without restating the whole entry.
     *
     * @param  array<string, mixed>  $defaults
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private static function merge(array $defaults, array $overrides): array
    {
        $kinds = $defaults['kinds'];

        foreach ($overrides['kinds'] ?? [] as $kind => $entry) {
            $kinds[$kind] = array_merge($kinds[$kind] ?? [], $entry);
        }

        return array_merge($defaults, $overrides, ['kinds' => $kinds]);
    }
}
