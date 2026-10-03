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
            'how-to-file-a-mechanics-lien' => [
                'title' => 'How to File a Mechanics Lien',
                'page_title' => 'How to File a Mechanics Lien, Step by Step',
                'description' => 'The steps to file a mechanics lien in any state: required notices, the filing deadline, what the claim says, where to record it, serving the owner and enforcing it.',
                'summary' => 'The steps from first notice to release, and the states that add or change a step.',
                'cluster' => 'liens',
                'published' => '2026-10-03',
                'updated' => '2026-10-03',
                'view' => 'pages.guides.how-to-file-a-mechanics-lien',
                'related' => [
                    ['name' => 'Mechanics lien service', 'url' => route('liens'), 'text' => 'We prepare and record the lien for you.'],
                    ['name' => 'Lien deadline calculator', 'url' => route('liens.deadline-calculator'), 'text' => 'Turn your project dates into deadlines for any state.'],
                    ['name' => 'Mechanics lien deadlines by state', 'url' => route('guides.show', ['slug' => 'mechanics-lien-deadlines-by-state']), 'text' => 'The lien filing deadline in every state.'],
                    ['name' => 'Preliminary notice requirements by state', 'url' => route('guides.show', ['slug' => 'preliminary-notice-requirements-by-state']), 'text' => 'Who must send one, when, and how, state by state.'],
                    ['name' => 'Notice of intent to lien explained', 'url' => route('guides.show', ['slug' => 'notice-of-intent-to-lien-explained'])],
                ],
            ],
            'notice-of-intent-to-lien-explained' => [
                'title' => 'Notice of Intent to Lien Explained',
                'page_title' => 'Notice of Intent to Lien Explained',
                'description' => 'What a notice of intent to lien is, which states require one before a mechanics lien, how much lead time each sets, what it should say and how to send it.',
                'summary' => 'What the notice is, the states that require one, their lead times and how to send it.',
                'cluster' => 'liens',
                'published' => '2026-10-03',
                'updated' => '2026-10-03',
                'view' => 'pages.guides.notice-of-intent-to-lien-explained',
                'related' => [
                    ['name' => 'Notice of intent to lien service', 'url' => route('liens.notice-of-intent-to-lien')],
                    ['name' => 'Colorado notice of intent', 'url' => route('liens.notice-of-intent-to-lien.state', ['state' => 'colorado'])],
                    ['name' => 'Missouri notice of intent', 'url' => route('liens.notice-of-intent-to-lien.state', ['state' => 'missouri'])],
                    ['name' => 'How to file a mechanics lien', 'url' => route('guides.show', ['slug' => 'how-to-file-a-mechanics-lien'])],
                    ['name' => 'Lien deadline calculator', 'url' => route('liens.deadline-calculator'), 'text' => 'Turn your project dates into deadlines for any state.'],
                ],
            ],
            'what-to-do-when-a-contractor-or-owner-doesnt-pay' => [
                'title' => 'What to Do When a Contractor or Owner Does Not Pay',
                'page_title' => 'Contractor Not Paying a Subcontractor? What to Do',
                'description' => 'What a subcontractor or supplier can do when a contractor or owner does not pay: check the deadlines, send a demand, give notice, file a lien and enforce it.',
                'summary' => 'The escalation ladder for unpaid subs and suppliers, from demand letter to lien suit.',
                'cluster' => 'liens',
                'published' => '2026-10-03',
                'updated' => '2026-10-03',
                'view' => 'pages.guides.what-to-do-when-a-contractor-or-owner-doesnt-pay',
                'related' => [
                    ['name' => 'Payment demand letter', 'url' => route('liens.payment-demand-letter')],
                    ['name' => 'Notice of intent to lien', 'url' => route('liens.notice-of-intent-to-lien')],
                    ['name' => 'Mechanics lien service', 'url' => route('liens'), 'text' => 'We prepare and record the lien for you.'],
                    ['name' => 'Lien deadline calculator', 'url' => route('liens.deadline-calculator'), 'text' => 'Turn your project dates into deadlines for any state.'],
                    ['name' => 'How to file a mechanics lien', 'url' => route('guides.show', ['slug' => 'how-to-file-a-mechanics-lien'])],
                ],
            ],
            'how-to-release-a-mechanics-lien' => [
                'title' => 'How to Release a Mechanics Lien',
                'page_title' => 'How to Release a Mechanics Lien After Payment',
                'description' => 'When you must release a mechanics lien after payment, the deadlines and penalties in 20 states, who signs, where it is recorded and what to do about a refusal.',
                'summary' => 'Release deadlines and penalties for 20 states, plus who signs and where it is recorded.',
                'cluster' => 'liens',
                'published' => '2026-10-03',
                'updated' => '2026-10-03',
                'view' => 'pages.guides.how-to-release-a-mechanics-lien',
                'related' => [
                    ['name' => 'Lien release service', 'url' => route('liens.lien-release')],
                    ['name' => 'Texas lien release', 'url' => route('liens.lien-release.state', ['state' => 'texas'])],
                    ['name' => 'Illinois lien release', 'url' => route('liens.lien-release.state', ['state' => 'illinois'])],
                    ['name' => 'Lien waivers', 'url' => route('liens.lien-waivers')],
                    ['name' => 'How to file a mechanics lien', 'url' => route('guides.show', ['slug' => 'how-to-file-a-mechanics-lien'])],
                ],
            ],
            'mechanics-lien-deadline-mistakes' => [
                'title' => 'Mechanics Lien Deadline Mistakes and What to Do After One',
                'page_title' => 'Missed a Mechanics Lien Deadline? What Still Works',
                'description' => 'The seven ways claimants lose mechanics lien rights, with state examples and statutes, and what you can still do to get paid after a missed deadline.',
                'summary' => 'Seven ways claimants lose lien rights, and the options left after a missed deadline.',
                'cluster' => 'liens',
                'published' => '2026-10-03',
                'updated' => '2026-10-03',
                'view' => 'pages.guides.mechanics-lien-deadline-mistakes',
                'related' => [
                    ['name' => 'Lien deadline calculator', 'url' => route('liens.deadline-calculator'), 'text' => 'Turn your project dates into deadlines for any state.'],
                    ['name' => 'Mechanics lien deadlines by state', 'url' => route('guides.show', ['slug' => 'mechanics-lien-deadlines-by-state']), 'text' => 'The lien filing deadline in every state.'],
                    ['name' => 'Preliminary notice', 'url' => route('liens.preliminary-notice'), 'text' => 'We prepare and send the notice for you.'],
                    ['name' => 'Payment demand letter', 'url' => route('liens.payment-demand-letter')],
                    ['name' => 'What to do when a contractor or owner does not pay', 'url' => route('guides.show', ['slug' => 'what-to-do-when-a-contractor-or-owner-doesnt-pay'])],
                ],
            ],
            'conditional-vs-unconditional-lien-waivers' => [
                'title' => 'Conditional vs Unconditional Lien Waivers',
                'page_title' => 'Conditional vs Unconditional Lien Waivers',
                'description' => 'A conditional lien waiver takes effect only when your payment clears. An unconditional waiver binds you the moment you sign. Learn which to use and when.',
                'summary' => 'The four lien waiver types, when to use each, and the states that make you use a statutory form.',
                'cluster' => 'waivers',
                'published' => '2026-10-03',
                'updated' => '2026-10-03',
                'view' => 'pages.guides.conditional-vs-unconditional-lien-waivers',
                'related' => [
                    ['name' => 'Lien waiver generator', 'url' => route('liens.lien-waivers')],
                    ['name' => 'How to fill out a lien waiver', 'url' => route('guides.show', ['slug' => 'how-to-fill-out-a-lien-waiver'])],
                    ['name' => 'California lien waiver forms', 'url' => route('liens.lien-waivers.state', ['state' => 'ca'])],
                    ['name' => 'Texas lien waiver forms', 'url' => route('liens.lien-waivers.state', ['state' => 'tx'])],
                    ['name' => 'Mechanics lien deadlines by state', 'url' => route('guides.show', ['slug' => 'mechanics-lien-deadlines-by-state']), 'text' => 'The lien filing deadline in every state.'],
                ],
            ],
            'how-to-fill-out-a-lien-waiver' => [
                'title' => 'How to Fill Out a Lien Waiver',
                'page_title' => 'How to Fill Out a Lien Waiver, Field by Field',
                'description' => 'Fill out a lien waiver one field at a time: claimant, customer, owner, project, through date, amount, exceptions and signature, plus when a notary is required.',
                'summary' => 'A field-by-field walkthrough of a lien waiver, the common errors, and what to do when the amount is wrong.',
                'cluster' => 'waivers',
                'published' => '2026-10-03',
                'updated' => '2026-10-03',
                'view' => 'pages.guides.how-to-fill-out-a-lien-waiver',
                'related' => [
                    ['name' => 'Lien waiver generator', 'url' => route('liens.lien-waivers')],
                    ['name' => 'Conditional vs unconditional lien waivers', 'url' => route('guides.show', ['slug' => 'conditional-vs-unconditional-lien-waivers'])],
                    ['name' => 'California lien waiver forms', 'url' => route('liens.lien-waivers.state', ['state' => 'ca'])],
                    ['name' => 'Texas lien waiver forms', 'url' => route('liens.lien-waivers.state', ['state' => 'tx'])],
                    ['name' => 'Georgia lien waiver forms', 'url' => route('liens.lien-waivers.state', ['state' => 'ga'])],
                ],
            ],
            'when-do-you-need-a-sales-tax-permit' => [
                'title' => 'When Do You Need a Sales Tax Permit',
                'page_title' => 'Do I Need a Sales Tax Permit? When You Must Register',
                'description' => 'You need a sales tax permit in a state once you have nexus there through a location, staff, inventory or enough sales. Here is how to tell.',
                'summary' => 'Physical presence, economic nexus, marketplace sales, events and the five states with no general sales tax.',
                'cluster' => 'sales-tax',
                'published' => '2026-10-03',
                'updated' => '2026-10-03',
                'view' => 'pages.guides.when-do-you-need-a-sales-tax-permit',
                'related' => [
                    ['name' => 'Sales tax registration', 'url' => route('sales-tax-registration'), 'text' => 'We prepare and file your state registrations.'],
                    ['name' => 'Economic nexus thresholds by state', 'url' => route('guides.show', ['slug' => 'economic-nexus-thresholds-by-state'])],
                    ['name' => 'Sales tax registration checklist', 'url' => route('guides.show', ['slug' => 'sales-tax-registration-checklist'])],
                    ['name' => 'Texas sales tax registration', 'url' => route('sales-tax-registration.state', ['state' => 'texas'])],
                    ['name' => 'California sales tax registration', 'url' => route('sales-tax-registration.state', ['state' => 'california'])],
                ],
            ],
            'sales-tax-registration-checklist' => [
                'title' => 'Sales Tax Registration Checklist',
                'page_title' => 'How to Register for a Sales Tax Permit, Checklist',
                'description' => 'What every state asks for when you register for a sales tax permit, how long approval takes, when a bond applies, and how to file your first return.',
                'summary' => 'The documents and details to gather, the usual timeline, bonds, filing frequency and your first return.',
                'cluster' => 'sales-tax',
                'published' => '2026-10-03',
                'updated' => '2026-10-03',
                'view' => 'pages.guides.sales-tax-registration-checklist',
                'related' => [
                    ['name' => 'Sales tax registration', 'url' => route('sales-tax-registration'), 'text' => 'We prepare and file your state registrations.'],
                    ['name' => 'When do you need a sales tax permit', 'url' => route('guides.show', ['slug' => 'when-do-you-need-a-sales-tax-permit'])],
                    ['name' => 'EIN (tax ID) application', 'url' => route('ein-tax-id')],
                    ['name' => 'Texas sales tax registration', 'url' => route('sales-tax-registration.state', ['state' => 'texas'])],
                    ['name' => 'Florida sales tax registration', 'url' => route('sales-tax-registration.state', ['state' => 'florida'])],
                ],
            ],
            'sellers-permit-vs-resale-certificate' => [
                'title' => 'Seller\'s Permit vs Resale Certificate',
                'page_title' => 'Seller\'s Permit vs Resale Certificate',
                'description' => 'A seller\'s permit lets you collect sales tax. A resale certificate lets you buy inventory without paying it. Here is who issues each and what they cost.',
                'summary' => 'What each document does, who issues it, what sellers must keep, misuse penalties and the multistate forms.',
                'cluster' => 'resale',
                'published' => '2026-10-03',
                'updated' => '2026-10-03',
                'view' => 'pages.guides.sellers-permit-vs-resale-certificate',
                'related' => [
                    ['name' => 'Resale certificates by state', 'url' => route('resale-certificates'), 'text' => 'Buy inventory tax-free with signed certificates.'],
                    ['name' => 'Sales tax registration', 'url' => route('sales-tax-registration'), 'text' => 'We prepare and file your state registrations.'],
                    ['name' => 'Texas resale certificate', 'url' => route('resale-certificates.state', ['state' => 'texas'])],
                    ['name' => 'California resale certificate', 'url' => route('resale-certificates.state', ['state' => 'california'])],
                    ['name' => 'How to fill out Texas Form 01-339', 'url' => route('guides.show', ['slug' => 'how-to-fill-out-texas-form-01-339'])],
                ],
            ],
            'how-to-fill-out-texas-form-01-339' => [
                'title' => 'How to Fill Out Texas Form 01-339',
                'page_title' => 'How to Fill Out Texas Form 01-339 (Resale Certificate)',
                'description' => 'Texas Form 01-339 is the sales and use tax resale certificate you give your supplier. Here is who can use it and how to complete each field.',
                'summary' => 'Who can use the Texas resale certificate, each field on the form, the exemption side, and misuse penalties.',
                'cluster' => 'resale',
                'published' => '2026-10-03',
                'updated' => '2026-10-03',
                'view' => 'pages.guides.how-to-fill-out-texas-form-01-339',
                'related' => [
                    ['name' => 'Texas resale certificate', 'url' => route('resale-certificates.state', ['state' => 'texas'])],
                    ['name' => 'Texas sales tax registration', 'url' => route('sales-tax-registration.state', ['state' => 'texas'])],
                    ['name' => 'Seller\'s permit vs resale certificate', 'url' => route('guides.show', ['slug' => 'sellers-permit-vs-resale-certificate'])],
                    ['name' => 'Resale certificates by state', 'url' => route('resale-certificates'), 'text' => 'Buy inventory tax-free with signed certificates.'],
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
