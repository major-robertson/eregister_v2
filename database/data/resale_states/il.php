<?php

/*
 * Illinois: Illinois Department of Revenue, Form CRT-61. Researched 2026-10-01
 * from tax.illinois.gov, ilga.gov, mtc.gov. Generated once from the EREG-8
 * resale research; edit this file directly from now on.
 */

return [
    'state' => 'IL',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Illinois Department of Revenue',
        'short' => 'IDOR',
        'url' => 'https://tax.illinois.gov/',
    ],
    'resale_page_url' => 'https://tax.illinois.gov/businesses/crtinfo.html',
    'form' => [
        'number' => 'CRT-61',
        'title' => 'Certificate of Resale (Sales and Related Taxes, Fees, and E911 Surcharge)',
        'pdf_url' => 'https://tax.illinois.gov/content/dam/soi/en/web/tax/forms/sales/documents/sales/crt-61.pdf',
        'prescribed' => false,
        'revision' => 'R-04/25',
        'notes' => 'CRT-61 is the Department\'s form, but purchasers may make their own certificate (or use a signed purchase order) if it contains every element listed in 86 Ill. Adm. Code 130.1405(b): seller and purchaser names and addresses, a description of the property, a resale statement, signature and date, and an Illinois account ID, resale number, or an out-of-state purchaser certification.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Illinois Certificate of Registration (retailer) or reseller registration with IDOR',
        'number_name' => 'Illinois Account ID (retailer or reseller account ID; historically \'resale number\')',
        'format' => '8 digits, shown on CRT-61 as ____-____',
        'verify_url' => 'https://mytax.illinois.gov/',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022), note 12: https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf. The Illinois number must be entered; no other state\'s number is acceptable; not usable for services.',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'Illinois is not a Streamlined Sales Tax member state; any certificate must meet 86 Ill. Adm. Code 130.1405 (Illinois account ID or resale number). https://tax.illinois.gov/businesses/crtinfo.html',
        ],
        'out_of_state_registration' => [
            'value' => false,
            'cite' => '35 ILCS 120/2c; 86 Ill. Adm. Code 130.1405(b)(5)(C); CRT-61 Step 2',
            'notes' => 'Limited exception only: an out-of-state purchaser not required to register in Illinois may certify on CRT-61 that it will resell and deliver the property only to purchasers outside Illinois, and list its other-state registration number. A purchaser who resells in Illinois needs an active Illinois account ID or resale number. The MTC note says no other state\'s number is acceptable.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => '86 Ill. Adm. Code 130.1405(c); CRT-61 Steps 4 (full blanket) and 5 (percentage blanket)',
        ],
    ],
    'expiration' => [
        'label' => 'Update every 3 years',
        'summary' => 'No statutory expiration. Blanket certificates, including percentage blankets, should be updated at least once every three years, and a new certificate given if a stated percentage changes.',
        'cite' => '86 Ill. Adm. Code 130.1405(c)(1); CRT-61 Instructions (R-04/25)',
    ],
    'good_faith' => [
        'summary' => 'Illinois does not use a good-faith standard. A correct certificate is prima facie proof of a sale for resale. The seller must confirm that the purchaser has an active Illinois registration or resale number at the time of sale (IDOR\'s Verify a Registered Business tool). Without an active number and a resale certification, the sale is presumed not for resale; the presumption can be rebutted with other evidence.',
        'cite' => '35 ILCS 120/2c; 86 Ill. Adm. Code 130.1405(b) and (d); MTC certificate note 12',
    ],
    'misuse_penalty' => [
        'summary' => 'A purchaser who obtains a registration or resale number by misrepresentation, claims to hold one when it does not, or uses its number to make a seller believe a purchase is for resale when it knows it is not, is guilty of a Class 4 felony. The purchaser also owes the tax plus penalties and interest, and IDOR may cancel a misused resale number.',
        'cite' => '35 ILCS 120/13; 35 ILCS 120/2c; CRT-61 Step 6',
    ],
    'facts' => [
        [
            'text' => 'Since January 1, 2025, a purchase by a lessor of tangible personal property that is subject to Illinois\'s new leasing tax, made for the purpose of leasing that property, is a tax-free sale for resale (P.A. 103-592). The MTC certificate\'s Illinois note, which says Illinois has no resale exemption for lease property, predates this change.',
            'source_url' => 'https://www.ilga.gov/legislation/ilcs/fulltext.asp?DocName=003501200K2c',
        ],
        [
            'text' => 'A Certificate of Registration alone does not exempt a purchase; the purchaser must give a Certificate of Resale (CRT-61 or an equivalent signed statement, including a signed purchase order).',
            'source_url' => 'https://tax.illinois.gov/questionsandanswers/answer.281.html',
        ],
        [
            'text' => 'Sellers must keep certificates for at least three and one-half years and should not mail them to IDOR unless asked.',
            'source_url' => 'https://tax.illinois.gov/content/dam/soi/en/web/tax/forms/sales/documents/sales/CRT-61Instr.pdf',
        ],
        [
            'text' => 'Illinois does not allow a resale certificate (including the MTC form) to be used to buy taxable services for resale.',
            'source_url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
        [
            'text' => 'Retailers with an active ST-556 account may use CRT-61 to buy titled items for resale, such as vehicles, watercraft, aircraft and trailers; a transaction return must still be filed.',
            'source_url' => 'https://tax.illinois.gov/content/dam/soi/en/web/tax/forms/sales/documents/sales/CRT-61Instr.pdf',
        ],
    ],
    'state_notes' => 'In Illinois, the resale certificate is Form CRT-61, Certificate of Resale, from the Illinois Department of Revenue (IDOR). The current version is R-04/25. You may also write your own certificate or use a signed purchase order, as long as it has every required item. Enter the seller\'s name and address and your own name and address. Then give your Illinois account ID. This is the 8-digit number IDOR assigns when you register as a retailer or reseller. An out-of-state buyer may skip the Illinois number only if it is not required to register in Illinois and will resell and deliver the goods only to customers outside Illinois. In that case, list your home state and its registration number. Describe the goods, or check the blanket box if every purchase from this seller is for resale. The purchaser or an authorized employee signs and dates the form. A blanket certificate has no fixed end date, but IDOR says to update it at least every three years. The seller should check your account ID with IDOR\'s Verify a Registered Business tool on MyTax Illinois. If you use the certificate for items you do not resell, you owe the tax plus penalties and interest. Knowingly misusing a registration or resale number to avoid tax is a Class 4 felony.',
    'sources' => [
        [
            'title' => 'IDOR: Certificate of Resale',
            'url' => 'https://tax.illinois.gov/businesses/crtinfo.html',
        ],
        [
            'title' => 'Form CRT-61 (R-04/25)',
            'url' => 'https://tax.illinois.gov/content/dam/soi/en/web/tax/forms/sales/documents/sales/crt-61.pdf',
        ],
        [
            'title' => 'CRT-61 Instructions (R-04/25)',
            'url' => 'https://tax.illinois.gov/content/dam/soi/en/web/tax/forms/sales/documents/sales/CRT-61Instr.pdf',
        ],
        [
            'title' => '86 Ill. Adm. Code 130.1405',
            'url' => 'https://ilga.gov/commission/jcar/admincode/086/086001300N14050R.html',
        ],
        [
            'title' => '35 ILCS 120/2c',
            'url' => 'https://www.ilga.gov/legislation/ilcs/fulltext.asp?DocName=003501200K2c',
        ],
        [
            'title' => '35 ILCS 120/13',
            'url' => 'https://www.ilga.gov/legislation/ilcs/fulltext.asp?DocName=003501200K13',
        ],
        [
            'title' => 'IDOR Q&A 281: Certificate of Registration not enough',
            'url' => 'https://tax.illinois.gov/questionsandanswers/answer.281.html',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
