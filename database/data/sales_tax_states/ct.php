<?php

/*
 * Connecticut: Connecticut Department of Revenue Services, Sales and Use
 * Tax Permit. Researched 2026-10-02 from portal.ct.gov. Generated once
 * from the EREG-13 sales tax research; edit this file directly from now
 * on.
 */

return [
    'state' => 'CT',
    'name' => 'Connecticut',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Connecticut Department of Revenue Services',
        'short' => 'DRS',
        'url' => 'https://portal.ct.gov/drs',
    ],
    'registration' => [
        'term' => 'Sales and Use Tax Permit',
        'portal' => [
            'name' => 'myconneCT',
            'url' => 'https://portal.ct.gov/DRS-myconneCT',
        ],
        'form' => [
            'number' => 'REG-1',
            'title' => 'Business Taxes Registration Application (completed online in myconneCT)',
            'pdf_url' => null,
            'online_only' => true,
        ],
        'fee' => [
            'amount_cents' => 10000,
            'summary' => '$100 fee to obtain a Sales and Use Tax Permit, paid online by bank transfer or card at registration. A separate permit is needed for each location.',
            'cite' => 'DRS-143 flyer (08/23), https://portal.ct.gov/-/media/DRS/Businesses/DRS-143-SUT-FLYER_0823.pdf; https://portal.ct.gov/drs/sales-tax/tax-information',
        ],
        'renewal' => [
            'required' => true,
            'summary' => 'The permit expires every two years. DRS automatically mails a new permit without charge if the business has no outstanding liabilities or unfiled returns.',
            'cite' => 'DRS-143 flyer (08/23), https://portal.ct.gov/-/media/DRS/Businesses/DRS-143-SUT-FLYER_0823.pdf',
        ],
        'timing' => [
            'online' => 'permit available to print in myconneCT the next day',
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'DRS says the permit is available to print the next day in myconneCT, and a copy is also mailed.',
            'cite' => 'DRS-143 flyer (08/23), https://portal.ct.gov/-/media/DRS/Businesses/DRS-143-SUT-FLYER_0823.pdf',
        ],
        'number' => [
            'name' => 'Connecticut Tax Registration Number',
            'format' => null,
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'Anyone who sells, rents or leases goods, sells a taxable service, or operates a hotel or lodging house in Connecticut must get a permit before making any sales, regardless of volume.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => '12-month period ending September 30 before the monthly or quarterly period (both tests must be met: at least $100,000 in gross receipts and 200 or more retail sales)',
            'effective' => '2019-07-01',
            'cite' => 'Conn. Gen. Stat. 12-407(a)(12); https://portal.ct.gov/drs/businesses/new-business-resource-center/registering-with-drs',
        ],
        'marketplace' => 'Since December 1, 2018, a marketplace facilitator with at least $250,000 of facilitated sales in the prior 12 months must register (Form REG-1) and collect and remit tax on its marketplace sellers\' Connecticut sales; an in-state retailer selling only through such a facilitator must still register (OCG-8, https://portal.ct.gov/-/media/drs/publications/ocg/ocg-8.pdf; DRS-143).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annual, assigned by DRS',
        'rule' => 'Monthly if sales and use tax liability exceeds $4,000 a year, quarterly between $1,000 and $4,000, annual if under $1,000. DRS\'s 2023 new-business flyer tells new registrants to file monthly.',
        'due_day' => 'last day of the month after the period',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'IP 2002(26), https://portal.ct.gov/drs/publications/informational-publications/2002/ip-200226-q--a-on-sales-and-use-tax-for-a-new-businesses; https://portal.ct.gov/drs/sales-tax/tax-information; DRS-143 flyer',
    ],
    'rates' => [
        'state_rate_pct' => 6.35,
        'local' => 'No local sales taxes. Special state rates: 1% computer and data processing services, 2.99% vessels, 7.35% meals, 7.75% certain luxury items, 9.35% short-term vehicle rentals.',
        'sourcing' => 'single statewide rate, so in-state sourcing does not change the rate',
        'cite' => 'https://portal.ct.gov/drs/sales-tax/tax-information',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => null,
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Failing to obtain a permit can bring a fine of up to $500, up to three months in prison, or both, for each offense. A penalty of $250 applies for the first day of business without a permit and $100 for each following day.',
            'cite' => 'Conn. Gen. Stat. 12-409; https://portal.ct.gov/drs/sales-tax/tax-information',
        ],
        'late_filing' => [
            'summary' => 'Late payment or nonpayment: 15% of the tax due or $50, whichever is greater, plus interest.',
            'cite' => 'Conn. Gen. Stat. 12-419; Form O-88 instructions for OS-114, https://portal.ct.gov/-/media/drs/forms/2021/sut/o-88_0721.pdf',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales and use tax',
            'room occupancy tax',
            'admissions and dues tax',
            'income tax withholding (same myconneCT registration)',
        ],
        'prerequisites' => 'An FEIN, or the SSN of a sole proprietor; get the FEIN from the IRS first.',
        'cite' => 'https://portal.ct.gov/drs/businesses/new-business-resource-center/registering-with-drs',
    ],
    'facts' => [
        [
            'text' => 'Connecticut\'s remote seller test requires both $100,000 in gross receipts and 200 retail sales in the 12 months ending September 30, unlike most states that use either test.',
            'source_url' => 'https://portal.ct.gov/drs/businesses/new-business-resource-center/registering-with-drs',
        ],
        [
            'text' => 'Quarterly and monthly filers must file Form OS-114 and pay electronically in myconneCT unless DRS grants a waiver (Form DRSEWVR).',
            'source_url' => 'https://portal.ct.gov/-/media/drs/forms/2021/sut/o-88_0721.pdf',
        ],
        [
            'text' => 'A buyer of an existing business cannot use the seller\'s permit; it must get its own, and each location needs its own displayed permit.',
            'source_url' => 'https://portal.ct.gov/-/media/DRS/Businesses/DRS-143-SUT-FLYER_0823.pdf',
        ],
        [
            'text' => 'Computer and data processing services are taxed at a reduced 1% rate.',
            'source_url' => 'https://portal.ct.gov/drs/sales-tax/tax-information',
        ],
        [
            'text' => 'Connecticut has no local sales taxes; one statewide 6.35% general rate applies.',
            'source_url' => 'https://portal.ct.gov/drs/sales-tax/tax-information',
        ],
    ],
    'state_notes' => 'In Connecticut, you need a Sales and Use Tax Permit. The Department of Revenue Services (DRS) issues it. You register online in myconneCT; the application is Form REG-1, Business Taxes Registration Application. You need an FEIN, or your SSN if you are a sole proprietor. The fee is $100, paid when you register. DRS says the permit is ready to print in myconneCT the next day, and a copy comes by mail. You need a permit for each location, and you must have it before your first sale. The permit expires every two years. DRS mails a new one free if you have no unpaid tax or missing returns. You file Form OS-114 in myconneCT. DRS assigns monthly, quarterly or annual filing based on how much tax you owe. Returns are due on the last day of the month after the period. The general rate is 6.35%, and there are no local sales taxes. The one mistake to avoid: skipping a return when you had no sales. DRS requires a return for every period, even when nothing is due. Late payments cost 15% of the tax or $50, whichever is more.',
    'sources' => [
        [
            'title' => 'DRS: Sales and Use Tax Information',
            'url' => 'https://portal.ct.gov/drs/sales-tax/tax-information',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DRS-143: Sales Tax Information for New Connecticut Businesses (08/23)',
            'url' => 'https://portal.ct.gov/-/media/DRS/Businesses/DRS-143-SUT-FLYER_0823.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DRS: Registering Your Business with DRS',
            'url' => 'https://portal.ct.gov/drs/businesses/new-business-resource-center/registering-with-drs',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DRS OCG-8: Marketplace facilitators and marketplace sellers',
            'url' => 'https://portal.ct.gov/-/media/drs/publications/ocg/ocg-8.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DRS Form O-88: Instructions for Form OS-114',
            'url' => 'https://portal.ct.gov/-/media/drs/forms/2021/sut/o-88_0721.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DRS IP 2002(26): Q&A on sales and use tax for new businesses',
            'url' => 'https://portal.ct.gov/drs/publications/informational-publications/2002/ip-200226-q--a-on-sales-and-use-tax-for-a-new-businesses',
            'accessed' => '2026-10-02',
        ],
    ],
];
