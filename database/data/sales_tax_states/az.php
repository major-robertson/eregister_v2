<?php

/*
 * Arizona: Arizona Department of Revenue, Transaction Privilege Tax (TPT)
 * License. Researched 2026-10-02 from azdor.gov, azleg.gov,
 * salestaxinstitute.com. Generated once from the EREG-13 sales tax
 * research; edit this file directly from now on.
 */

return [
    'state' => 'AZ',
    'name' => 'Arizona',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Arizona Department of Revenue',
        'short' => 'ADOR',
        'url' => 'https://azdor.gov/',
    ],
    'registration' => [
        'term' => 'Transaction Privilege Tax (TPT) License',
        'portal' => [
            'name' => 'AZTaxes.gov (also Arizona Business One Stop for new businesses)',
            'url' => 'https://www.aztaxes.gov/',
        ],
        'form' => [
            'number' => 'JT-1',
            'title' => 'Arizona Joint Tax Application for a TPT License (ADOR 10196)',
            'pdf_url' => 'https://azdor.gov/sites/default/files/2023-03/FORMS_TPT_10196_f.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 1200,
            'summary' => '$12 state fee per location, plus a municipal privilege tax license fee of up to $50 for each city or town where the business operates (set by city ordinance; many cities charge none). Remote sellers and out-of-state marketplace facilitators with no Arizona presence pay only the $12 state fee.',
            'cite' => 'A.R.S. 42-5005(A), (B), (I); https://azdor.gov/business/transaction-privilege-tax/retail-sales-subject-tpt/out-state-sellers/licensing-and-renewal',
        ],
        'renewal' => [
            'required' => true,
            'summary' => 'The license is valid for the calendar year and must be renewed every year. There is no state renewal fee, but city renewal fees of up to $50 per city are due January 1 and are late after the last business day of January (for example Phoenix $50, Scottsdale $50, Mesa $20). A license not renewed or cancelled is renewed automatically and billed the fee and penalty.',
            'cite' => 'A.R.S. 42-5005(C), (D); https://azdor.gov/transaction-privilege-tax/tpt-license/renewing-tpt-license',
        ],
        'timing' => [
            'online' => 'License number the same day; certificate mailed in 7 to 10 business days',
            'paper' => '2 weeks by mail; same day in person',
            'temporary_number' => false,
            'summary' => 'ADOR says an AZTaxes.gov application gets a TPT license number the same day, with the license certificate mailed in 7 to 10 business days. A mailed JT-1 takes 2 weeks and an in-person JT-1 is same day. Licenses are not delivered until fees are paid in full.',
            'cite' => 'https://azdor.gov/transaction-privilege-tax/tpt-license/applying-tpt-license',
        ],
        'number' => [
            'name' => 'TPT license number',
            'format' => '8 digits (from the resale certificate research; not re-checked on an ADOR page)',
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'Any business with gross receipts from a taxable activity in Arizona (such as retail sales from an Arizona location) must hold a TPT license before engaging in business (A.R.S. 42-5005(A)).',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous or current calendar year (gross sales from direct sales into Arizona; retail classification only)',
            'effective' => '2021-01-01',
            'cite' => 'A.R.S. 42-5043; https://azdor.gov/business/transaction-privilege-tax/retail-sales-subject-tpt/out-state-sellers/economic-threshold',
        ],
        'marketplace' => 'A marketplace facilitator that facilitates more than $100,000 of sales into Arizona in the current or previous year must be licensed and pay TPT on those sales; sales made through a facilitator do not count toward the remote seller\'s own threshold (https://azdor.gov/business/transaction-privilege-tax/retail-sales-subject-tpt/out-state-sellers/economic-threshold).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly, annual or seasonal by estimated combined liability',
        'rule' => 'Annual if estimated combined state, county and city TPT is under $2,000 a year; quarterly for $2,000 to $8,000; monthly above $8,000; seasonal for 8 months or less of activity.',
        'due_day' => '20th of the month after the period; treated as timely if received by the last business day of that month (electronic) or the second-to-last business day (paper). Penalties run from the 20th if late.',
        'zero_return_required' => true,
        'prepayments' => 'Annual estimated payment due in June (statutory date June 20) if combined TPT, telecommunications excise and county excise liability for the prior calendar year was $4,100,000 or more.',
        'cite' => 'https://azdor.gov/transaction-privilege-tax/tpt-license/tpt-filing-frequency; https://azdor.gov/business/transaction-privilege-tax/electronic-annual-estimated-tax-payment',
    ],
    'rates' => [
        'state_rate_pct' => 5.6,
        'local' => 'County and city TPT rates are added to the 5.6% state retail rate; all are reported to ADOR on one return.',
        'sourcing' => 'origin for in-state retail sales (per secondary sources; not confirmed on an ADOR page)',
        'cite' => 'https://azdor.gov/business/transaction-privilege-tax',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'No separate city registration. Since January 1, 2017, ADOR licenses and collects TPT for all 91 cities and towns, including the former non-program cities (Apache Junction, Avondale, Chandler, Douglas, Flagstaff, Glendale, Mesa, Nogales, Peoria, Phoenix, Prescott, Scottsdale, Tempe and Tucson). The city license is still a separate municipal privilege tax license, but it is issued by ADOR on the same JT-1 or AZTaxes application, with a fee of up to $50 per city (A.R.S. 42-5005(B)).',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Engaging in business without a TPT license violates A.R.S. 42-5005 and is a class 3 misdemeanor. Continuing in business without renewing a city license adds a civil penalty of up to $25 per city (ADOR applies 50% of the city renewal fee).',
            'cite' => 'A.R.S. 42-5005(A), (Q); A.R.S. 42-1125(R); https://azdor.gov/transaction-privilege-tax/tpt-license/renewing-tpt-license',
        ],
        'late_filing' => [
            'summary' => 'Late filing: 4.5% of the tax per month or part of a month, up to 25%. Late payment: 0.5% per month, up to 10%. Combined late-filing and late-payment penalties are capped at 25%.',
            'cite' => 'A.R.S. 42-1125(A), (D)',
        ],
    ],
    'connected' => [
        'covers' => [
            'transaction privilege tax',
            'use tax',
            'city and county privilege taxes',
            'employer withholding',
            'unemployment insurance (DES)',
        ],
        'prerequisites' => 'An EIN, or an SSN for a sole proprietor without employees; single-member LLCs must have an EIN. Only a person legally responsible for the business may sign.',
        'cite' => 'https://azdor.gov/transaction-privilege-tax/tpt-license/applying-tpt-license; https://azdor.gov/forms/tpt-forms/joint-tax-application-tpt-license',
    ],
    'facts' => [
        [
            'text' => 'Arizona\'s tax is a transaction privilege tax on the seller for the privilege of doing business, not a sales tax on the buyer; it is commonly passed on to customers.',
            'source_url' => 'https://azdor.gov/business/transaction-privilege-tax',
        ],
        [
            'text' => 'From January 1, 2025, cities may no longer tax residential rentals (30 days or more), under SB 1131 (Laws 2023, ch. 204). Owners stopped collecting city residential rental TPT for periods after December 31, 2024.',
            'source_url' => 'https://azdor.gov/business/transaction-privilege-tax/residential-rental-guidelines',
        ],
        [
            'text' => 'The remote seller threshold stepped down from $200,000 (2019) and $150,000 (2020) to $100,000 for 2021 and later, and applies only to the retail classification.',
            'source_url' => 'https://azdor.gov/business/transaction-privilege-tax/retail-sales-subject-tpt/out-state-sellers/economic-threshold',
        ],
        [
            'text' => 'A business with several locations needs a TPT license for each location or business name, even if it reports them on one consolidated return; each new location costs another $12.',
            'source_url' => 'https://www.azleg.gov/ars/42/05005.htm',
        ],
        [
            'text' => 'ADOR revokes no license automatically, but it may revoke one after 13 consecutive months of missed returns (A.R.S. 42-5005(P)).',
            'source_url' => 'https://www.azleg.gov/ars/42/05005.htm',
        ],
    ],
    'state_notes' => 'Arizona does not have a sales tax permit. It has a Transaction Privilege Tax (TPT) License, issued by the Arizona Department of Revenue (ADOR). You can apply online at AZTaxes.gov, through Arizona Business One Stop, or on the paper Joint Tax Application, Form JT-1. Online, ADOR issues your license number the same day and mails the certificate in 7 to 10 business days. A mailed JT-1 takes about 2 weeks. The state fee is $12 per location. Each city where you do business can add a city license fee of up to $50, and ADOR collects it on the same application. You do not register with cities separately. The license runs for the calendar year. You must renew it every year by January 1; the state part is free, but city renewal fees apply. ADOR sets your filing schedule by your expected tax: annual under $2,000 a year, quarterly from $2,000 to $8,000, monthly above that. Returns are due on the 20th. The one mistake to avoid: skipping a return because you had no sales. ADOR requires a return every period, even when you owe $0.',
    'sources' => [
        [
            'title' => 'ADOR: Applying for a TPT License',
            'url' => 'https://azdor.gov/transaction-privilege-tax/tpt-license/applying-tpt-license',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ADOR: Joint Tax Application for a TPT License (JT-1)',
            'url' => 'https://azdor.gov/forms/tpt-forms/joint-tax-application-tpt-license',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ADOR: Renewing a TPT License',
            'url' => 'https://azdor.gov/transaction-privilege-tax/tpt-license/renewing-tpt-license',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ADOR: Out-of-state sellers, licensing and renewal',
            'url' => 'https://azdor.gov/business/transaction-privilege-tax/retail-sales-subject-tpt/out-state-sellers/licensing-and-renewal',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ADOR: Economic Threshold',
            'url' => 'https://azdor.gov/business/transaction-privilege-tax/retail-sales-subject-tpt/out-state-sellers/economic-threshold',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ADOR: TPT Filing Frequency',
            'url' => 'https://azdor.gov/transaction-privilege-tax/tpt-license/tpt-filing-frequency',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ADOR: Electronic Annual Estimated Tax Payment',
            'url' => 'https://azdor.gov/business/transaction-privilege-tax/electronic-annual-estimated-tax-payment',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'A.R.S. 42-5005, TPT and municipal privilege tax licenses',
            'url' => 'https://www.azleg.gov/ars/42/05005.htm',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'A.R.S. 42-1125, Civil penalties',
            'url' => 'https://www.azleg.gov/ars/42/01125.htm',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ADOR: Residential Rental Guidelines',
            'url' => 'https://azdor.gov/business/transaction-privilege-tax/residential-rental-guidelines',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales Tax Institute: Centralized licensing and reporting of Arizona TPT begins January 1, 2017',
            'url' => 'https://www.salestaxinstitute.com/resources/centralized-licensing-and-reporting-arizona-tpt-begins-january-1-2017',
            'accessed' => '2026-10-02',
        ],
    ],
];
