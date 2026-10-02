<?php

/*
 * California: California Department of Tax and Fee Administration, Form
 * CDTFA-230. Researched 2026-10-01 from cdtfa.ca.gov,
 * onlineservices.cdtfa.ca.gov, mtc.gov. Generated once from the EREG-8 resale
 * research; edit this file directly from now on.
 */

return [
    'state' => 'CA',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'California Department of Tax and Fee Administration',
        'short' => 'CDTFA',
        'url' => 'https://cdtfa.ca.gov/',
    ],
    'resale_page_url' => 'https://cdtfa.ca.gov/formspubs/pub103/valid-resale-certificates.htm',
    'form' => [
        'number' => 'CDTFA-230',
        'title' => 'General Resale Certificate (California Resale Certificate)',
        'pdf_url' => 'https://cdtfa.ca.gov/formspubs/cdtfa230.pdf',
        'prescribed' => false,
        'revision' => 'REV. 1 (8-17)',
        'notes' => 'CDTFA provides Form CDTFA-230 but does not require it: \'Any document, including a letter, note, purchase order, or preprinted form, can serve as a resale certificate\' if it contains the essential elements of Regulation 1668(b)(1). CDTFA also publishes industry versions (for example CDTFA-230-A, CDTFA-230-F).',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'California Seller\'s Permit',
        'number_name' => 'California seller\'s permit number',
        'format' => null,
        'verify_url' => 'https://onlineservices.cdtfa.ca.gov/?Link=PermitSearch',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC Uniform Resale Certificate (rev. 10/14/2022) lists CA with note 4: valid only as a resale certificate under Regulation 1668, not as an exemption certificate; a valid resale certificate is effective until revoked. CDTFA Publication 103 accepts any document containing the Regulation 1668 essential elements.',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'California is not a Streamlined Sales Tax member and CDTFA names no SST form. Any document with all Regulation 1668(b)(1) elements can serve as a resale certificate, so an SST form would be judged only on those elements.',
        ],
        'out_of_state_registration' => [
            'value' => false,
            'cite' => 'Regulation 1668(b)(1); https://cdtfa.ca.gov/lawguides/vol1/sutr/1706.html',
            'notes' => 'California does not substitute another state\'s registration number for a California seller\'s permit number. A purchaser not required to hold a California permit (for example an out-of-state retailer that makes no sales in California) may instead give a sufficient explanation of why it is not required to hold one. For drop shipments to California customers, Regulation 1706 treats a drop shipper engaged in business in California as the retailer, and a resale certificate from a true retailer without a California seller\'s permit (or use tax registration or qualifying marketplace status) does not relieve it.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'https://cdtfa.ca.gov/formspubs/pub103/documenting-and-reporting-sales.htm (\'Repeat customers ... can ... provide one blanket resale certificate\'); Regulation 1668',
        ],
    ],
    'expiration' => [
        'label' => 'Valid until revoked',
        'summary' => 'A resale certificate remains in effect until revoked in writing. A blanket certificate does not cover a purchase that the buyer\'s purchase order marks as taxable.',
        'cite' => 'Cal. Code Regs., tit. 18, § 1668(a) (Regulation 1668)',
    ],
    'good_faith' => [
        'summary' => 'A seller that timely accepts a resale certificate in good faith does not owe the tax on that sale. Timely means before the seller bills the buyer, within the seller\'s normal billing and payment cycle, or at or before delivery. Without evidence to the contrary, a seller is presumed to have acted in good faith if the certificate has all essential elements and appears valid on its face. Sellers can check a permit online or by calling 1-888-225-5263.',
        'cite' => 'Regulation 1668; Rev. & Tax. Code § 6091-6092; https://cdtfa.ca.gov/formspubs/pub103/documenting-and-reporting-sales.htm',
    ],
    'misuse_penalty' => [
        'summary' => 'A purchaser who uses property bought under a resale certificate for anything other than resale (or demonstration and display while held for sale) owes use tax on the purchase price. A person who knowingly misuses a resale certificate for personal gain or to evade tax is liable, for each purchase, for the tax plus a penalty of 10 percent of the tax or $500, whichever is more. Knowingly giving a resale certificate for an item the person will not resell before use can also be a misdemeanor.',
        'cite' => 'Rev. & Tax. Code §§ 6072, 6094.5; Regulation 1668(d); Form CDTFA-230 \'For Your Information\' paragraph',
    ],
    'facts' => [
        [
            'text' => 'A valid California resale certificate must show the purchaser\'s name and address, its seller\'s permit number (or an explanation of why it is not required to hold one), a description of the property, a statement that the property is bought for resale, the date, and the signature of the purchaser, its employee or authorized representative.',
            'source_url' => 'https://cdtfa.ca.gov/formspubs/pub103/valid-resale-certificates.htm',
        ],
        [
            'text' => 'On a drop shipment to a California customer, a drop shipper that is engaged in business in California is treated as the retailer, and a resale certificate from an out-of-state true retailer only helps if it shows a valid California seller\'s permit, use tax registration or qualifying marketplace sale.',
            'source_url' => 'https://cdtfa.ca.gov/lawguides/vol1/sutr/1706.html',
        ],
        [
            'text' => 'The resale certificate covers tangible personal property bought for resale, including property that becomes an ingredient or component of an item manufactured for resale. It is not an exemption certificate; other exemptions use their own certificates.',
            'source_url' => 'https://cdtfa.ca.gov/lawguides/vol1/sutr/1668.html',
        ],
        [
            'text' => 'Regulation 1668 was last amended effective July 1, 2016, adding subdivision (j) on purchases of counterfeit goods.',
            'source_url' => 'https://cdtfa.ca.gov/lawguides/vol1/sutr/1668.html',
        ],
        [
            'text' => 'Sellers can verify a buyer\'s seller\'s permit with CDTFA\'s online \'Verify a Permit, License, or Account\' search.',
            'source_url' => 'https://onlineservices.cdtfa.ca.gov/?Link=PermitSearch',
        ],
    ],
    'state_notes' => 'In California, the usual form is CDTFA-230, the General Resale Certificate, from the California Department of Tax and Fee Administration (CDTFA). CDTFA does not require that exact form. Any document works if it has all the required parts. Enter your California seller\'s permit number. If you are not required to hold a California seller\'s permit, for example because you make no sales in California, you must explain why instead. Describe what you sell and the items you are buying for resale. The certificate must be signed by you, your employee or an authorized representative, and dated. One blanket certificate can cover repeat purchases from the same supplier. It stays in effect until you revoke it in writing. Your supplier should get it before billing you or by delivery, and can check your permit online with CDTFA. If you use an item yourself instead of reselling it, you owe use tax on its purchase price. If you knowingly misuse a resale certificate to avoid tax, you owe the tax plus a penalty of 10 percent of the tax or $500, whichever is more, for each purchase. Misuse can also be a misdemeanor.',
    'sources' => [
        [
            'title' => 'CDTFA Publication 103: Valid Resale Certificates',
            'url' => 'https://cdtfa.ca.gov/formspubs/pub103/valid-resale-certificates.htm',
        ],
        [
            'title' => 'CDTFA Publication 103: Documenting and Reporting Sales',
            'url' => 'https://cdtfa.ca.gov/formspubs/pub103/documenting-and-reporting-sales.htm',
        ],
        [
            'title' => 'Form CDTFA-230 General Resale Certificate',
            'url' => 'https://cdtfa.ca.gov/formspubs/cdtfa230.pdf',
        ],
        [
            'title' => 'Regulation 1668, Sales for Resale',
            'url' => 'https://cdtfa.ca.gov/lawguides/vol1/sutr/1668.html',
        ],
        [
            'title' => 'Regulation 1706, Drop Shipments',
            'url' => 'https://cdtfa.ca.gov/lawguides/vol1/sutr/1706.html',
        ],
        [
            'title' => 'CDTFA Verify a Permit, License, or Account',
            'url' => 'https://onlineservices.cdtfa.ca.gov/?Link=PermitSearch',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
