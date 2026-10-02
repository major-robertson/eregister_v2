<?php

/*
 * Colorado: Colorado Department of Revenue, Taxation Division, Colorado
 * Sales Tax License (retail sales tax license). Researched 2026-10-02 from
 * tax.colorado.gov, salestaxinstitute.com. Generated once from the EREG-13
 * sales tax research; edit this file directly from now on.
 */

return [
    'state' => 'CO',
    'name' => 'Colorado',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Colorado Department of Revenue, Taxation Division',
        'short' => 'the Department of Revenue',
        'url' => 'https://tax.colorado.gov/',
    ],
    'registration' => [
        'term' => 'Colorado Sales Tax License (retail sales tax license)',
        'portal' => [
            'name' => 'MyBizColorado (registration); Revenue Online (filing and renewal)',
            'url' => 'https://mybiz.colorado.gov/',
        ],
        'form' => [
            'number' => 'CR 0100',
            'title' => 'Colorado Sales Tax and Withholding Account Application',
            'pdf_url' => 'https://tax.colorado.gov/sites/tax/files/documents/CR0100_2025.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 1600,
            'summary' => '$16 license fee per location for the two-year license period, prorated in six-month steps if bought after June 30, plus a $50 deposit on the first retail location. The deposit is refunded automatically after the business has remitted $50 in state sales tax. A wholesale license is $16 with no deposit; a charitable license is $8.',
            'cite' => 'CR 0100 instructions (11/22/24), https://tax.colorado.gov/sites/tax/files/documents/CR0100_2025.pdf; https://tax.colorado.gov/sales-tax-guide',
        ],
        'renewal' => [
            'required' => true,
            'summary' => 'Licenses expire December 31 of each odd-numbered year and are renewed for two years at $16 per location. The current period began January 1, 2026 and runs to December 31, 2027. Renew in Revenue Online, by EFT, or on paper form DR 0594.',
            'cite' => 'https://tax.colorado.gov/renew-your-sales-tax-license',
        ],
        'timing' => [
            'online' => 'Colorado Account Number the same day; paper license mailed in 2 to 3 weeks',
            'paper' => '4 to 6 weeks by mail; license issued during the visit at a Taxpayer Service Center',
            'temporary_number' => false,
            'summary' => 'Registering at MyBiz.Colorado.gov gives the Colorado Account Number the same day, and the paper license follows in 2 to 3 weeks after the fees post. A mailed CR 0100 takes 4 to 6 weeks; an in-person application at a service center gets the license during the visit.',
            'cite' => 'CR 0100 instructions (11/22/24), https://tax.colorado.gov/sites/tax/files/documents/CR0100_2025.pdf',
        ],
        'number' => [
            'name' => 'Colorado Account Number (CAN)',
            'format' => '8 digits (from the resale certificate research; separate from the 12-digit location ID)',
            'cite' => 'https://tax.colorado.gov/renew-your-sales-tax-license',
        ],
    ],
    'nexus' => [
        'physical' => 'Any retailer making taxable retail sales in Colorado from a physical location must hold a Colorado sales tax license, with a separate license for each business location.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous or current calendar year (all retail sales into Colorado, taxable or not); collection starts the first day of the month at least 90 days after crossing the threshold in the current year',
            'effective' => '2019-06-01',
            'cite' => 'C.R.S. 39-26-102(3)(c); Colorado Sales Tax Guide, https://tax.colorado.gov/sales-tax-guide',
        ],
        'marketplace' => 'A marketplace facilitator over the $100,000 threshold collects state and state-administered local tax for its sellers, and a seller that sells only through such a marketplace may be exempt from licensing and filing (https://tax.colorado.gov/sales-tax-guide).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annual by average monthly tax collected',
        'rule' => 'Monthly in general. Quarterly if average or estimated monthly state and state-administered local sales tax collected is under $1,100 but more than $50; annual if $50 or less. The Department raises frequency each January 1 when collections grow, but lowers it only on request.',
        'due_day' => '20th of the month after the period',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'https://tax.colorado.gov/sales-tax-guide',
    ],
    'rates' => [
        'state_rate_pct' => 2.9,
        'local' => 'State-administered city, county and special district taxes are reported on the state return (DR 0100); self-collected home-rule city taxes are reported separately. Rates are listed in publication DR 1002.',
        'sourcing' => 'destination: a sale is sourced where the buyer takes possession',
        'cite' => 'https://tax.colorado.gov/sales-tax-guide',
    ],
    'local_registration' => [
        'required' => true,
        'summary' => 'Yes, in home-rule self-collecting cities. The Department of Revenue does not license or collect for home-rule cities that administer their own sales tax, including Arvada, Aurora, Boulder, Colorado Springs, Denver, Fort Collins, Lakewood, Longmont and Loveland. A seller with taxable sales in one of these cities must get that city\'s sales tax license and file with the city. Many of them accept returns through the state\'s Sales & Use Tax System (SUTS), but the city license is still separate; the Department\'s DR 1002 lists contact details for each one.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => null,
            'cite' => null,
        ],
        'late_filing' => [
            'summary' => 'Failure to file or pay on time: 10% of the unpaid tax plus 0.5% for each month it stays unpaid, up to 18% in total, plus interest. Failing to file electronically when required costs the greater of $50 or 5% of the tax.',
            'cite' => 'https://tax.colorado.gov/sales-tax-guide',
        ],
    ],
    'connected' => [
        'covers' => [
            'state sales tax',
            'state-administered local sales taxes',
            'wage withholding (same CR 0100 application)',
        ],
        'prerequisites' => 'An FEIN for any entity other than a sole proprietor without one; trade names are registered with the Colorado Secretary of State. Mailed and in-person applications need a valid photo ID.',
        'cite' => 'CR 0100 instructions (11/22/24), https://tax.colorado.gov/sites/tax/files/documents/CR0100_2025.pdf',
    ],
    'facts' => [
        [
            'text' => 'All prior Colorado sales tax licenses expired December 31, 2025; renewed licenses run January 1, 2026 to December 31, 2027, at $16 per location.',
            'source_url' => 'https://tax.colorado.gov/renew-your-sales-tax-license',
        ],
        [
            'text' => 'E-filing becomes mandatory in steps: from January 2026 for retailers with prior-year gross sales of $500,000 or more, from January 2027 for $50,000 or more, and for all returns reporting January 2028 or later.',
            'source_url' => 'https://tax.colorado.gov/sales-tax-guide',
        ],
        [
            'text' => 'The Colorado retail delivery fee rose from $0.28 to $0.31 per qualifying delivery on July 1, 2026. Retailers with $500,000 or less in prior-year Colorado retail sales are exempt.',
            'source_url' => 'https://www.salestaxinstitute.com/resources/colorado-increases-retail-delivery-fee-rate-effective-july-1-2026',
        ],
        [
            'text' => 'Colorado dropped any transaction count; the remote seller test is $100,000 in retail sales, and all retail sales count, even exempt ones.',
            'source_url' => 'https://tax.colorado.gov/sales-tax-guide',
        ],
        [
            'text' => 'The $50 deposit is required only once per account; additional locations under the same account number pay only the $16 fee.',
            'source_url' => 'https://tax.colorado.gov/sites/tax/files/documents/CR0100_2025.pdf',
        ],
    ],
    'state_notes' => 'In Colorado, you need a Colorado Sales Tax License. The Colorado Department of Revenue issues it. Apply online at MyBiz.Colorado.gov, or on Form CR 0100, the Colorado Sales Tax and Withholding Account Application. Online, you get your Colorado Account Number the same day, and the paper license arrives in 2 to 3 weeks. A mailed form takes 4 to 6 weeks. The license costs $16 per location, plus a one-time $50 deposit. The state refunds the deposit after you have paid $50 in state sales tax. Every license expires on December 31 of odd-numbered years and must be renewed for $16. The current period ends December 31, 2027. Most businesses file monthly. Smaller ones can file quarterly or yearly. Returns are due on the 20th, and you must file even with no sales. The state rate is 2.9%, plus local taxes based on where the buyer receives the goods. The one mistake to avoid: thinking the state license covers every city. Many home-rule cities, including Denver, Aurora and Colorado Springs, collect their own sales tax. If you sell in one, you also need that city\'s license.',
    'sources' => [
        [
            'title' => 'Colorado DOR: Sales Tax Guide',
            'url' => 'https://tax.colorado.gov/sales-tax-guide',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Colorado DOR: Renew Your Sales Tax License',
            'url' => 'https://tax.colorado.gov/renew-your-sales-tax-license',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Colorado DOR: CR 0100 instructions (11/22/24)',
            'url' => 'https://tax.colorado.gov/sites/tax/files/documents/CR0100_2025.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Colorado DOR: SUTS Participating Jurisdictions',
            'url' => 'https://tax.colorado.gov/SUTS-Jurisdictions',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales Tax Institute: Colorado increases retail delivery fee rate, effective July 1, 2026',
            'url' => 'https://www.salestaxinstitute.com/resources/colorado-increases-retail-delivery-fee-rate-effective-july-1-2026',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Colorado DOR press release: Retail delivery fees no longer apply to qualified small, new businesses',
            'url' => 'https://tax.colorado.gov/press-release/retail-delivery-fees-no-longer-apply-to-qualified-small-new-businesses',
            'accessed' => '2026-10-02',
        ],
    ],
];
