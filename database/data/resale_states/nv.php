<?php

/*
 * Nevada: Nevada Department of Taxation, Form TAX-F005. Researched 2026-10-01
 * from tax.nv.gov, leg.state.nv.us, mynvtax.nv.gov, mtc.gov. Generated once
 * from the EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'NV',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Nevada Department of Taxation',
        'short' => 'the Department of Taxation',
        'url' => 'https://tax.nv.gov/',
    ],
    'resale_page_url' => 'https://tax.nv.gov/faqs/sales-tax-faqs/',
    'form' => [
        'number' => 'TAX-F005',
        'title' => 'Nevada Resale Certificate',
        'pdf_url' => 'https://tax.nv.gov/wp-content/uploads/2024/03/TAX-F005-Resale-Certificate-1.pdf',
        'prescribed' => true,
        'revision' => 'V2026.1',
        'notes' => 'A Spanish version is also published. NRS 372.165 requires a certificate substantially in the Department\'s form, signed by the purchaser unless submitted electronically.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Nevada Seller\'s Permit (Business and Sales Tax account)',
        'number_name' => 'Seller\'s permit Location ID number',
        'format' => null,
        'verify_url' => 'https://mynvtax.nv.gov/tap/_/#1',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC Uniform Resale Certificate (rev. 10-14-2022) lists NV, note 20: valid as a resale certificate only, not an exemption certificate; contractors should not use it.',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'Nevada is a Streamlined Sales Tax full member state; NRS 372.155(1)(b) and NRS 372.170(2) refer to sellers registered under NRS 360B.200 (SST).',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Department Sales Tax FAQs: out-of-state resale certificates are acceptable in Nevada if they contain the required elements. https://tax.nv.gov/faqs/sales-tax-faqs/',
            'notes' => 'NRS 372.155(1) gives the seller its burden-of-proof relief when the purchaser holds a Nevada permit or is registered through SST (NRS 360B.200). For drop shipments, the customer only needs to be in the business of selling the property (NRS 372.155(2)).',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Department Sales Tax FAQs: a resale certificate does not expire and stays valid until the retailer closes its account. https://tax.nv.gov/faqs/sales-tax-faqs/',
        ],
    ],
    'expiration' => [
        'label' => 'No expiration',
        'summary' => 'A Nevada resale certificate does not expire. It is valid until the purchasing retailer closes its Business and Sales Tax account. The Department calls regular verification of customers\' certificates good practice.',
        'cite' => 'https://tax.nv.gov/faqs/sales-tax-faqs/',
    ],
    'good_faith' => [
        'summary' => 'The seller is relieved of proving a sale was not at retail if it takes a resale certificate from a purchaser who sells tangible personal property, holds a Nevada permit or SST registration, and intends to resell. The seller is liable only if the purchaser\'s use tax goes unpaid and the seller fraudulently failed to collect or solicited an unlawful certificate.',
        'cite' => 'NRS 372.155(1); NRS 372.170(1)(b)',
    ],
    'misuse_penalty' => [
        'summary' => 'If the purchaser uses the property for anything other than retention, demonstration or display, it owes use tax measured by its purchase price. Giving a resale certificate for property the person knows will not be resold, to evade the tax, is a misdemeanor.',
        'cite' => 'NRS 372.170(1)(a); NRS 372.175 (https://www.leg.state.nv.us/nrs/nrs-372.html)',
    ],
    'facts' => [
        [
            'text' => 'On drop shipments, a third-party vendor is relieved of proof if it takes a resale certificate or other acceptable evidence from a customer who is in the business of selling tangible personal property and sells it in the regular course of business.',
            'source_url' => 'https://www.leg.state.nv.us/nrs/nrs-372.html',
        ],
        [
            'text' => 'Contractors are generally consumers of the materials they use and should not use a resale certificate; sellers should not accept one from a contractor (NAC 372.200).',
            'source_url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
        [
            'text' => 'If a buyer commingles fungible goods bought for resale with other goods, sales from the mix are treated as sales of the resale goods first.',
            'source_url' => 'https://www.leg.state.nv.us/nrs/nrs-372.html',
        ],
        [
            'text' => 'A buyer who only rents out property while holding it for sale may elect to report tax on the rental receipts instead of the purchase price.',
            'source_url' => 'https://www.leg.state.nv.us/nrs/nrs-372.html',
        ],
        [
            'text' => 'The 2026 revision of TAX-F005 (V2026.1) asks for the seller\'s permit Location ID number, the purchaser\'s printed name and a location address.',
            'source_url' => 'https://tax.nv.gov/wp-content/uploads/2024/03/TAX-F005-Resale-Certificate-1.pdf',
        ],
    ],
    'state_notes' => 'Nevada uses Form TAX-F005, the Nevada Resale Certificate, from the Nevada Department of Taxation. You fill it out as the buyer and give it to your supplier. Enter your seller\'s permit Location ID number, describe what your business sells, name the supplier and describe what you are buying. Print your name, sign and date it. If you send it electronically, a signature is not needed. The certificate does not expire. It stays valid until you close your Nevada sales tax account. The Department says sellers should check their customers\' permits from time to time, and offers a permit search for that. Out-of-state resale certificates are accepted if they include the same details. The Department also takes the Multistate Tax Commission certificate, but only for resale. Contractors generally cannot use a resale certificate for materials. If you use something you bought tax-free for any purpose other than holding it for sale, you owe use tax on what you paid. Giving a certificate for goods you know you will not resell, to avoid the tax, is a misdemeanor under NRS 372.175.',
    'sources' => [
        [
            'title' => 'TAX-F005 Nevada Resale Certificate (V2026.1)',
            'url' => 'https://tax.nv.gov/wp-content/uploads/2024/03/TAX-F005-Resale-Certificate-1.pdf',
        ],
        [
            'title' => 'Nevada Department of Taxation Sales Tax FAQs',
            'url' => 'https://tax.nv.gov/faqs/sales-tax-faqs/',
        ],
        [
            'title' => 'NRS Chapter 372 Sales and Use Taxes',
            'url' => 'https://www.leg.state.nv.us/nrs/nrs-372.html',
        ],
        [
            'title' => 'MyNVTax permit search',
            'url' => 'https://mynvtax.nv.gov/tap/_/#1',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10-14-2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
