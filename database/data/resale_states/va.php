<?php

/*
 * Virginia: Virginia Department of Taxation, Form ST-10. Researched 2026-10-01
 * from tax.virginia.gov, law.lis.virginia.gov. Generated once from the EREG-8
 * resale research; edit this file directly from now on.
 */

return [
    'state' => 'VA',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Virginia Department of Taxation',
        'short' => 'Virginia Tax',
        'url' => 'https://www.tax.virginia.gov/',
    ],
    'resale_page_url' => 'https://www.tax.virginia.gov/sites/default/files/taxforms/exemption-certificates/any/st-10-any.pdf',
    'form' => [
        'number' => 'ST-10',
        'title' => 'Commonwealth of Virginia Sales and Use Tax Certificate of Exemption (resale, lease or rental, packaging)',
        'pdf_url' => 'https://www.tax.virginia.gov/sites/default/files/taxforms/exemption-certificates/any/st-10-any.pdf',
        'prescribed' => true,
        'revision' => 'Rev. 05/26',
        'notes' => 'The Department prescribes ST-10 for purchases for resale. ST-14 is for out-of-state dealers buying for immediate transport out of Virginia in their own vehicle. ST-10 may not be used to buy cigarettes for resale (since January 1, 2018) or by using or consuming construction contractors.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Virginia sales and use tax certificate of registration',
        'number_name' => 'Virginia Account No.',
        'format' => null,
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'Virginia Tax P.D. 95-316 (Dec. 15, 1995) and P.D. 19-125 (Jan. 31, 2019), https://www.tax.virginia.gov/laws-rules-decisions/rulings-tax-commissioner/19-125. Accepted only by Department ruling, in lieu of ST-10, if it contains all ST-10 information and the purchaser is registered in at least one listed state. Virginia is not listed on the MTC certificate itself (rev. 10/14/22).',
        ],
        'sst' => [
            'value' => false, // not a Streamlined member; shown as not accepted rather than inheriting the seeded 'true'
            'cite' => 'Virginia is not a Streamlined Sales Tax member and no Virginia Tax guidance was found that names the SST certificate. The Department accepts other states\' agency-issued resale certificates if they carry all ST-10 information (Va. Code §58.1-623).',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Virginia Tax rulings (e.g. P.D. 25-81, June 20, 2025, https://www.tax.virginia.gov/laws-rules-decisions/rulings-tax-commissioner/25-81); 23VAC10-210-280.',
            'notes' => 'The Department allowed the resale exemption for an unregistered out-of-state dealer that used Form ST-10 and wrote its out-of-state registration number on it, and accepts other states\' resale certificates that contain all ST-10 information. Out-of-state dealers hauling goods out of Virginia themselves use Form ST-14.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Form ST-10 (Rev. 05/26): covers all purchases \'on and after this date\' and remains in effect until revoked in writing by the Department; supplier needs only one certificate on file.',
        ],
    ],
    'expiration' => [
        'label' => 'Valid until revoked',
        'summary' => 'No expiration. The certificate stays in effect until revoked in writing by the Department of Taxation. A supplier needs only one properly executed ST-10 per dealer on file.',
        'cite' => 'Form ST-10 (Rev. 05/26); 23VAC10-210-280(A)',
    ],
    'good_faith' => [
        'summary' => 'All sales are presumed taxable. The dealer carries the burden of proof unless it takes a certificate of exemption in good faith from the purchaser. A certificate taken in good faith relieves the seller of liability for collecting the tax. Certificates that are not valid on their face cannot be accepted in good faith.',
        'cite' => 'Va. Code §58.1-623; 23VAC10-210-280(A)-(B); P.D. 19-125',
    ],
    'misuse_penalty' => [
        'summary' => 'Giving or knowingly receiving a false or fraudulent exemption certificate is a Class 1 misdemeanor. The Tax Commissioner may also suspend the exemption after 10 days\' written notice, or instead assess a penalty of up to $1,000 for misuse. Property bought on a certificate and then used becomes a taxable sale by the purchaser.',
        'cite' => 'Va. Code §58.1-636; Va. Code §58.1-623.1; Va. Code §58.1-623',
    ],
    'facts' => [
        [
            'text' => 'Form ST-10 may not be used to purchase cigarettes for resale after January 1, 2018.',
            'source_url' => 'https://www.tax.virginia.gov/sites/default/files/taxforms/exemption-certificates/any/st-10-any.pdf',
        ],
        [
            'text' => 'ST-10 also covers property bought for taxable lease or rental and packaging materials that go to the customer with the product.',
            'source_url' => 'https://www.tax.virginia.gov/sites/default/files/taxforms/exemption-certificates/any/st-10-any.pdf',
        ],
        [
            'text' => 'Equipment a registered dealer buys for use in its business, such as cash registers or showcases, is taxable even if bought on an ST-10.',
            'source_url' => 'https://law.lis.virginia.gov/admincode/title23/agency10/chapter210/section280/',
        ],
        [
            'text' => 'A dealer registered in another state that buys for resale and immediately takes the goods out of Virginia is exempt if the Virginia seller gets a valid certificate (Form ST-14).',
            'source_url' => 'https://www.tax.virginia.gov/laws-rules-decisions/rulings-tax-commissioner/25-81',
        ],
        [
            'text' => 'ST-10 must be signed by a corporate officer or authorized person, one partner, an association member, or the sole proprietor.',
            'source_url' => 'https://www.tax.virginia.gov/sites/default/files/taxforms/exemption-certificates/any/st-10-any.pdf',
        ],
    ],
    'state_notes' => 'Virginia buyers use Form ST-10, the Sales and Use Tax Certificate of Exemption, from the Virginia Department of Taxation. Check box 1 for property bought for resale. Box 2 covers property for taxable lease or rental, and box 3 covers packaging. Enter your Virginia sales and use tax account number, business name, address and kind of business. An officer, partner, member or the proprietor signs. Give the certificate to your supplier. The supplier needs only one ST-10 from you, and it stays in effect until the Department revokes it in writing. Out-of-state dealers may use ST-10 with their home-state registration number. Dealers who take goods out of Virginia themselves use Form ST-14. Virginia Tax has accepted the Multistate Tax Commission certificate in place of ST-10 if it has all the same information. ST-10 cannot be used for cigarettes or by contractors who use the materials. If you use an item yourself, it becomes a taxable sale to you. Giving a false exemption certificate is a Class 1 misdemeanor under Virginia Code section 58.1-636. The Tax Commissioner may also suspend your exemption or charge a penalty of up to $1,000.',
    'sources' => [
        [
            'title' => 'Virginia Form ST-10 (Rev. 05/26)',
            'url' => 'https://www.tax.virginia.gov/sites/default/files/taxforms/exemption-certificates/any/st-10-any.pdf',
        ],
        [
            'title' => '23VAC10-210-280 Certificates of exemption',
            'url' => 'https://law.lis.virginia.gov/admincode/title23/agency10/chapter210/section280/',
        ],
        [
            'title' => 'Va. Code §58.1-623',
            'url' => 'https://law.lis.virginia.gov/vacode/title58.1/chapter6/section58.1-623/',
        ],
        [
            'title' => 'Va. Code §58.1-623.1',
            'url' => 'https://law.lis.virginia.gov/vacode/title58.1/chapter6/section58.1-623.1/',
        ],
        [
            'title' => 'Va. Code §58.1-636',
            'url' => 'https://law.lis.virginia.gov/vacode/title58.1/chapter6/section58.1-636/',
        ],
        [
            'title' => 'Virginia Tax P.D. 25-81',
            'url' => 'https://www.tax.virginia.gov/laws-rules-decisions/rulings-tax-commissioner/25-81',
        ],
        [
            'title' => 'Virginia Tax P.D. 19-125',
            'url' => 'https://www.tax.virginia.gov/laws-rules-decisions/rulings-tax-commissioner/19-125',
        ],
        [
            'title' => 'Virginia Tax P.D. 95-316',
            'url' => 'https://www.tax.virginia.gov/laws-rules-decisions/rulings-tax-commissioner/95-316/',
        ],
    ],
];
