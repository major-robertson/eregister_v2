<?php

/*
 * Idaho: Idaho State Tax Commission, Seller's Permit. Researched
 * 2026-10-02 from tax.idaho.gov, legislature.idaho.gov. Generated once
 * from the EREG-13 sales tax research; edit this file directly from now
 * on.
 */

return [
    'state' => 'ID',
    'name' => 'Idaho',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Idaho State Tax Commission',
        'short' => 'the Tax Commission',
        'url' => 'https://tax.idaho.gov/',
    ],
    'registration' => [
        'term' => 'Seller\'s Permit',
        'portal' => [
            'name' => 'Idaho Business Registration (IBR)',
            'url' => 'https://www2.labor.idaho.gov/IBRS',
        ],
        'form' => [
            'number' => 'EFO00147',
            'title' => 'Idaho Business Registration paper application (print and mail)',
            'pdf_url' => 'https://tax.idaho.gov/document-mngr/forms_EFO00147',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'The Tax Commission\'s registration page lists no fee for a seller\'s permit.',
            'cite' => 'https://tax.idaho.gov/online-services/business-registration/',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'No renewal was found. The Tax Commission cancels a permit after 12 consecutive months of $0 sales returns.',
            'cite' => 'https://tax.idaho.gov/taxes/sales-use/stfiling/',
        ],
        'timing' => [
            'online' => '10 to 15 business days',
            'paper' => 'up to four weeks',
            'temporary_number' => false,
            'summary' => 'The Tax Commission says you will get your permit in 10 to 15 business days when you apply online, and up to four weeks when you mail the application. Temporary seller\'s permits are available for short events.',
            'cite' => 'https://tax.idaho.gov/online-services/business-registration/',
        ],
        'number' => [
            'name' => 'Idaho seller\'s permit number',
            'format' => '9 digits, e.g. 000123456 (from the resale certificate research)',
            'cite' => 'https://tax.idaho.gov/validseller',
        ],
    ],
    'nexus' => [
        'physical' => 'Any business selling taxable goods or services in Idaho from an Idaho presence needs a seller\'s permit before its first sale; Idaho residents with under $5,000 in calendar-year sales qualify for a small seller exemption.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'current or previous calendar year (sales into Idaho, including sales made through a marketplace facilitator)',
            'effective' => '2019-06-01',
            'cite' => 'Idaho Code 63-3611 (H 259, 2019); https://tax.idaho.gov/taxes/sales-use/guides-for-certain-groups/online-sellers/online-guide/',
        ],
        'marketplace' => 'From June 1, 2019, a marketplace facilitator whose own plus third-party Idaho sales exceed $100,000 in the current or previous year must collect Idaho tax, with separate seller\'s permits for its own sales and its third-party sales (https://tax.idaho.gov/taxes/sales-use/guides-for-certain-groups/online-sellers/online-guide/).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly, semiannual or annual, set by the Tax Commission',
        'rule' => 'Most retailers file monthly. Retailers who owe less than $750 tax per quarter file quarterly. Distributors or wholesalers with only a few sales can apply to file semiannually or annually.',
        'due_day' => '20th of the month after the period (semiannual July 20 and January 20; annual January 20)',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'https://tax.idaho.gov/taxes/sales-use/stfiling/',
    ],
    'rates' => [
        'state_rate_pct' => 6.0,
        'local' => 'No general county or city sales tax collected by the state. About two dozen resort cities levy their own local option sales taxes, and some auditorium districts tax lodging.',
        'sourcing' => 'single 6% state rate',
        'cite' => 'https://tax.idaho.gov/taxes/sales-use/sales-tax/local-sales-tax/',
    ],
    'local_registration' => [
        'required' => true,
        'summary' => 'Only in resort cities with a local option sales tax. The Tax Commission lists Bellevue, Bonners Ferry, Cascade, Crouch, Donnelly, Driggs, Hailey, Harrison, Irwin, Kellogg, Ketchum, Lava Hot Springs, Mackay, McCall, Ponderay, Riggins, Salmon, Sandpoint, Stanley, Sun Valley, Swan Valley, Tetonia and Victor, and tells sellers to contact each city for its rules. Some cities tax all sales; others only lodging, liquor by the drink and restaurant food. Some auditorium district permits are issued through Idaho Business Registration.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => null,
            'cite' => null,
        ],
        'late_filing' => [
            'summary' => 'Late filing: 5% of the tax due per month, up to 25%. Late payment: 0.5% per month, up to 25%. Each has a $10 minimum. Interest runs from the original due date.',
            'cite' => 'https://tax.idaho.gov/taxes/sales-use/stfiling/',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales and use tax',
            'travel and convention tax',
            'some auditorium district taxes',
            'income tax withholding (if you have employees)',
            'Department of Labor and Industrial Commission accounts',
        ],
        'prerequisites' => 'Register the business with the Idaho Secretary of State and get an EIN if required; have SSNs or EINs for all owners, partners and officers.',
        'cite' => 'https://tax.idaho.gov/online-services/business-registration/',
    ],
    'facts' => [
        [
            'text' => 'Idaho residents with cumulative calendar-year sales under $5,000 are exempt as small sellers; once sales pass $5,000 they must start collecting immediately and apply for a permit within 30 days.',
            'source_url' => 'https://tax.idaho.gov/taxes/sales-use/stfiling/',
        ],
        [
            'text' => 'A return is required every period even with no sales; after 12 straight months of $0 returns the Tax Commission cancels the permit.',
            'source_url' => 'https://tax.idaho.gov/taxes/sales-use/stfiling/',
        ],
        [
            'text' => 'Sellers do not report sales made through registered marketplace facilitators or short-term rental marketplaces that already report them on Form 850.',
            'source_url' => 'https://tax.idaho.gov/taxes/sales-use/stfiling/',
        ],
        [
            'text' => 'A marketplace facilitator must hold two seller\'s permits in Idaho: one for its own sales and one for third-party sales.',
            'source_url' => 'https://tax.idaho.gov/taxes/sales-use/guides-for-certain-groups/online-sellers/online-guide/',
        ],
        [
            'text' => 'Buyers use Form ST-101, Sales Tax Resale or Exemption Certificate, for resale purchases.',
            'source_url' => 'https://tax.idaho.gov/taxes/sales-use/guides-for-certain-groups/wholesalers/selling-goods/',
        ],
    ],
    'state_notes' => 'In Idaho, you need a Seller\'s Permit from the Idaho State Tax Commission. You apply through Idaho Business Registration (IBR), online or on the paper application. Register your business with the Idaho Secretary of State first, and get an EIN if you need one. The Tax Commission lists no fee. It says you will get your permit in 10 to 15 business days online, or up to four weeks by mail. The same registration can set up travel and convention tax and withholding accounts. Most retailers file monthly, by the 20th of the next month. Retailers who owe less than $750 a quarter file quarterly. You must file even if you made no sales; after 12 months of zero returns, your permit is cancelled. The state rate is 6%. The one mistake to avoid: forgetting resort city taxes. About two dozen cities, including Ketchum, Sun Valley, McCall and Sandpoint, have their own local sales tax. The Tax Commission tells sellers to contact those cities directly about registering and paying.',
    'sources' => [
        [
            'title' => 'Idaho State Tax Commission: Getting Tax Permits',
            'url' => 'https://tax.idaho.gov/online-services/business-registration/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Idaho State Tax Commission: Sales Tax Filing and Paying',
            'url' => 'https://tax.idaho.gov/taxes/sales-use/stfiling/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Idaho State Tax Commission: Online Sellers Guide',
            'url' => 'https://tax.idaho.gov/taxes/sales-use/guides-for-certain-groups/online-sellers/online-guide/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Idaho State Tax Commission: Local Sales Tax',
            'url' => 'https://tax.idaho.gov/taxes/sales-use/sales-tax/local-sales-tax/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Idaho State Tax Commission: City Sales Taxes',
            'url' => 'https://tax.idaho.gov/taxes/sales-use/sales-tax/local-sales-tax/city-sales-tax/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Idaho Legislature: H 259 (2019), marketplace facilitators',
            'url' => 'https://legislature.idaho.gov/wp-content/uploads/sessioninfo/2019/legislation/H0259E1.pdf',
            'accessed' => '2026-10-02',
        ],
    ],
];
