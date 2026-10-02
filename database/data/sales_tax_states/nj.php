<?php

/*
 * New Jersey: New Jersey Division of Taxation (Department of the
 * Treasury), Certificate of Authority. Researched 2026-10-02 from nj.gov,
 * law.justia.com, pub.njleg.gov. Generated once from the EREG-13 sales tax
 * research; edit this file directly from now on.
 */

return [
    'state' => 'NJ',
    'name' => 'New Jersey',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'New Jersey Division of Taxation (Department of the Treasury)',
        'short' => 'the Division of Taxation',
        'url' => 'https://www.nj.gov/treasury/taxation/',
    ],
    'registration' => [
        'term' => 'Certificate of Authority',
        'portal' => [
            'name' => 'NJ Online Business Registration (Division of Revenue and Enterprise Services)',
            'url' => 'https://www.njportal.com/DOR/BusinessRegistration',
        ],
        'form' => [
            'number' => 'NJ-REG',
            'title' => 'Business Registration Application',
            'pdf_url' => 'https://www.nj.gov/treasury/revenue/pdf/Legacy-Reg-Form-0825.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No fee to file NJ-REG, and the Certificate of Authority is issued without charge. Forming a new business entity has separate filing fees.',
            'cite' => 'NJ-REG instructions (Legacy Reg Form 08/25); N.J.S.A. 54:32B-15',
        ],
        'renewal' => [
            'required' => false,
            'summary' => null,
            'cite' => null,
        ],
        'timing' => [
            'online' => null,
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'Register at least 15 business days before you start business. By statute the Director issues the Certificate of Authority (Form CA-1) within five days after registration. The Division publishes no separate online or paper processing time.',
            'cite' => 'NJ-REG instructions; N.J.S.A. 54:32B-15',
        ],
        'number' => [
            'name' => 'New Jersey Taxpayer Identification Number',
            'format' => '12 digits: the FEIN followed by a 3-digit suffix (usually 000), shown on the Certificate of Authority',
            'cite' => 'NJ-REG instructions (Certificate of Authority shows the 12-digit identification number)',
        ],
    ],
    'nexus' => [
        'physical' => 'Anyone selling taxable goods or services in New Jersey, maintaining a place of business, owning business property or employing workers in the state, including sellers at flea markets, craft shows and fairs, must register.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => 'current or prior calendar year',
            'effective' => '2018-11-01',
            'cite' => 'P.L. 2018, c.132 (A4496); Division of Taxation bulletin S&U-5 (Rev. 5/25)',
        ],
        'marketplace' => 'Marketplace facilitators must collect and remit on all marketplace sales, whether or not the marketplace seller meets either threshold; remote sellers do not collect on sales made through a marketplace (P.L. 2018, c.132; S&U-5).',
    ],
    'filing' => [
        'frequencies' => 'quarterly returns (ST-50), plus monthly payments for larger sellers',
        'rule' => 'Everyone files Form ST-50 quarterly. Sellers that collected more than $30,000 in New Jersey sales and use tax in the prior calendar year also pay by Monthly Voucher (ST-51) for the first and second month of a quarter when that month\'s tax exceeds $500.',
        'due_day' => '20th of the month after the period (11:59 p.m.)',
        'zero_return_required' => true,
        'prepayments' => 'Monthly Voucher payments for prior-year collections over $30,000 (when the month\'s tax exceeds $500)',
        'cite' => 'Division of Taxation, Filing and Remitting Sales and Use Tax (su_12)',
    ],
    'rates' => [
        'state_rate_pct' => 6.625,
        'local' => 'No general local sales tax. Qualified Urban Enterprise Zone and certain Salem County in-person sales use a reduced 3.3125% rate; Atlantic City and certain tourism districts add luxury or tourism taxes on specific items such as hotel rooms, restaurant meals and admissions.',
        'sourcing' => 'destination-based (Streamlined Sales Tax member)',
        'cite' => 'NJ-REG packet, Taxes of the State of New Jersey; S&U-5',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'No local sales tax registration. Municipalities may not license a peddler or vendor without a fixed place of business unless the vendor shows a valid Certificate of Authority (N.J.S.A. 40:52-1.3).',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'The Division can make on-site jeopardy assessments and seize assets of vendors that do not register, file or remit. Vendors collect tax as trustees and can be held personally liable.',
            'cite' => 'Division of Taxation, Information for Vendors; NJ-REG packet sales tax section',
        ],
        'late_filing' => [
            'summary' => '$100 for each month or part of a month a return is late, plus 5% of the underpayment per month, up to 25%, plus interest.',
            'cite' => 'N.J.S.A. 54:49-4',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'use tax',
            'employer withholding',
            'unemployment and disability insurance (UI/DI/FLI)',
            'corporation business tax',
            'other state taxes administered by Taxation and Labor',
        ],
        'prerequisites' => 'An FEIN for corporations and businesses with employees. A new LLC, corporation or partnership files its public record (formation) first, and must file NJ-REG within 60 days of forming.',
        'cite' => 'NJ-REG instructions, Introduction',
    ],
    'facts' => [
        [
            'text' => 'Registration produces two documents: a Business Registration Certificate and, if you will collect sales tax, the Certificate of Authority (Form CA-1). Both must be displayed at your place of business and at any event where you sell.',
            'source_url' => 'https://www.nj.gov/treasury/taxation/informationforvendors.shtml',
        ],
        [
            'text' => 'Monthly Voucher payments cannot be filed for $0. If a month\'s tax is $500 or less, it is paid with the quarterly ST-50 instead.',
            'source_url' => 'https://www.nj.gov/treasury/taxation/su_12.shtml',
        ],
        [
            'text' => 'Sales and use tax returns and payments must be filed electronically through the New Jersey Tax Portal.',
            'source_url' => 'https://www.nj.gov/treasury/taxation/su_12.shtml',
        ],
        [
            'text' => 'Salem County businesses that make reduced-rate sales file a separate monthly return, Form ST-450.',
            'source_url' => 'https://www.nj.gov/treasury/revenue/pdf/Legacy-Reg-Form-0825.pdf',
        ],
        [
            'text' => 'The Division may grant a marketplace facilitator up to 180 days\' delay of its collection and reporting duties on written request.',
            'source_url' => 'https://www.nj.gov/treasury/taxation/pdf/pubs/sales/su5.pdf',
        ],
    ],
    'state_notes' => 'In New Jersey the sales tax registration is called a Certificate of Authority. The New Jersey Division of Taxation issues it. You apply on the Business Registration Application, Form NJ-REG, which is filed with the Division of Revenue and Enterprise Services. Most businesses file it online through NJ Online Business Registration. There is no fee. The same application also registers you for withholding and other state taxes. Register at least 15 business days before you start selling. By law the certificate is issued within five days after you register. If you are forming a new LLC or corporation, form it first, then file NJ-REG within 60 days. Remote sellers must register once they pass $100,000 in New Jersey sales or 200 transactions in the current or prior calendar year. After you register, you file Form ST-50 every quarter, due on the 20th of the month after the quarter. File it even if you had no sales. If you collected more than $30,000 in the prior year, you also make monthly payments when a month\'s tax tops $500. Returns must be filed online. A late return costs $100 a month plus 5% a month of the tax. The one mistake to avoid: starting sales before the certificate arrives. Display it wherever you sell.',
    'sources' => [
        [
            'title' => 'NJ Division of Taxation, Information for Vendors',
            'url' => 'https://www.nj.gov/treasury/taxation/informationforvendors.shtml',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'New Jersey Business Registration packet and NJ-REG instructions (Legacy Reg Form 08/25)',
            'url' => 'https://www.nj.gov/treasury/revenue/pdf/Legacy-Reg-Form-0825.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'NJ Division of Taxation, Filing and Remitting Sales and Use Tax',
            'url' => 'https://www.nj.gov/treasury/taxation/su_12.shtml',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'S&U-5, Making Mail-Order and Internet Sales (Rev. 5/25)',
            'url' => 'https://www.nj.gov/treasury/taxation/pdf/pubs/sales/su5.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'N.J.S.A. 54:32B-15, Certificate of registration',
            'url' => 'https://law.justia.com/codes/new-jersey/title-54/section-54-32b-15/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'N.J.S.A. 54:49-4, Late filing penalty (via Division search results)',
            'url' => 'https://www.nj.gov/treasury/taxation/njit19.shtml',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'P.L. 2018, c.132 (A4496)',
            'url' => 'https://pub.njleg.gov/bills/2018/AL18/132_.HTM',
            'accessed' => '2026-10-02',
        ],
    ],
];
