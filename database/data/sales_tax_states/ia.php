<?php

/*
 * Iowa: Iowa Department of Revenue, Sales and Use Tax Permit. Researched
 * 2026-10-02 from revenue.iowa.gov, legis.iowa.gov. Generated once from
 * the EREG-13 sales tax research; edit this file directly from now on.
 */

return [
    'state' => 'IA',
    'name' => 'Iowa',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Iowa Department of Revenue',
        'short' => 'IDR',
        'url' => 'https://revenue.iowa.gov/',
    ],
    'registration' => [
        'term' => 'Sales and Use Tax Permit',
        'portal' => [
            'name' => 'GovConnectIowa (Business Permit Registration)',
            'url' => 'https://revenue.iowa.gov/permits-licensing/business-permit-registration',
        ],
        'form' => [
            'number' => '78-005',
            'title' => 'Iowa Business Tax Permit Registration',
            'pdf_url' => 'https://revenue.iowa.gov/media/2337/download?inline=',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No fee. IDR says these permits are free of charge. (Separate fees apply only to permits such as the Household Hazardous Material permit.)',
            'cite' => 'https://revenue.iowa.gov/permits-licensing/business-permit-registration',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'Sales and use tax permits are valid until revoked by the Department.',
            'cite' => 'Iowa Code 423.36(5)',
        ],
        'timing' => [
            'online' => 'as early as 1 business day for the account letter',
            'paper' => null,
            'temporary_number' => true,
            'summary' => 'IDR says the letter with the account number and IDR ID can arrive in as early as one business day, but to allow up to 6 weeks for it by mail. The business may begin collecting tax immediately; its copy of the application serves as proof of registration until the account number arrives.',
            'cite' => 'https://revenue.iowa.gov/permits-licensing/business-permit-registration',
        ],
        'number' => [
            'name' => 'Iowa sales and use tax permit number',
            'format' => null,
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'A retailer with any permanent or temporary place of business, employee or other representative, or property in Iowa must hold a permit and collect Iowa tax.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'current or prior calendar year (gross revenue from sales into Iowa)',
            'effective' => '2019-07-01',
            'cite' => 'Iowa Code 423.14A; https://revenue.iowa.gov/taxes/tax-guidance/sales-use-excise-tax/remote-sellers-marketplace-facilitators',
        ],
        'marketplace' => 'Since July 1, 2019, a marketplace facilitator that makes or facilitates $100,000 or more in Iowa sales must collect state and local option sales tax on all taxable sales through its marketplace (IDR Remote Sellers & Marketplace Facilitators guidance).',
    ],
    'filing' => [
        'frequencies' => 'monthly or annual',
        'rule' => 'Less than $1,200 in sales and use tax per year: file and pay annually (due January 31). $1,200 or more per year: file and pay monthly, electronically. Left blank on the application, the frequency defaults to monthly. Rule from Senate File 2367, effective July 1, 2022. Hotel and motel, automobile rental and construction equipment taxes are always monthly.',
        'due_day' => 'last day of the month after the period (annual returns due January 31)',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'https://revenue.iowa.gov/taxes/tax-guidance/sales-use-excise-tax/sales-and-use-tax-permit-return-filing-and-payment-changes; Form 78-005 instructions',
    ],
    'rates' => [
        'state_rate_pct' => 6.0,
        'local' => '1% local option sales tax where adopted (most jurisdictions); 7% combined maximum.',
        'sourcing' => 'destination (Streamlined Sales Tax member)',
        'cite' => 'https://revenue.iowa.gov/taxes/tax-guidance/sales-use-excise-tax/sales-use-tax-guide',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'No separate local registration. Local option sales tax is collected under the state permit and reported to IDR.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Knowingly selling at retail without a permit is a serious misdemeanor; selling after a permit is revoked is an aggravated misdemeanor. Corporate officers who so act are also liable.',
            'cite' => 'Iowa Code 423.40; Iowa Code 423.36(1)',
        ],
        'late_filing' => [
            'summary' => '5% of unpaid tax for failure to file on time and 5% for failure to pay on time; one late monthly or quarterly return or payment in three years may be waived. A further $1,000 applies if a return is not filed within 90 days of written demand.',
            'cite' => 'Iowa Code 421.27(1), (2), (8)',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'use tax',
            'local option sales tax',
            'hotel and motel tax',
            'automobile rental tax',
            'withholding tax',
            'water service excise tax',
            'household hazardous materials permit',
        ],
        'prerequisites' => 'FEIN (required for withholding and recommended to have ready); owner names and SSNs for corporations and similar entities.',
        'cite' => 'https://revenue.iowa.gov/permits-licensing/business-permit-registration; Form 78-005',
    ],
    'facts' => [
        [
            'text' => 'Since July 1, 2022 (Senate File 2367), Iowa has one combined sales and use tax permit and only two filing frequencies: annual for under $1,200 in tax per year, monthly otherwise.',
            'source_url' => 'https://revenue.iowa.gov/taxes/tax-guidance/sales-use-excise-tax/sales-and-use-tax-permit-return-filing-and-payment-changes',
        ],
        [
            'text' => 'Iowa taxes software as a service and specified digital products.',
            'source_url' => 'https://revenue.iowa.gov/taxes/tax-guidance/sales-use-excise-tax/sales-use-tax-guide',
        ],
        [
            'text' => 'Iowa is a member of the Streamlined Sales Tax Governing Board.',
            'source_url' => 'https://revenue.iowa.gov/taxes/tax-guidance/sales-use-excise-tax/sales-use-tax-guide',
        ],
        [
            'text' => 'An in-state retailer needs a permit for each place of business; remote sellers get one permit regardless of locations. All locations added on one registration file a single consolidated return.',
            'source_url' => 'https://www.legis.iowa.gov/docs/ico/section/423.36.pdf',
        ],
        [
            'text' => 'Businesses selling to an Iowa state agency must hold a valid permit before the sale.',
            'source_url' => 'https://www.legis.iowa.gov/docs/ico/section/423.36.pdf',
        ],
    ],
    'state_notes' => 'In Iowa, you need a Sales and Use Tax Permit from the Iowa Department of Revenue (IDR). You can apply online through GovConnectIowa, or mail or fax Form 78-005, the Iowa Business Tax Permit Registration. Online is the fastest route. The permit is free. IDR says the letter with your account number can come in as little as one business day, but to allow up to six weeks by mail. You may start collecting tax right away, and your copy of the application serves as proof until the number arrives. The permit does not expire unless IDR revokes it. When you apply, you estimate your yearly tax. If you expect under $1,200 a year, you file once a year, by January 31. Otherwise you file monthly, by the last day of the next month. If you leave the estimate blank, you are set to monthly. The state rate is 6%, and most places add a 1% local option tax. Remote sellers must register once Iowa sales reach $100,000 in the current or prior calendar year. Iowa taxes some services and software as a service. The mistake to avoid: not filing when you had no sales. IDR requires a return every period from your start date, even with zero tax, until you cancel the account.',
    'sources' => [
        [
            'title' => 'IDR: Business Permit Registration',
            'url' => 'https://revenue.iowa.gov/permits-licensing/business-permit-registration',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'IDR: Sales & Use Tax Guide',
            'url' => 'https://revenue.iowa.gov/taxes/tax-guidance/sales-use-excise-tax/sales-use-tax-guide',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'IDR: Sales and Use Tax Permit, Return Filing, and Payment Changes',
            'url' => 'https://revenue.iowa.gov/taxes/tax-guidance/sales-use-excise-tax/sales-and-use-tax-permit-return-filing-and-payment-changes',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'IDR: Remote Sellers & Marketplace Facilitators',
            'url' => 'https://revenue.iowa.gov/taxes/tax-guidance/sales-use-excise-tax/remote-sellers-marketplace-facilitators',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Form 78-005 Iowa Business Tax Permit Registration (07/17/2026)',
            'url' => 'https://revenue.iowa.gov/media/2337/download?inline=',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Iowa Code 421.27 Penalties',
            'url' => 'https://www.legis.iowa.gov/docs/ico/section/421.27.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Iowa Code 423.36 Permits',
            'url' => 'https://www.legis.iowa.gov/docs/ico/section/423.36.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Iowa Code chapter 423 (423.40)',
            'url' => 'https://www.legis.iowa.gov/docs/ico/chapter/423.pdf',
            'accessed' => '2026-10-02',
        ],
    ],
];
