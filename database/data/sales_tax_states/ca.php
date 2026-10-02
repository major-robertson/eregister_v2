<?php

/*
 * California: California Department of Tax and Fee Administration,
 * Seller's Permit. Researched 2026-10-02 from cdtfa.ca.gov, taxes.ca.gov.
 * Generated once from the EREG-13 sales tax research; edit this file
 * directly from now on.
 */

return [
    'state' => 'CA',
    'name' => 'California',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'California Department of Tax and Fee Administration',
        'short' => 'CDTFA',
        'url' => 'https://www.cdtfa.ca.gov/',
    ],
    'registration' => [
        'term' => 'Seller\'s Permit',
        'portal' => [
            'name' => 'CDTFA Online Services (Register a New Business Activity)',
            'url' => 'https://onlineservices.cdtfa.ca.gov/',
        ],
        'form' => [
            'number' => null,
            'title' => 'Online registration in CDTFA Online Services, or in person at a CDTFA office (no downloadable paper application found)',
            'pdf_url' => null,
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'The permit is free. CDTFA may require a security deposit, for example when the law requires it, after a revocation, or after a history of nonpayment; the amount is set at application based on expected sales.',
            'cite' => 'CDTFA Publication 73, https://cdtfa.ca.gov/formspubs/pub73.pdf; https://cdtfa.ca.gov/taxes-and-fees/faqseller.htm',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'The permit does not expire on a schedule. It is valid only while the holder is actively in business as a seller.',
            'cite' => 'R&TC 6072; https://cdtfa.ca.gov/taxes-and-fees/faqseller.htm',
        ],
        'timing' => [
            'online' => 'may be issued the same day',
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'CDTFA says, \'We may be able to issue your permit the same day.\' Temporary seller\'s permits are available for selling operations of 90 days or less at one location.',
            'cite' => 'CDTFA Publication 107, https://cdtfa.ca.gov/formspubs/pub107/applying-for-a-sellers-permit.htm',
        ],
        'number' => [
            'name' => 'California seller\'s permit number',
            'format' => null,
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'Anyone who sells or leases tangible personal property that would normally be taxable at retail in California, including keeping inventory in a California warehouse, must hold a seller\'s permit for each place of business.',
        'economic' => [
            'revenue_usd' => 500000,
            'transactions' => null,
            'period' => 'preceding or current calendar year (total combined sales of tangible personal property for delivery in California by the retailer and related persons, taxable or not)',
            'effective' => '2019-04-01',
            'cite' => 'R&TC 6203(c)(4); CDTFA Regulation 1684.5, https://cdtfa.ca.gov/lawguides/vol1/sutr/1684-5.html',
        ],
        'marketplace' => 'From October 1, 2019, a marketplace facilitator whose own and facilitated sales for delivery in California exceed $500,000 is the retailer for its marketplace sellers\' sales and must register and pay the tax (R&TC 6040-6049.5; https://cdtfa.ca.gov/lawguides/vol1/sutr/1684-5.html).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly, quarterly prepay, fiscal yearly or yearly, assigned by CDTFA',
        'rule' => 'CDTFA assigns the reporting basis when it issues the permit, based on expected or reported sales. Businesses with average taxable sales of $17,000 or more a month are notified in writing to file quarterly with prepayments.',
        'due_day' => 'last day of the month after the reporting period',
        'zero_return_required' => true,
        'prepayments' => 'Quarterly prepay accounts make prepayments for the first two months of each quarter, due the 24th of the following month, then file a quarterly return.',
        'cite' => 'CDTFA Publication 73, https://cdtfa.ca.gov/formspubs/pub73.pdf; https://cdtfa.ca.gov/taxes-and-fees/sales-use-tax-returns-filing-dates.htm',
    ],
    'rates' => [
        'state_rate_pct' => 7.25,
        'local' => '7.25% statewide base rate (state plus uniform local); voter-approved district taxes add to it in many cities and counties.',
        'sourcing' => 'The 7.25% statewide rate is based on the seller\'s place of business; district taxes apply where the goods are delivered if the seller is engaged in business in that district.',
        'cite' => 'CDTFA Publication 105, https://cdtfa.ca.gov/formspubs/pub105/basic-rules.htm',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => null,
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Selling in California without a permit, or after a permit is suspended or revoked, is a misdemeanor for the person and each officer of a corporation that does so.',
            'cite' => 'R&TC 6071, https://cdtfa.ca.gov/lawguides/vol1/sutl/6071.html',
        ],
        'late_filing' => [
            'summary' => '10% penalty for a late return and 10% for a late payment; when both apply for the same period, the total is capped at 10% of the tax due, plus interest.',
            'cite' => 'R&TC 6591; CDTFA Publication 75, https://cdtfa.ca.gov/formspubs/pub75/when-do-interest-penalty-collection-cost-recovery-fee-charges-apply.htm',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'use tax',
        ],
        'prerequisites' => 'SSN, date of birth and driver\'s license or ID number of the owners or officers, bank and supplier details, expected monthly sales and an email address. Employer payroll registration is with EDD, not CDTFA.',
        'cite' => 'https://taxes.ca.gov/sales-and-use-tax/sellers-permit/; https://cdtfa.ca.gov/formspubs/pub107/applying-for-a-sellers-permit.htm',
    ],
    'facts' => [
        [
            'text' => 'California counts all sales of tangible personal property for delivery into the state toward the $500,000 threshold, including exempt sales and the sales of related persons.',
            'source_url' => 'https://cdtfa.ca.gov/lawguides/vol1/sutr/1684-5.html',
        ],
        [
            'text' => 'A seller must file a return every period even with no sales or no tax due.',
            'source_url' => 'https://cdtfa.ca.gov/formspubs/pub73.pdf',
        ],
        [
            'text' => 'A seller not engaged in business in a special tax district may collect the 7.25% statewide rate on deliveries into that district; once engaged in business there, it must collect the district rate.',
            'source_url' => 'https://cdtfa.ca.gov/formspubs/pub105/basic-rules.htm',
        ],
        [
            'text' => 'Temporary seller\'s permits cover selling operations of 90 days or less at one location, such as seasonal or event sales.',
            'source_url' => 'https://cdtfa.ca.gov/taxes-and-fees/faqseller.htm',
        ],
        [
            'text' => 'Buyers use CDTFA-230, General Resale Certificate, for resale purchases, but any document with the elements of Regulation 1668 is acceptable.',
            'source_url' => 'https://cdtfa.ca.gov/formspubs/pub103/valid-resale-certificates.htm',
        ],
    ],
    'state_notes' => 'In California, you need a Seller\'s Permit. The California Department of Tax and Fee Administration (CDTFA) issues it. You register online through CDTFA Online Services, or at a CDTFA office. There is no paper form to mail. The permit is free. CDTFA may ask for a security deposit, based on your type of business and expected sales. CDTFA says it may be able to issue your permit the same day. Have your SSN, driver\'s license number, bank details and supplier details ready. You need a permit for each place of business. The permit does not expire, but it is valid only while you are actively selling. When CDTFA issues the permit, it tells you how often to file: monthly, quarterly, quarterly with prepayments, or yearly. Returns are due on the last day of the month after the period. Businesses averaging $17,000 or more in taxable sales a month must make prepayments. The statewide rate is 7.25%, and district taxes add to it in many areas. The one mistake to avoid: skipping a return because you had no sales. California requires a return every period, even when you owe nothing.',
    'sources' => [
        [
            'title' => 'CDTFA Publication 107: Applying for a seller\'s permit',
            'url' => 'https://cdtfa.ca.gov/formspubs/pub107/applying-for-a-sellers-permit.htm',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'CDTFA Publication 73: Your California Seller\'s Permit',
            'url' => 'https://cdtfa.ca.gov/formspubs/pub73.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'CDTFA: Obtaining a Seller\'s Permit FAQ',
            'url' => 'https://cdtfa.ca.gov/taxes-and-fees/faqseller.htm',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'taxes.ca.gov: Get a Seller\'s Permit',
            'url' => 'https://taxes.ca.gov/sales-and-use-tax/sellers-permit/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'CDTFA Regulation 1684.5',
            'url' => 'https://cdtfa.ca.gov/lawguides/vol1/sutr/1684-5.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'CDTFA: Sales and use tax returns filing dates',
            'url' => 'https://cdtfa.ca.gov/taxes-and-fees/sales-use-tax-returns-filing-dates.htm',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'CDTFA Publication 105: District taxes, basic rules',
            'url' => 'https://cdtfa.ca.gov/formspubs/pub105/basic-rules.htm',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'R&TC 6071 (CDTFA law guide)',
            'url' => 'https://cdtfa.ca.gov/lawguides/vol1/sutl/6071.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'R&TC 6072 (CDTFA law guide)',
            'url' => 'https://cdtfa.ca.gov/lawguides/vol1/sutl/6072.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'CDTFA Publication 75: When interest and penalty apply',
            'url' => 'https://cdtfa.ca.gov/formspubs/pub75/when-do-interest-penalty-collection-cost-recovery-fee-charges-apply.htm',
            'accessed' => '2026-10-02',
        ],
    ],
];
