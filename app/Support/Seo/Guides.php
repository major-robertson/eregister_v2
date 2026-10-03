<?php

namespace App\Support\Seo;

use Illuminate\Support\Carbon;

/**
 * The editorial guides at "/guides/{slug}": one static entry per guide. The
 * hub (/guides), the guide route and the sitemap all read this list, so a new
 * guide needs an entry here plus its Blade view and nothing else.
 *
 * Entry shape (every key required unless marked optional):
 *
 *   'mechanics-lien-deadlines-by-state' => [         // the slug: lower-case, hyphens, unique
 *       'title' => 'Mechanics Lien Deadlines by State',  // the H1 and the Article headline
 *       'page_title' => '…',                             // <title>: 60 characters or fewer, no " | eRegister"
 *       'description' => '…',                            // meta description: 70 to 165 characters
 *       'summary' => '…',                                // one line for the hub card
 *       'cluster' => 'liens',                            // a key of CLUSTERS
 *       'published' => '2026-10-03',                     // ISO date, set once
 *       'updated' => '2026-10-03',                       // ISO date; becomes dateModified and the sitemap lastmod
 *       'view' => 'pages.guides.mechanics-lien-deadlines-by-state',
 *       'related' => [                                   // the related-links module under the article
 *           ['name' => 'Deadline calculator', 'url' => route('liens.deadline-calculator'), 'text' => 'Optional line.'],
 *       ],
 *   ],
 *
 * Entries are built in definitions() rather than a constant so 'related' can
 * use route(). all() adds the 'slug' key to each entry.
 */
final class Guides
{
    /** Cluster key => the H2 on the hub. A cluster shows on the hub once it has a guide. */
    public const CLUSTERS = [
        'liens' => 'Mechanics liens',
        'waivers' => 'Lien waivers',
        'sales-tax' => 'Sales tax',
        'resale' => 'Resale certificates',
    ];

    /** @return array<string, array<string, mixed>> slug => entry */
    private static function definitions(): array
    {
        return [
            'mechanics-lien-deadlines-by-state' => [
                'title' => 'Mechanics Lien Deadlines by State',
                'page_title' => 'Mechanics Lien Deadlines by State: All 50 States',
                'description' => 'The mechanics lien filing deadline in all 50 states, with the preliminary notice, notice of intent and enforcement rules for each state.',
                'summary' => 'The lien filing deadline, notice rules and enforcement period for every state in one table.',
                'cluster' => 'liens',
                'published' => '2026-10-03',
                'updated' => '2026-10-03',
                'view' => 'pages.guides.mechanics-lien-deadlines-by-state',
                'related' => [
                    ['name' => 'Lien deadline calculator', 'url' => route('liens.deadline-calculator'), 'text' => 'Turn your project dates into deadlines for any state.'],
                    ['name' => 'Preliminary notice requirements', 'url' => route('guides.show', ['slug' => 'preliminary-notice-requirements-by-state']), 'text' => 'Who must send one, when, and how, state by state.'],
                    ['name' => 'Mechanics lien filing', 'url' => route('liens'), 'text' => 'We prepare and record the lien for you.'],
                ],
            ],
            'preliminary-notice-requirements-by-state' => [
                'title' => 'Preliminary Notice Requirements by State',
                'page_title' => 'Preliminary Notice Requirements by State',
                'description' => 'Who must send a preliminary notice in each state, the deadline, who receives it and how to deliver it, drawn from each state\'s lien statute.',
                'summary' => 'Who must send a preliminary notice in each state, the deadline, the recipients and the delivery method.',
                'cluster' => 'liens',
                'published' => '2026-10-03',
                'updated' => '2026-10-03',
                'view' => 'pages.guides.preliminary-notice-requirements-by-state',
                'related' => [
                    ['name' => 'Preliminary notice service', 'url' => route('liens.preliminary-notice'), 'text' => 'We prepare and send the notice for you.'],
                    ['name' => 'Mechanics lien deadlines', 'url' => route('guides.show', ['slug' => 'mechanics-lien-deadlines-by-state']), 'text' => 'The lien filing deadline in every state.'],
                    ['name' => 'Lien deadline calculator', 'url' => route('liens.deadline-calculator'), 'text' => 'Turn your project dates into deadlines for any state.'],
                ],
            ],
            'economic-nexus-thresholds-by-state' => [
                'title' => 'Economic Nexus Thresholds by State',
                'page_title' => 'Economic Nexus Thresholds by State',
                'description' => 'The remote seller sales tax threshold in every state with a sales tax and DC: the dollar amount, any transaction count, the period and the agency.',
                'summary' => 'The remote seller threshold for every state with a sales tax, with the measurement period and the agency.',
                'cluster' => 'sales-tax',
                'published' => '2026-10-03',
                'updated' => '2026-10-03',
                'view' => 'pages.guides.economic-nexus-thresholds-by-state',
                'related' => [
                    ['name' => 'Sales tax registration', 'url' => route('sales-tax-registration'), 'text' => 'We prepare and file your state registrations.'],
                    ['name' => 'Resale certificates', 'url' => route('resale-certificates'), 'text' => 'Buy inventory tax-free with signed certificates.'],
                    ['name' => 'All guides', 'url' => route('guides.index'), 'text' => 'Liens, lien waivers and sales tax.'],
                ],
            ],
        ];
    }

    /** @return array<string, array<string, mixed>> slug => entry (with 'slug'), in definition order */
    public static function all(): array
    {
        $guides = [];
        foreach (self::definitions() as $slug => $guide) {
            $guides[$slug] = ['slug' => $slug] + $guide;
        }

        return $guides;
    }

    /** @return array<string, mixed>|null */
    public static function find(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    /**
     * Clusters that have at least one guide, in CLUSTERS order.
     *
     * @return array<string, array{label: string, guides: array<int, array<string, mixed>>}>
     */
    public static function byCluster(): array
    {
        $clusters = [];
        foreach (self::CLUSTERS as $key => $label) {
            $guides = array_values(array_filter(self::all(), fn (array $guide) => $guide['cluster'] === $key));
            if ($guides) {
                $clusters[$key] = ['label' => $label, 'guides' => $guides];
            }
        }

        return $clusters;
    }

    public static function clusterLabel(string $cluster): ?string
    {
        return self::CLUSTERS[$cluster] ?? null;
    }

    /** The guide's canonical URL, built from the configured app URL. */
    public static function url(string $slug): string
    {
        return Urls::absolute(route('guides.show', ['slug' => $slug], absolute: false));
    }

    /** The newest 'updated' date in the registry (ISO), for the hub's sitemap entry. */
    public static function lastUpdated(): string
    {
        return max(array_column(self::all(), 'updated'));
    }

    /** "October 3, 2026" */
    public static function displayDate(string $date): string
    {
        return Carbon::parse($date)->format('F j, Y');
    }
}
