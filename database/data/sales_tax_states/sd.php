<?php

/*
 * South Dakota: South Dakota Department of Revenue, Sales Tax License.
 * Researched 2026-10-02 from dor.sd.gov, sdlegislature.gov,
 * salestaxinstitute.com. Generated once from the EREG-13 sales tax
 * research; edit this file directly from now on.
 */

return [
    'state' => 'SD',
    'name' => 'South Dakota',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'South Dakota Department of Revenue',
        'short' => 'the Department of Revenue',
        'url' => 'https://dor.sd.gov/',
    ],
    'registration' => [
        'term' => 'Sales Tax License',
        'portal' => [
            'name' => 'South Dakota Tax License Application (online); returns are filed in EPath',
            'url' => 'https://apps.sd.gov/rv23cedar/main/main.aspx',
        ],
        'form' => [
            'number' => null,
            'title' => 'Online Tax License Application (sales, use, contractor\'s excise, manufacturer, wholesaler and motor fuel licenses)',
            'pdf_url' => null,
            'online_only' => null,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'There is no fee for a sales or contractor\'s excise tax license. A license card is issued once the license is approved.',
            'cite' => 'SD DOR Tax Fact, License Requirements for Sales, Use & Contractor\'s Excise Tax (Jan. 2019), https://dor.sd.gov/media/1mpcmi1y/tax-fact-license-requirements.pdf',
        ],
        'renewal' => [
            'required' => null,
            'summary' => null,
            'cite' => null,
        ],
        'timing' => [
            'online' => null,
            'paper' => null,
            'temporary_number' => null,
            'summary' => 'The Department\'s pages do not state a processing time. They say a license card is issued once the license is approved.',
            'cite' => 'https://dor.sd.gov/media/1mpcmi1y/tax-fact-license-requirements.pdf',
        ],
        'number' => [
            'name' => 'South Dakota sales tax license number',
            'format' => '8 digits followed by a two-letter license type (e.g. ST); may be written with or without dashes',
            'cite' => 'Carried from resale research of 2026-10-01 (https://dor.sd.gov/businesses/taxes/sales-use-tax/); not re-verified on a DOR page in this pass',
        ],
    ],
    'nexus' => [
        'physical' => 'Any retailer with a physical presence in South Dakota that sells, rents or leases products or services (including products delivered electronically) in the state must hold a sales tax license, one per business location unless a statewide license is approved (SDCL 10-45-24).',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous or current calendar year (gross revenue must exceed $100,000); register by the first day of the month that starts at least 30 days after the threshold is met',
            'effective' => '2023-07-01',
            'cite' => 'SDCL 10-64-2 (as amended by SL 2023, ch 38, SB 30, which removed the 200-transaction test effective July 1, 2023); SD DOR Remote Seller Bulletin (Aug. 2025)',
        ],
        'marketplace' => 'Since March 1, 2019 a marketplace provider must be licensed and remit tax on all sales it facilitates into South Dakota if it is a remote seller over the threshold, facilitates sales for one seller over the threshold, or facilitates sales for sellers whose combined sales exceed it (SDCL ch. 10-65; SD DOR Sales and Use Tax Guide, Jan. 2026).',
    ],
    'filing' => [
        'frequencies' => 'monthly by default; the Department may assign another period (bimonthly, semiannual and others are used)',
        'rule' => 'SDCL 10-45-27.3 makes monthly the default and lets the Secretary require or allow another reporting period; the dollar thresholds for each period are not published on the pages reviewed.',
        'due_day' => '20th of the month after the period for returns; electronic payments by the 25th; paper returns and payments by the 20th',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'SDCL 10-45-27.3; SD DOR Sales and Use Tax Guide (Jan. 2026), p. 14: a business must file a return each reporting period even if it did not conduct business',
    ],
    'rates' => [
        'state_rate_pct' => 4.2,
        'local' => 'Municipal sales and use tax of 1% to 2% in cities that impose it, plus a municipal gross receipts tax of up to 1% on lodging, alcohol, eating establishments and admissions; all reported on the state return',
        'sourcing' => 'destination: tax applies where the customer receives the product or service',
        'cite' => 'SDCL 10-45-2 (4.2% until June 30, 2027, then 4.5%); SD DOR Sales and Use Tax Guide (Jan. 2026), pp. 4-5, 8',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'Municipal taxes are administered by the Department of Revenue and reported on the same state return; no separate city license was found for sales tax.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Operating a taxable business without a license is a Class 1 misdemeanor (up to $1,000 and/or one year in jail). Continuing after the Department gives notice that a license is required, or after a license is revoked, can be a Class 6 felony.',
            'cite' => 'SD DOR Tax Fact, License Requirements (Jan. 2019); SD DOR Sales and Use Tax Guide (Jan. 2026), p. 17',
        ],
        'late_filing' => [
            'summary' => '10% of the tax, minimum $10, if the return is not filed within 30 days after the due date; assessed even if no tax is due. Interest is 1% a month (minimum $5 the first month).',
            'cite' => 'SDCL 10-59-6; SD DOR Sales and Use Tax Guide (Jan. 2026), p. 14',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'use tax',
            'contractor\'s excise tax',
            'manufacturer and wholesaler licenses',
            'motor fuel taxes',
        ],
        'prerequisites' => null,
        'cite' => 'SD DOR Sales and Use Tax Guide (Jan. 2026), p. 2: the online Tax License Application covers Contractor\'s Excise, Manufacturer, Sales, Use, Wholesaler and Motor Fuel licenses',
    ],
    'facts' => [
        [
            'text' => 'The state rate was cut from 4.5% to 4.2% on July 1, 2023 (HB 1137). The cut sunsets, and the rate returns to 4.5% on July 1, 2027 under current law.',
            'source_url' => 'https://dor.sd.gov/businesses/taxes/sales-use-tax/2023-legislative-updates/',
        ],
        [
            'text' => 'South Dakota dropped the 200-transaction test for remote sellers on July 1, 2023 (SB 30). Only gross sales over $100,000 now count.',
            'source_url' => 'https://dor.sd.gov/businesses/taxes/sales-use-tax/2023-legislative-updates/',
        ],
        [
            'text' => 'Sales tax applies to the sale of services as well as goods, unless a service is specifically exempt. Construction services fall under the separate contractor\'s excise tax.',
            'source_url' => 'https://dor.sd.gov/media/kavh1fzg/2026-1_sales-use-tax-guide.pdf',
        ],
        [
            'text' => 'South Dakota is a Streamlined Sales Tax member state. Sellers can obtain a South Dakota license through the Streamlined registration system.',
            'source_url' => 'https://dor.sd.gov/businesses/taxes/sales-use-tax/',
        ],
        [
            'text' => 'A business with several South Dakota locations needs a license for each one, unless the Department approves a statewide license for consolidated filing.',
            'source_url' => 'https://dor.sd.gov/media/1mpcmi1y/tax-fact-license-requirements.pdf',
        ],
    ],
    'state_notes' => 'In South Dakota the permit is called a sales tax license. The South Dakota Department of Revenue issues it. You apply online through the Department\'s Tax License Application at sd.gov/taxapp. Sellers registering in several states can apply through the Streamlined Sales Tax system instead. There is no fee. You need a license for each business location unless the Department approves a statewide license. You need a license if you have a physical presence in South Dakota. A seller with no physical presence needs one once its gross sales into the state pass $100,000 in the current or previous calendar year. Register by the first day of the month that starts at least 30 days after you pass that mark. Most products and services are taxed. The state rate is 4.2% until June 30, 2027, when it is set to return to 4.5%. Cities add their own tax, which you report on the same state return. The Department sets your filing period. Returns are due on the 20th of the month after the period, and electronic payments on the 25th. File a return for every period, even if you had no sales. A late return draws a penalty of 10% of the tax, with a $10 minimum, even when no tax is due. The mistake to avoid: skipping the return in a month with no sales.',
    'sources' => [
        [
            'title' => 'SD DOR, Sales & Use Tax',
            'url' => 'https://dor.sd.gov/businesses/taxes/sales-use-tax/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'SD DOR Tax Fact: License Requirements for Sales, Use & Contractor\'s Excise Tax',
            'url' => 'https://dor.sd.gov/media/1mpcmi1y/tax-fact-license-requirements.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'SD DOR Remote Seller Bulletin (Aug. 2025)',
            'url' => 'https://dor.sd.gov/media/yh0n3oc2/remote-seller-bulletin.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'SD DOR Sales and Use Tax Guide (Jan. 2026)',
            'url' => 'https://dor.sd.gov/media/kavh1fzg/2026-1_sales-use-tax-guide.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'SD DOR 2023 Legislative Updates',
            'url' => 'https://dor.sd.gov/businesses/taxes/sales-use-tax/2023-legislative-updates/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'SD DOR Filing and Paying Taxes Online Help',
            'url' => 'https://dor.sd.gov/businesses/taxes/sales-use-tax/filing-and-paying-taxes-online-help/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'SDCL 10-64-2',
            'url' => 'https://sdlegislature.gov/Statutes/10-64-2',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'SDCL 10-45-2',
            'url' => 'https://sdlegislature.gov/Statutes/10-45-2',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'SDCL 10-45-27.3',
            'url' => 'https://sdlegislature.gov/Statutes/10-45-27.3',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'SDCL 10-59-6',
            'url' => 'https://sdlegislature.gov/Statutes/10-59-6',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales Tax Institute: South Dakota drops 200 transaction count (cross-check)',
            'url' => 'https://www.salestaxinstitute.com/resources/south-dakota-drops-200-transaction-count-from-its-economic-nexus-threshold',
            'accessed' => '2026-10-02',
        ],
    ],
];
