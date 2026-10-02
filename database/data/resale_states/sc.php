<?php

/*
 * South Carolina: South Carolina Department of Revenue, Form ST-8A. Researched
 * 2026-10-01 from dor.sc.gov, mtc.gov. Generated once from the EREG-8 resale
 * research; edit this file directly from now on.
 */

return [
    'state' => 'SC',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'South Carolina Department of Revenue',
        'short' => 'the SCDOR',
        'url' => 'https://dor.sc.gov/',
    ],
    'resale_page_url' => 'https://dor.sc.gov/sales-use-tax-resale-certificates-0',
    'form' => [
        'number' => 'ST-8A',
        'title' => 'Resale Certificate',
        'pdf_url' => 'https://dor.sc.gov/forms-site/Forms/ST8A.pdf',
        'prescribed' => true,
        'revision' => 'Rev. 12/29/25',
        'notes' => 'Use of ST-8A is not mandatory. Any certificate is valid if it contains the same information as ST-8A, including a letter from the purchaser, another state\'s resale certificate, or the MTC Uniform Sales & Use Tax Certificate (SC Revenue Procedure #08-2).',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'South Carolina Retail License',
        'number_name' => 'South Carolina Retail License Number / File Number',
        'format' => '9 digits (the license says \'Retail License\' in bold at the top). A wholesale exemption certificate number (SC Code 12-36-120(1)) may be used instead. SSNs, FEINs and use tax registration numbers are not acceptable.',
        'verify_url' => 'https://dor.sc.gov/verify-a-retail-license',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'SC Revenue Procedure #08-2 (https://dor.sc.gov/sales-use-tax-resale-certificates-0); SCDOR Policy Manual Chapter 8 (Sept. 2025); SC is listed on the MTC certificate rev. 10/14/22.',
        ],
        'sst' => [
            'value' => false, // not a Streamlined member; shown as not accepted rather than inheriting the seeded 'true'
            'cite' => 'South Carolina is not a Streamlined Sales Tax member state and SCDOR guidance does not mention the SST certificate. Under Rev. Proc. #08-2 any certificate is acceptable if it contains all the information ST-8A requests, so an SST certificate might qualify only if complete to that standard.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Form ST-8A, Rev. 12/29/25, instruction 3 (\'Another state\'s resale certificate and number is acceptable in this state\'); SC Revenue Procedure #08-2.',
            'notes' => 'The purchaser gives the other state and its retail license number in place of an SC retail license number.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'SC Revenue Procedure #08-2 (\'Only one resale certificate must be maintained on file per customer\'); Form ST-8A states the certificate remains in effect unless revoked or canceled in writing.',
        ],
    ],
    'expiration' => [
        'label' => 'Valid until revoked',
        'summary' => 'No expiration. The certificate remains in effect unless revoked or canceled in writing. One certificate per customer is enough.',
        'cite' => 'Form ST-8A (Rev. 12/29/25); SC Revenue Procedure #08-2',
    ],
    'good_faith' => [
        'summary' => 'The seller is relieved of the tax, and liability shifts to the purchaser, if (1) the certificate contains all information the SCDOR requires and is fully and properly completed, (2) the seller did not fraudulently fail to collect or remit the tax, and (3) the seller did not solicit the purchaser to make an unlawful resale claim. The seller must keep a copy for audit.',
        'cite' => 'S.C. Code Ann. §12-36-950 and §12-36-2510(C); SC Revenue Procedure #08-2 as modified by SC Revenue Ruling #21-15; Form ST-8A',
    ],
    'misuse_penalty' => [
        'summary' => 'A purchaser who uses a resale certificate to buy property tax free that it knows is not exempt owes the tax plus a penalty of 5% of the tax for each month or part of a month the failure continues, up to 50%, in addition to all other penalties.',
        'cite' => 'S.C. Code Ann. §12-54-43(M); SC Revenue Procedure #08-2; Form ST-8A',
    ],
    'facts' => [
        [
            'text' => 'Goods bought for resale and later withdrawn from stock for the business\'s own use must be reported as a withdrawal and taxed on fair market value, but not less than the original purchase price (Regulation 117-309.17).',
            'source_url' => 'https://dor.sc.gov/forms-site/Forms/ST8A.pdf',
        ],
        [
            'text' => 'The MTC certificate may also be used to buy taxable services for resale, such as communications, accommodations, laundry services and electricity.',
            'source_url' => 'https://dor.sc.gov/sales-use-tax-resale-certificates-0',
        ],
        [
            'text' => 'A South Carolina certificate of registration (use tax registration) is not a retail license and cannot be used on a resale certificate.',
            'source_url' => 'https://dor.sc.gov/forms-site/Forms/ST8A.pdf',
        ],
        [
            'text' => 'For drop shipments where a manufacturer ships to a retailer\'s South Carolina customer, see SC Revenue Ruling #98-8.',
            'source_url' => 'https://dor.sc.gov/resources-site/lawandpolicy/Documents/SCTIED-Chapter%208%20.pdf',
        ],
        [
            'text' => 'A seller cannot simply write the buyer\'s retail license number on the invoice. It must have a properly completed resale certificate on file to shift the liability.',
            'source_url' => 'https://dor.sc.gov/sites/dor/files/Documents/Policy%20Manuals/Chapter%2023%20-%20Frequently%20Asked%20Questions.pdf',
        ],
    ],
    'state_notes' => 'In South Carolina, buyers usually give suppliers Form ST-8A, Resale Certificate, from the South Carolina Department of Revenue (SCDOR). The form is not mandatory. Any certificate with the same information is accepted, including the Multistate Tax Commission uniform certificate or another state\'s resale certificate. Enter your 9-digit South Carolina retail license number. If you are not registered in South Carolina, enter the state and number of the retail license you hold there. A use tax registration number, SSN or FEIN does not qualify. Describe your type of business and the kinds of items you sell, lease or rent. An owner, partner or officer signs and dates the form. One certificate per supplier is enough. It stays in effect until you revoke it in writing. Your supplier keeps a copy for audit. Once your supplier accepts a complete certificate, liability for the tax shifts to you. If you later use the goods in your business, report them as a withdrawal from stock and pay the tax. If you knowingly use a resale certificate for items that are not exempt, you owe the tax plus a penalty of 5% per month, up to 50%, under South Carolina Code section 12-54-43(M).',
    'sources' => [
        [
            'title' => 'SCDOR Form ST-8A Resale Certificate (Rev. 12/29/25)',
            'url' => 'https://dor.sc.gov/forms-site/Forms/ST8A.pdf',
        ],
        [
            'title' => 'SC Revenue Procedure #08-2, Resale Certificates',
            'url' => 'https://dor.sc.gov/sales-use-tax-resale-certificates-0',
        ],
        [
            'title' => 'SC Revenue Procedure #98-2 (superseded)',
            'url' => 'https://dor.sc.gov/sales-use-tax-resale-certificate',
        ],
        [
            'title' => 'SCDOR Policy Manual Chapter 8, Sales and Use Tax Specific Provisions (Sept. 2025)',
            'url' => 'https://dor.sc.gov/resources-site/lawandpolicy/Documents/SCTIED-Chapter%208%20.pdf',
        ],
        [
            'title' => 'SCDOR Policy Manual Chapter 23, FAQs (Sept. 2025)',
            'url' => 'https://dor.sc.gov/sites/dor/files/Documents/Policy%20Manuals/Chapter%2023%20-%20Frequently%20Asked%20Questions.pdf',
        ],
        [
            'title' => 'SCDOR Verify a Retail License',
            'url' => 'https://dor.sc.gov/verify-a-retail-license',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/22)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
