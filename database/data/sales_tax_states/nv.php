<?php

/*
 * Nevada: Nevada Department of Taxation, Seller's Permit (Sales and Use
 * Tax Permit). Researched 2026-10-02 from tax.nv.gov, leg.state.nv.us,
 * salestaxinstitute.com. Generated once from the EREG-13 sales tax
 * research; edit this file directly from now on.
 */

return [
    'state' => 'NV',
    'name' => 'Nevada',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Nevada Department of Taxation',
        'short' => 'the Department of Taxation',
        'url' => 'https://tax.nv.gov/',
    ],
    'registration' => [
        'term' => 'Seller\'s Permit (Sales and Use Tax Permit)',
        'portal' => [
            'name' => 'My Nevada Tax',
            'url' => 'https://mynvtax.nv.gov/',
        ],
        'form' => [
            'number' => 'TAX-F006',
            'title' => 'Nevada Business Registration',
            'pdf_url' => 'https://tax.nv.gov/wp-content/uploads/2024/03/TAX-F006-Nevada-Business-Registration2.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 1500,
            'summary' => '$15 for each in-state business location; sellers with no Nevada location still pay a $15 minimum. The Department may also require a security deposit (three times estimated monthly tax for monthly filers, six times for quarterly filers per Form TAX-F006 instructions); no deposit is required if the amount would not exceed $1,000, and a waiver can be requested after three years of perfect reporting. The permit is not issued until required security is posted.',
            'cite' => 'NRS 360.5972; NRS 372.510; Form TAX-F006 instructions, items 24 and 25',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'Permits are valid until suspended or revoked. There is no renewal fee. A new permit is needed if the location or ownership changes.',
            'cite' => 'Department of Taxation Taxpayer Information Packet (TPI-1), What Is Required of Sellers',
        ],
        'timing' => [
            'online' => null,
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'The Department does not publish a processing time. Its My Nevada Tax registration handout says that after you submit and pay the $15 fee you can view your locations and print the permit for display.',
            'cite' => 'Department of Taxation, My Nevada Tax registration handout (July 2025)',
        ],
        'number' => [
            'name' => 'Seller\'s permit Location ID',
            'format' => null,
            'cite' => 'Taxpayer Information Packet (TPI-1), resale certificate sample',
        ],
    ],
    'nexus' => [
        'physical' => 'Any person engaging in business in Nevada as a seller of tangible personal property, including out-of-state businesses with inventory in Nevada fulfillment centers (unless all their sales go through registered marketplace facilitators), must hold a permit for each place of business.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => 'previous or current calendar year',
            'effective' => '2018-10-01',
            'cite' => 'Nevada remote seller regulation effective 10/1/2018; SB 447 (2019); Department of Taxation Marketplace Facilitator/Seller FAQs',
        ],
        'marketplace' => 'Since October 1, 2019, marketplace facilitators that exceed $100,000 or 200 transactions in the previous or current calendar year must register and collect on all facilitated Nevada sales (NRS 372.748 and following; Department FAQs). Sellers that reach the threshold must register by the first day of the month at least 30 days later.',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annual by taxable sales',
        'rule' => 'Quarterly if taxable sales do not exceed $10,000 per month; annual for quarterly filers with no tax due for the prior three quarters or taxable sales of $1,500 or less over the prior four quarters; monthly otherwise.',
        'due_day' => '20th of the month after the period, starting with the January 2026 return (due February 20, 2026); previously the last day of the month',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'NRS 372.355 (as amended 2025); NRS 372.380; Nevada Tax Notes Issue 206 (March 2026); TPI-1',
    ],
    'rates' => [
        'state_rate_pct' => 6.85,
        'local' => '6.85% is the statewide minimum combined rate; county rates bring combined rates up to 8.375% (Clark County).',
        'sourcing' => 'destination-based (Streamlined Sales Tax member); tax is due for the county where the property is first used',
        'cite' => 'Taxpayer Information Packet (TPI-1) county rate table; Department SEID handout',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'County sales taxes are reported on the state return by county. No separate local sales tax registration. A Nevada State Business License from the Secretary of State is a separate requirement.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Engaging in business without the required permit, or after it is suspended, is a misdemeanor for the business and each corporate officer. After notice, the Department may order the place of business locked and sealed.',
            'cite' => 'NRS 360.490',
        ],
        'late_filing' => [
            'summary' => 'Late returns or payments are charged a penalty by days late: 2% (1 to 10 days), 4% (11 to 15), 6% (16 to 20), 8% (21 to 30), 10% (31 or more), plus 0.75% interest per month.',
            'cite' => 'NAC 360.395; NRS 360.417; TPI-1',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'use tax',
            'Modified Business Tax (when registered with the Employment Security Division)',
        ],
        'prerequisites' => 'EIN (or SSN for sole proprietors) and the Nevada Business ID from the Secretary of State\'s State Business License, which Form TAX-F006 asks for.',
        'cite' => 'Form TAX-F006 instructions, items 5 and 17; TAX-F006 page 3 note on Modified Business Tax',
    ],
    'facts' => [
        [
            'text' => 'Starting with the January 2026 period, Nevada sales and use tax returns are due on the 20th of the following month instead of the last day of the month.',
            'source_url' => 'https://tax.nv.gov/wp-content/uploads/2026/03/Nevada-Tax-Notes-March-2026.pdf',
        ],
        [
            'text' => 'A remote seller that sells only through registered marketplace facilitators, and whose only Nevada link is inventory in a third-party fulfillment center, does not need its own Nevada permit.',
            'source_url' => 'https://tax.nv.gov/faqs/marketplace-facilitator-seller-faqs/',
        ],
        [
            'text' => 'Separately stated delivery, shipping and postage charges have not been taxable in Nevada since May 2009 (AB 403).',
            'source_url' => 'https://tax.nv.gov/faqs/sales-tax-faqs/',
        ],
        [
            'text' => 'The Department may require a security deposit before issuing the permit. Form TAX-F006 tells applicants to estimate it from monthly taxable receipts at the highest Nevada rate, 8.375%.',
            'source_url' => 'https://tax.nv.gov/wp-content/uploads/2024/03/TAX-F006-Nevada-Business-Registration2.pdf',
        ],
        [
            'text' => 'My Nevada Tax Phase 3, the final phase of the Department\'s new system, launches December 7, 2026 for other tax types such as lodging and tire fees.',
            'source_url' => 'https://tax.nv.gov/wp-content/uploads/2026/03/Nevada-Tax-Notes-March-2026.pdf',
        ],
    ],
    'state_notes' => 'In Nevada you need a Seller\'s Permit, also called a sales and use tax permit. The Nevada Department of Taxation issues it. Most businesses apply online in My Nevada Tax. The paper option is Form TAX-F006, Nevada Business Registration. The permit fee is $15 for each Nevada location, and $15 if you have no location in the state. The Department may also ask for a security deposit based on your expected tax. No deposit is needed if the figure is $1,000 or less. The permit is not issued until any required deposit is posted. Have your EIN and your Nevada State Business License number ready. Remote sellers must register once they pass $100,000 in Nevada sales or 200 transactions in the current or prior calendar year. After you register, most sellers file quarterly. You file monthly if taxable sales top $10,000 a month, and some very small sellers file yearly. Since the January 2026 period, returns are due on the 20th of the following month, not the last day. File a return even when you had no sales. Late penalties start at 2% and rise to 10% after 30 days. The one mistake to avoid: using the old end-of-month due date.',
    'sources' => [
        [
            'title' => 'Form TAX-F006, Nevada Business Registration',
            'url' => 'https://tax.nv.gov/wp-content/uploads/2024/03/TAX-F006-Nevada-Business-Registration2.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Taxpayer Information Packet (TPI-1)',
            'url' => 'https://tax.nv.gov/wp-content/uploads/2024/05/Taxpayer-Information-Packet-TPI-1.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Nevada Tax Notes, Issue 206 (March 2026)',
            'url' => 'https://tax.nv.gov/wp-content/uploads/2026/03/Nevada-Tax-Notes-March-2026.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Marketplace Facilitator/Seller FAQs',
            'url' => 'https://tax.nv.gov/faqs/marketplace-facilitator-seller-faqs/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales Tax FAQs',
            'url' => 'https://tax.nv.gov/faqs/sales-tax-faqs/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'My Nevada Tax registration and SEID handout',
            'url' => 'https://tax.nv.gov/wp-content/uploads/2025/07/SEID-Handouts-For-Print.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'NRS Chapter 372 (372.355, 372.380, 372.510)',
            'url' => 'https://www.leg.state.nv.us/nrs/nrs-372.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'NRS Chapter 360 (360.490, 360.5971, 360.5972)',
            'url' => 'https://www.leg.state.nv.us/nrs/nrs-360.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales Tax Institute: Nevada sales and use tax return due date change (cross-check)',
            'url' => 'https://www.salestaxinstitute.com/resources/nevada-sales-use-tax-return-due-date-change',
            'accessed' => '2026-10-02',
        ],
    ],
];
