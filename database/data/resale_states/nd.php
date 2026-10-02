<?php

/*
 * North Dakota: North Dakota Office of State Tax Commissioner, Form SFN 21950.
 * Researched 2026-10-01 from tax.nd.gov, ndlegis.gov. Generated once from the
 * EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'ND',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'North Dakota Office of State Tax Commissioner',
        'short' => 'the Tax Commissioner',
        'url' => 'https://www.tax.nd.gov/',
    ],
    'resale_page_url' => 'https://www.tax.nd.gov/forms',
    'form' => [
        'number' => 'SFN 21950',
        'title' => 'Certificate of Resale',
        'pdf_url' => 'https://www.tax.nd.gov/sites/www/files/documents/forms/business/sales-use/resale-cert1.pdf',
        'prescribed' => true,
        'revision' => '3-2026',
        'notes' => 'N.D. Admin. Code 81-04.1-01-15 also accepts the MTC uniform certificate and the SST certificate of exemption.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'North Dakota Sales and Use Tax Permit',
        'number_name' => 'Sales and use tax permit number',
        'format' => null,
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'N.D. Admin. Code 81-04.1-01-15 (https://ndlegis.gov/prod/acdata/pdf/81-04.1-01.pdf)',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'N.D. Admin. Code 81-04.1-01-15; North Dakota is an SST full member (NDCC ch. 57-39.4).',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'SFN 21950 (3-2026) asks for the state that issued the buyer\'s sales and use tax permit (\'I hereby certify that I hold [Enter State] Sales and Use Tax permit number\'). https://www.tax.nd.gov/sites/www/files/documents/forms/business/sales-use/resale-cert1.pdf',
            'notes' => 'For drop shipments, NDCC 57-39.4-18(1)(h) lets the drop shipper accept the reseller\'s certificate whether or not the reseller is registered in North Dakota.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'N.D. Admin. Code 81-04.1-01-15: a new certificate is not needed for each sale.',
        ],
    ],
    'expiration' => [
        'label' => 'Renew every 2 years',
        'summary' => 'No expiration is printed on the form, but the Tax Commissioner\'s guideline says exemption certificates should be obtained from retailers at least every two years.',
        'cite' => 'Guideline \'Sales, Use and Gross Receipts Tax Requirements\' (content revised 10-2025): https://www.tax.nd.gov/sites/www/files/documents/guidelines/business/sales-use/sales-use-and-gross-receipts-tax-requirements-1.pdf',
    ],
    'good_faith' => [
        'summary' => 'A seller that obtains a fully completed certificate (or the required data elements) within 90 days after the sale is relieved of the tax if the buyer claimed the exemption improperly; the buyer is then liable. If the state asks for proof later, the seller has 120 days to get a certificate taken in good faith or other proof. Relief does not cover sellers who fraudulently fail to collect or solicit unlawful claims.',
        'cite' => 'NDCC 57-39.4-18(2)-(4) (https://ndlegis.gov/cencode/t57c39-4.pdf)',
    ],
    'misuse_penalty' => [
        'summary' => 'If a claimed resale turns out not to be exempt, the tax and penalty are collected from the buyer. A person who gives a seller a false certificate is liable for any tax and penalties on the sale. By signing SFN 21950 the buyer agrees to report and remit tax and penalties on items it uses or consumes.',
        'cite' => 'N.D. Admin. Code 81-04.1-01-15; SFN 21950 certification',
    ],
    'facts' => [
        [
            'text' => 'A contractor that takes materials from stock bought for resale and uses them on a construction contract must pay use tax on its cost.',
            'source_url' => 'https://www.tax.nd.gov/sites/www/files/documents/guidelines/business/sales-use/sales-use-and-gross-receipts-tax-requirements-1.pdf',
        ],
        [
            'text' => 'Certificates are not sent to the Tax Commissioner; the supplier keeps them to support sales claimed as for resale.',
            'source_url' => 'https://www.tax.nd.gov/sites/www/files/documents/guidelines/business/sales-use/sales-use-and-gross-receipts-tax-requirements-1.pdf',
        ],
        [
            'text' => 'A drop shipper may claim the resale exemption from its customer\'s certificate even if the customer is not registered in North Dakota.',
            'source_url' => 'https://ndlegis.gov/cencode/t57c39-4.pdf',
        ],
        [
            'text' => 'A paper certificate needs a signature; electronic exemption claims do not.',
            'source_url' => 'https://ndlegis.gov/cencode/t57c39-4.pdf',
        ],
        [
            'text' => 'SFN 21950 was reissued in March 2026 (3-2026), replacing the 11-2002 version.',
            'source_url' => 'https://www.tax.nd.gov/sites/www/files/documents/forms/business/sales-use/resale-cert1.pdf',
        ],
    ],
    'state_notes' => 'North Dakota uses form SFN 21950, the Certificate of Resale, from the North Dakota Office of State Tax Commissioner. The buyer fills it out and gives it to the seller. Enter the state that issued your sales and use tax permit and the permit number. This can be a North Dakota permit or one from another state. Describe what you sell, lease or rent, and name the seller. Sign and date it. Do not send it to the Tax Commissioner. One certificate covers future purchases from that seller. The Tax Commissioner\'s guidance says sellers should get a new certificate at least every two years. North Dakota also accepts the Multistate Tax Commission uniform certificate and the Streamlined Sales Tax certificate of exemption. The seller is protected if it has a completed certificate within 90 days of the sale. By signing, you agree to pay tax and penalties on anything you use yourself instead of reselling. If you give a seller a false certificate, you are liable for the tax and penalties on the sale.',
    'sources' => [
        [
            'title' => 'SFN 21950 Certificate of Resale (3-2026)',
            'url' => 'https://www.tax.nd.gov/sites/www/files/documents/forms/business/sales-use/resale-cert1.pdf',
        ],
        [
            'title' => 'N.D. Admin. Code ch. 81-04.1-01 (81-04.1-01-15 Certificate of resale)',
            'url' => 'https://ndlegis.gov/prod/acdata/pdf/81-04.1-01.pdf',
        ],
        [
            'title' => 'NDCC ch. 57-39.4 Streamlined Sales and Use Tax (57-39.4-18)',
            'url' => 'https://ndlegis.gov/cencode/t57c39-4.pdf',
        ],
        [
            'title' => 'Guideline: Sales, Use and Gross Receipts Tax Requirements',
            'url' => 'https://www.tax.nd.gov/sites/www/files/documents/guidelines/business/sales-use/sales-use-and-gross-receipts-tax-requirements-1.pdf',
        ],
        [
            'title' => 'Tax Forms Search',
            'url' => 'https://www.tax.nd.gov/forms',
        ],
    ],
];
