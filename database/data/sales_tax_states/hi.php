<?php

/*
 * Hawaii: Hawaii Department of Taxation, General Excise Tax (GET) License.
 * Researched 2026-10-02 from files.hawaii.gov, tax.hawaii.gov,
 * data.capitol.hawaii.gov. Generated once from the EREG-13 sales tax
 * research; edit this file directly from now on.
 */

return [
    'state' => 'HI',
    'name' => 'Hawaii',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Hawaii Department of Taxation',
        'short' => 'DOTAX',
        'url' => 'https://tax.hawaii.gov/',
    ],
    'registration' => [
        'term' => 'General Excise Tax (GET) License',
        'portal' => [
            'name' => 'Hawaii Tax Online (hitax.hawaii.gov); also Hawaii Business Express',
            'url' => 'https://hitax.hawaii.gov/',
        ],
        'form' => [
            'number' => 'BB-1',
            'title' => 'State of Hawaii Basic Business Application (select General Excise/Use Tax)',
            'pdf_url' => null,
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 2000,
            'summary' => '$20 registration fee. Branch licenses for extra locations (Form G-50) are free.',
            'cite' => 'An Introduction to the General Excise Tax (rev. Sept. 2023), Q21 and Q25, https://files.hawaii.gov/tax/legal/brochures/GE_brochure-23.pdf',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'No renewal was found in DOTAX\'s GET guide; the license is issued once and must be displayed at the place of business.',
            'cite' => 'https://files.hawaii.gov/tax/legal/brochures/GE_brochure-23.pdf',
        ],
        'timing' => [
            'online' => '5 business days through hitax.hawaii.gov; within 2 weeks through Hawaii Business Express',
            'paper' => 'within four weeks by mail or drop-off; same day in person at a district tax office',
            'temporary_number' => false,
            'summary' => 'DOTAX issues a Hawaii Tax Identification Number within 5 business days through hitax.hawaii.gov, within 2 weeks through Hawaii Business Express, the same day in person at a district tax office, or within four weeks by mail or drop-off.',
            'cite' => 'An Introduction to the General Excise Tax (rev. Sept. 2023), Q22',
        ],
        'number' => [
            'name' => 'Hawaii Tax Identification Number (GE number)',
            'format' => 'GE followed by 12 digits, laid out GE-XXX-XXX-XXXX-XX (from the resale certificate research, per Form G-17)',
            'cite' => 'https://files.hawaii.gov/tax/forms/current/g17.pdf',
        ],
    ],
    'nexus' => [
        'physical' => 'A business with an office, employees or representatives, inventory or other property in Hawaii, or that performs services such as installation or repair in Hawaii, must hold a GET license.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => 'current or preceding calendar year (gross income in the State of $100,000 or more, or 200 or more separate transactions; either test)',
            'effective' => '2018-07-01',
            'cite' => 'HRS 237-2.5 (L 2018, c 41), Unofficial Compilation as of 12/31/2025, https://files.hawaii.gov/tax/legal/hrs/hrs_237.pdf; Tax Announcement 2018-10',
        ],
        'marketplace' => 'Hawaii treats a marketplace facilitator as the seller of the goods it facilitates, so it owes the GET at the retail rate on those sales (HRS 237-1 definitions; not confirmed on a DOTAX page read today).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or semiannual periodic returns (Form G-45), plus an annual return (Form G-49)',
        'rule' => 'Semiannual if GET (including county surcharge) is $2,000 or less a year; quarterly if $4,000 or less; monthly if more than $4,000, and monthly filers must file electronically. If total liability for the year is $100 or less, periodic returns are not required, but the annual G-49 is.',
        'due_day' => 'periodic returns the 20th of the month after the period; annual return the 20th day of the fourth month after the tax year (April 20 for calendar-year filers)',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'An Introduction to the General Excise Tax (rev. Sept. 2023), Q35-Q36, https://files.hawaii.gov/tax/legal/brochures/GE_brochure-23.pdf',
    ],
    'rates' => [
        'state_rate_pct' => 4.0,
        'local' => 'County surcharge of 0.5% in counties that adopted it, for 4.5% total on retail; 0.5% for wholesaling and manufacturing; 0.15% on insurance commissions.',
        'sourcing' => 'The county surcharge follows where goods are delivered or services are used (per DOTAX\'s brochure rules for the surcharge).',
        'cite' => 'An Introduction to the General Excise Tax (rev. Sept. 2023), https://files.hawaii.gov/tax/legal/brochures/GE_brochure-23.pdf',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => null,
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Doing business without a GET license can bring a civil citation with a $500 fine for most businesses, or $2,000 for cash-based businesses.',
            'cite' => 'An Introduction to the General Excise Tax (rev. Sept. 2023), Q14 and Q28',
        ],
        'late_filing' => [
            'summary' => 'Late filing: 5% of the tax per month or part of a month, up to 25%. Tax not paid within 60 days of the due date: 20% of the unpaid tax. Failing to file or pay electronically when required: 2%.',
            'cite' => 'An Introduction to the General Excise Tax (rev. Sept. 2023); HRS 231-39',
        ],
    ],
    'connected' => [
        'covers' => [
            'general excise tax',
            'use tax (automatic with GET registration)',
            'other taxes on the BB-1 such as withholding and transient accommodations tax',
        ],
        'prerequisites' => null,
        'cite' => 'An Introduction to the General Excise Tax (rev. Sept. 2023), Q18',
    ],
    'facts' => [
        [
            'text' => 'Hawaii has no sales tax; the GET is a tax on the business\'s gross income, which businesses may pass on to customers. It applies to most services as well as goods.',
            'source_url' => 'https://tax.hawaii.gov/get/',
        ],
        [
            'text' => 'Hawaii\'s remote seller test still includes 200 transactions as an alternative to $100,000 of gross income, in the current or preceding calendar year (HRS 237-2.5 as compiled to December 31, 2025).',
            'source_url' => 'https://files.hawaii.gov/tax/legal/hrs/hrs_237.pdf',
        ],
        [
            'text' => 'Every GET licensee must file an annual reconciliation return (Form G-49); not filing it within 12 months of its due date can cost the business its GET exemptions and lower rates.',
            'source_url' => 'https://files.hawaii.gov/tax/legal/brochures/GE_brochure-23.pdf',
        ],
        [
            'text' => 'Businesses whose total GET, use tax and county surcharge exceed $100,000 a year must pay by electronic funds transfer.',
            'source_url' => 'https://files.hawaii.gov/tax/legal/brochures/GE_brochure-23.pdf',
        ],
        [
            'text' => 'Act 47 (2024) exempted medical and dental services covered by Medicare, Medicaid and TRICARE from the GET.',
            'source_url' => 'https://data.capitol.hawaii.gov/sessions/session2026/bills/HB281_.pdf',
        ],
    ],
    'state_notes' => 'Hawaii does not have a sales tax permit. Businesses need a General Excise Tax (GET) License from the Hawaii Department of Taxation (DOTAX). You apply on Form BB-1, the State of Hawaii Basic Business Application, and select General Excise/Use Tax. You can file online at hitax.hawaii.gov, through Hawaii Business Express, by mail, or at a district tax office. The fee is $20. DOTAX says online applications at hitax.hawaii.gov take about 5 business days, Hawaii Business Express up to 2 weeks, and mail up to four weeks. In person, you can get your number the same day. Each extra location needs a free branch license. The GET is 4% on most retail sales and services, plus a 0.5% county surcharge where adopted. You file Form G-45 monthly, quarterly or every six months, depending on your yearly tax, by the 20th of the next month. You also file an annual return, Form G-49, by April 20 for calendar-year filers. The one mistake to avoid: skipping the annual G-49. Missing it for 12 months can cost you exemptions and lower rates you would otherwise get.',
    'sources' => [
        [
            'title' => 'DOTAX: An Introduction to the General Excise Tax (rev. Sept. 2023)',
            'url' => 'https://files.hawaii.gov/tax/legal/brochures/GE_brochure-23.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'HRS Chapter 237, General Excise Tax Law (Unofficial Compilation as of 12/31/2025)',
            'url' => 'https://files.hawaii.gov/tax/legal/hrs/hrs_237.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOTAX: Hawaii General Excise Tax (GET)',
            'url' => 'https://tax.hawaii.gov/get/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Hawaii Legislature: HB 281 (2025), findings on Act 47 (2024)',
            'url' => 'https://data.capitol.hawaii.gov/sessions/session2026/bills/HB281_.pdf',
            'accessed' => '2026-10-02',
        ],
    ],
];
