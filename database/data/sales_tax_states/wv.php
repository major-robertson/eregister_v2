<?php

/*
 * West Virginia: West Virginia Tax Division (West Virginia Department of
 * Revenue), Business Registration Certificate. Researched 2026-10-02 from
 * tax.wv.gov, code.wvlegislature.gov. Generated once from the EREG-13
 * sales tax research; edit this file directly from now on.
 */

return [
    'state' => 'WV',
    'name' => 'West Virginia',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'West Virginia Tax Division (West Virginia Department of Revenue)',
        'short' => 'the Tax Division',
        'url' => 'https://tax.wv.gov/',
    ],
    'registration' => [
        'term' => 'Business Registration Certificate',
        'portal' => [
            'name' => 'West Virginia One Stop Business Portal (business4.wv.gov); remote sellers may use the simplified Remote Seller Registration on MyTaxes',
            'url' => 'https://business4.wv.gov/',
        ],
        'form' => [
            'number' => 'WV/BUS-APP',
            'title' => 'Application for Registration Certificate (West Virginia New Business Registration Application)',
            'pdf_url' => 'https://tax.wv.gov/Documents/Business/BusinessRegistrationInformationAndInstructions.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 3000,
            'summary' => '$30 for each Business Registration Certificate (one per fixed business location). Some organizations are exempt from the fee. Reinstating a revoked certificate costs a $100 penalty plus the $30 fee.',
            'cite' => 'WV Tax Division TSD-360, Business Registration Procedures (Rev. March 2023); Business Registration Information & Instructions (June 2025)',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'The certificate is permanent until the business closes or moves, or the Tax Commissioner suspends, revokes or cancels it.',
            'cite' => 'TSD-360 (Rev. March 2023); Business Registration Information & Instructions (June 2025)',
        ],
        'timing' => [
            'online' => null,
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'After the application is processed, the Tax Division sends the Business Registration Certificate and a list of the business\'s tax account numbers. No processing time is stated.',
            'cite' => 'Business Registration Information & Instructions (June 2025), p. 3',
        ],
        'number' => [
            'name' => 'Business Registration Certificate number (with a separate account ID for each tax)',
            'format' => null,
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'Every vendor engaging in business in West Virginia must register, with a separate certificate for each fixed location where goods or services are offered for sale or lease or where customer accounts are opened or serviced.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => 'immediately preceding calendar year or current calendar year',
            'effective' => '2019-01-01',
            'cite' => 'W. Va. Code 11-15A-6b(e); WV Tax Division, Remote Sellers and West Virginia Sales and Use Tax',
        ],
        'marketplace' => 'For sales on and after July 1, 2019 a marketplace facilitator or referrer that meets the same $100,000 or 200-transaction test must collect and remit tax on sales it makes or facilitates into West Virginia (W. Va. Code 11-15A-6b; TSD-442).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annual',
        'rule' => 'More than $250 a month in tax: monthly. Average monthly remittance of $250 or less: may file quarterly. Annual tax of $600 or less: may file one annual return. Taxpayers who paid more than $25,000 in the prior year must file and pay electronically.',
        'due_day' => '20th of the month after the period',
        'zero_return_required' => true,
        'prepayments' => 'The June accelerated payment for large filers is no longer required, effective April 7, 2025 (per the Tax Division\'s sales tax page).',
        'cite' => 'W. Va. Code 11-15-16; WV Tax Division TSD-100, Business Taxes (Rev. July 2026), p. 3',
    ],
    'rates' => [
        'state_rate_pct' => 6,
        'local' => 'Municipal sales and use taxes in a growing number of cities, reported on Schedule M of the state return (CST-200CU); food for home consumption is exempt from the state tax',
        'sourcing' => 'destination: municipal tax applies where the goods or taxable service are delivered',
        'cite' => 'W. Va. Code 11-15-3(b); TSD-100 (Rev. July 2026), pp. 3-4',
    ],
    'local_registration' => [
        'required' => true,
        'summary' => 'Municipal sales tax is filed with the state, but many West Virginia cities impose their own business and occupation (B&O) tax, registration taxes or license fees that the Tax Division does not collect. The Tax Division tells new businesses to contact each city where they will do business.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Doing business without a required Business Registration Certificate can bring fines of $1,000 to $10,000, plus a penalty of $100 a day after 30 days of operating without one.',
            'cite' => 'W. Va. Code 11-9-11 and 11-9-12, as summarized in TSD-360 (Rev. March 2023)',
        ],
        'late_filing' => [
            'summary' => 'Failure to file: 5% of the net tax due per month or part of a month, up to 25%. Failure to pay: an additional 0.5% per month, up to 25%. Interest is charged separately.',
            'cite' => 'W. Va. Code 11-10-18(a)',
        ],
    ],
    'connected' => [
        'covers' => [
            'consumers sales and service tax',
            'use tax',
            'employer withholding',
            'other Tax Division accounts as applicable',
        ],
        'prerequisites' => 'FEIN (or the owner\'s SSN for a sole proprietor with no employees). The One Stop Business Portal at business4.wv.gov registers the business with the Secretary of State and other agencies as well as the Tax Division.',
        'cite' => 'Business Registration Information & Instructions (June 2025); TSD-360 (Rev. March 2023)',
    ],
    'facts' => [
        [
            'text' => 'West Virginia charges a $30 fee for each Business Registration Certificate. Some organizations are exempt from the fee.',
            'source_url' => 'https://tax.wv.gov/Documents/TSD/tsd360.pdf',
        ],
        [
            'text' => 'A sales and use tax return must be filed even if no tax is collected or due.',
            'source_url' => 'https://tax.wv.gov/Documents/TSD/tsd100.pdf',
        ],
        [
            'text' => 'Food and food ingredients for human consumption are exempt from West Virginia sales tax.',
            'source_url' => 'https://tax.wv.gov/Documents/TSD/tsd100.pdf',
        ],
        [
            'text' => 'Businesses selling from vehicles must carry a copy of the certificate in each vehicle, and contractors must keep a copy at each construction site.',
            'source_url' => 'https://tax.wv.gov/Documents/Business/BusinessRegistrationInformationAndInstructions.pdf',
        ],
        [
            'text' => 'Out-of-state sellers whose only West Virginia activity is internet, phone or mail-order sales may use a simplified Remote Seller Registration on MyTaxes.',
            'source_url' => 'https://tax.wv.gov/Documents/Business/BusinessRegistrationInformationAndInstructions.pdf',
        ],
    ],
    'state_notes' => 'In West Virginia the registration is called a Business Registration Certificate. The West Virginia Tax Division issues it, and it covers sales and use tax along with your other state tax accounts. Register online through the West Virginia One Stop Business Portal at business4.wv.gov, or mail Form WV/BUS-APP, the Application for Registration Certificate. The fee is $30 for each business location. The certificate does not expire, and you must post it where you do business. Sellers outside West Virginia must register once they pass $100,000 in sales or 200 transactions into the state in the current or prior calendar year. Remote sellers can use a simpler registration on MyTaxes. The state rate is 6%, and some cities add their own sales tax, which you report on the state return. Food for home use is exempt. Most sellers file monthly. If your tax averages $250 a month or less you may file quarterly, and at $600 a year or less, annually. Returns are due on the 20th of the month after the period. File a return even when no tax is due. The mistake to avoid: assuming the state certificate covers city taxes. Many cities have their own business taxes and licenses, so check with each city.',
    'sources' => [
        [
            'title' => 'WV Tax Division: Sales and Use Tax',
            'url' => 'https://tax.wv.gov/Business/SalesAndUseTax/Pages/SalesAndUseTax.aspx',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'WV TSD-360, Business Registration Procedures (Rev. March 2023)',
            'url' => 'https://tax.wv.gov/Documents/TSD/tsd360.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'WV Business Registration Information & Instructions (June 2025)',
            'url' => 'https://tax.wv.gov/Documents/Business/BusinessRegistrationInformationAndInstructions.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'WV TSD-100, Business Taxes (Rev. July 2026)',
            'url' => 'https://tax.wv.gov/Documents/TSD/tsd100.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'WV Tax Division: Remote Sellers and West Virginia Sales and Use Tax',
            'url' => 'https://tax.wv.gov/Business/SalesAndUseTax/ECommerce/RemoteSellers/Pages/RemoteSellersAndWestVirginiaTax.aspx',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'WV TSD-442, Marketplace Facilitators',
            'url' => 'https://tax.wv.gov/Documents/TSD/tsd442.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'W. Va. Code 11-15A-6b',
            'url' => 'https://code.wvlegislature.gov/11-15A-6b/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'W. Va. Code 11-15-16',
            'url' => 'https://code.wvlegislature.gov/11-15-16/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'W. Va. Code 11-15-3',
            'url' => 'https://code.wvlegislature.gov/11-15-3/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'W. Va. Code 11-10-18',
            'url' => 'https://code.wvlegislature.gov/11-10-18/',
            'accessed' => '2026-10-02',
        ],
    ],
];
