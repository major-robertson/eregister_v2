<?php

/*
 * Kentucky: Kentucky Department of Revenue, Sales and Use Tax Permit.
 * Researched 2026-10-02 from revenue.ky.gov, taxanswers.ky.gov,
 * apps.legislature.ky.gov, taxjar.com. Generated once from the EREG-13
 * sales tax research; edit this file directly from now on.
 */

return [
    'state' => 'KY',
    'name' => 'Kentucky',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Kentucky Department of Revenue',
        'short' => 'DOR',
        'url' => 'https://revenue.ky.gov/',
    ],
    'registration' => [
        'term' => 'Sales and Use Tax Permit',
        'portal' => [
            'name' => 'MyTaxes.ky.gov (New Business Registration; formerly reached through Kentucky Business One Stop)',
            'url' => 'https://mytaxes.ky.gov/',
        ],
        'form' => [
            'number' => '10A100',
            'title' => 'Kentucky Tax Registration Application',
            'pdf_url' => 'https://revenue.ky.gov/Forms/10A100(P)(4-25)_FINAL_locked%20Fill-in.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No registration fee is listed on the Kentucky Tax Registration Application or DOR registration pages for a sales and use tax account.',
            'cite' => 'Form 10A100(P) (4-25); cross-check TaxJar Kentucky guide',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'There is no expiration date. Sales and use tax account numbers stay active until the retailer cancels them.',
            'cite' => 'https://taxanswers.ky.gov/Sales-and-Excise-Taxes/Pages/Remote-Retailers-Marketplace-Providers-FAQs.aspx',
        ],
        'timing' => [
            'online' => 'account numbers almost immediately; 2 to 3 business days if held for DOR review',
            'paper' => '5 to 10 business days',
            'temporary_number' => false,
            'summary' => 'Online registrations usually return tax account numbers almost instantly; applications work-listed for review are completed within 2 to 3 business days. Fully completed paper applications are processed in 5 to 10 business days, barring seasonal workload.',
            'cite' => 'Form 10A100(P) (4-25) instructions; https://taxanswers.ky.gov/Sales-and-Excise-Taxes/Pages/My-business-has-NO-tax-accounts-with-DOR.aspx',
        ],
        'number' => [
            'name' => 'Kentucky sales and use tax account number',
            'format' => null,
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'A retailer that engages in business in Kentucky, such as having a place of business, employees, agents or property in the state, or selling taxable goods or the services added in 2023 and later, must hold a permit under KRS 139.240.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous or current calendar year (gross receipts from sales into Kentucky)',
            'effective' => '2026-08-01',
            'cite' => 'KRS 139.340 as amended by 2026 HB 757; DOR SSUTA recertification letter (7-29-26). Before Aug. 1, 2026 the test was $100,000 or 200 transactions (effective July 1, 2018).',
        ],
        'marketplace' => 'Since July 1, 2019, a marketplace provider over the remote seller threshold must register and collect Kentucky tax on all sales through its marketplace; from Aug. 1, 2026 the threshold is $100,000 only (KRS 139.450, as amended by 2026 HB 757).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annual',
        'rule' => 'Returns are due monthly, but DOR allows quarterly or annual filing based on the amount of tax reported annually. DOR reviews each account and adjusts frequencies each June. Since May 2, 2025, sales tax returns must be filed and paid online through MyTaxes.',
        'due_day' => '20th of the month after the period',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'https://taxanswers.ky.gov/Sales-and-Excise-Taxes/Pages/Sales-and-Excise-Tax-FAQs.aspx; https://revenue.ky.gov/Business/Sales-Use-Tax/Pages/default.aspx',
    ],
    'rates' => [
        'state_rate_pct' => 6.0,
        'local' => 'No local sales taxes; 6% statewide.',
        'sourcing' => 'destination (Streamlined Sales Tax member)',
        'cite' => 'https://revenue.ky.gov/Business/Sales-Use-Tax/Pages/default.aspx',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'No local sales tax and no local sales tax registration. (Local transient room taxes and occupational license taxes are separate local matters.)',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Engaging in business as a seller without the required permit, or after it is suspended, is a Class B misdemeanor for the business and each corporate officer involved.',
            'cite' => 'KRS 139.990; KRS 139.240',
        ],
        'late_filing' => [
            'summary' => '2% of the tax due for each 30 days or part of 30 days a return is late, up to 20%, minimum $10. A separate late-payment penalty applies on the same scale.',
            'cite' => 'KRS 131.180; https://revenue.ky.gov/Collections/Pages/Penalties-Interest-and-Fees.aspx',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales and use tax',
            'employer\'s withholding tax',
            'transient room tax',
            'motor vehicle tire fee',
            'consumer\'s use tax',
            'corporation income tax',
            'limited liability entity tax',
            'utility gross receipts license tax',
        ],
        'prerequisites' => 'FEIN (not required for sole proprietors, who use an SSN); Kentucky Secretary of State registration for entities.',
        'cite' => 'Form 10A100(P) (4-25) instructions',
    ],
    'facts' => [
        [
            'text' => '2026 House Bill 757 removed the 200-transaction test for remote retailers and marketplace providers effective Aug. 1, 2026, leaving only the $100,000 gross receipts test. It also taxed data brokering services and pay phone receipts from that date.',
            'source_url' => 'https://revenue.ky.gov/Business/Sales-Use-Tax/Documents/KY%20SSUTA%20Recertification%20-%207-29-26.pdf',
        ],
        [
            'text' => 'Since May 2, 2025, Kentucky sales and excise tax returns must be filed and paid online.',
            'source_url' => 'https://revenue.ky.gov/News/Pages/Online-Filing-and-Payment-Mandate-for-Sales-and-Excise-Tax-Returns.aspx',
        ],
        [
            'text' => 'House Bill 8 (2023) added many services to the Kentucky sales tax base at the 6% rate.',
            'source_url' => 'https://revenue.ky.gov/Business/Sales-Use-Tax/Pages/default.aspx',
        ],
        [
            'text' => 'Kentucky charges admissions tax on initiation, membership and monthly fees for using a facility.',
            'source_url' => 'https://revenue.ky.gov/Forms/10A100(P)(4-25)_FINAL_locked%20Fill-in.pdf',
        ],
        [
            'text' => 'Kentucky is a Streamlined Sales Tax member state.',
            'source_url' => 'https://revenue.ky.gov/Business/Sales-Use-Tax/Pages/Streamlined-Sales-Tax.aspx',
        ],
    ],
    'state_notes' => 'In Kentucky, you need a Sales and Use Tax Permit from the Kentucky Department of Revenue (DOR). You apply online through MyTaxes.ky.gov using New Business Registration. You can also mail Form 10A100, the Kentucky Tax Registration Application. DOR lists no fee for a sales tax account. Online applicants usually get account numbers almost right away, or within 2 to 3 business days if DOR reviews the file. Paper applications take 5 to 10 business days. The permit does not expire. It stays active until you cancel it. Kentucky has a flat 6% rate and no local sales tax. Since 2023, many services are taxable too, so check your service list before you assume you are exempt. Returns are due by the 20th of the month after each period. DOR starts you on a schedule and may move you to quarterly or annual filing each June based on your tax. Since May 2025, you must file and pay online. Remote sellers must register once Kentucky sales pass $100,000 in the current or prior year; the 200-transaction test ended Aug. 1, 2026. The mistake to avoid: selling before your permit is active. Doing business without a permit is a misdemeanor in Kentucky.',
    'sources' => [
        [
            'title' => 'Kentucky DOR: Sales & Use Tax',
            'url' => 'https://revenue.ky.gov/Business/Sales-Use-Tax/Pages/default.aspx',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Form 10A100(P) Kentucky Tax Registration Application (4-25)',
            'url' => 'https://revenue.ky.gov/Forms/10A100(P)(4-25)_FINAL_locked%20Fill-in.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TaxAnswers: My Business Has No Tax Accounts With DOR',
            'url' => 'https://taxanswers.ky.gov/Sales-and-Excise-Taxes/Pages/My-business-has-NO-tax-accounts-with-DOR.aspx',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TaxAnswers: Remote Retailers and Marketplace Providers',
            'url' => 'https://taxanswers.ky.gov/Sales-and-Excise-Taxes/Pages/Remote-Retailers-Marketplace-Providers-FAQs.aspx',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TaxAnswers: Sales and Excise Tax FAQs',
            'url' => 'https://taxanswers.ky.gov/Sales-and-Excise-Taxes/Pages/Sales-and-Excise-Tax-FAQs.aspx',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Kentucky SSUTA Recertification letter (7-29-26)',
            'url' => 'https://revenue.ky.gov/Business/Sales-Use-Tax/Documents/KY%20SSUTA%20Recertification%20-%207-29-26.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => '26RS HB 757',
            'url' => 'https://apps.legislature.ky.gov/record/26rs/hb757.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Kentucky DOR: Penalties, Interest and Fees',
            'url' => 'https://revenue.ky.gov/Collections/Pages/Penalties-Interest-and-Fees.aspx',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'KRS Chapter 139',
            'url' => 'https://apps.legislature.ky.gov/law/statutes/chapter.aspx?id=37663',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TaxJar Kentucky sales tax guide (cross-check)',
            'url' => 'https://www.taxjar.com/sales-tax/states/kentucky',
            'accessed' => '2026-10-02',
        ],
    ],
];
