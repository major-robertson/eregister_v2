<?php

/*
 * Massachusetts: Massachusetts Department of Revenue, Form ST-4. Researched
 * 2026-10-01 from mass.gov, mtc.gov. Generated once from the EREG-8 resale
 * research; edit this file directly from now on.
 */

return [
    'state' => 'MA',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Massachusetts Department of Revenue',
        'short' => 'DOR',
        'url' => 'https://www.mass.gov/orgs/massachusetts-department-of-revenue',
    ],
    'resale_page_url' => 'https://www.mass.gov/guides/sales-and-use-tax',
    'form' => [
        'number' => 'ST-4',
        'title' => 'Sales Tax Resale Certificate',
        'pdf_url' => 'https://www.mass.gov/doc/form-st-4-sales-tax-resale-certificate/download',
        'prescribed' => true,
        'revision' => 'Rev. 8/16',
        'notes' => '830 CMR 64H.8.1 requires a resale certificate in the form prescribed by the Commissioner (Form ST-4). Manufacturers claiming exempt use use Form ST-12; exempt organizations use Form ST-5.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Massachusetts Vendor\'s Registration (sales and use tax registration, issued through MassTaxConnect)',
        'number_name' => 'Account ID number or Federal ID number',
        'format' => null,
        'verify_url' => 'https://mtc.dor.state.ma.us/mtc/_/',
    ],
    'accepts' => [
        'mtc' => [
            'value' => false,
            'cite' => '830 CMR 64H.8.1(4)(c) requires the form prescribed by the Commissioner (ST-4); Massachusetts is not listed on the MTC certificate (rev. 10/14/2022).',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'Massachusetts is not an SST member; 830 CMR 64H.8.1(4)(c) requires Form ST-4.',
        ],
        'out_of_state_registration' => [
            'value' => false,
            'cite' => 'Form ST-4 Notice to Vendors 2 and Notice to Purchasers 2; DOR Sales and Use Tax guide: https://www.mass.gov/guides/sales-and-use-tax',
            'notes' => 'The purchaser must hold a valid Massachusetts vendor registration. A Massachusetts vendor cannot accept a resale certificate from a customer without Massachusetts nexus, but may accept instead a statement on the customer\'s letterhead (or with its business card attached) signed under the pains and penalties of perjury (Directive 89-10); for drop shipments, a notarized letterhead statement (Letter Ruling 85-35).',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Form ST-4 checkbox: Single purchase certificate or Blanket certificate',
        ],
    ],
    'expiration' => [
        'label' => 'No expiration',
        'summary' => 'No expiration is set by the form or regulation. The vendor keeps the certificate as part of its permanent tax records.',
        'cite' => 'Form ST-4 (Rev. 8/16), Notice to Vendors 5; 830 CMR 64H.8.1',
    ],
    'good_faith' => [
        'summary' => 'A resale certificate relieves the vendor of the burden of proof only if taken in good faith from a purchaser engaged in selling that kind of property or service who holds a valid Massachusetts registration. Good faith is questioned if the vendor knows facts suggesting the buyer will not resell, such as the buyer not being in the business of selling that merchandise. On written notice, the vendor has 60 days to produce or correct certificates; otherwise it must prove the sale was not a retail sale by other evidence.',
        'cite' => 'M.G.L. c. 64H, sec. 8; 830 CMR 64H.8.1(4)(b) and (d); Form ST-4 Notice to Vendors 2-3',
    ],
    'misuse_penalty' => [
        'summary' => 'If the buyer uses the property other than for retention, demonstration or display while holding it for sale, the use is taxable as of first use. Willful misuse may result in criminal tax evasion sanctions of up to one year in prison and fines of $10,000 ($50,000 for corporations).',
        'cite' => 'Form ST-4 (Rev. 8/16) warning; 830 CMR 64H.8.1(4)(e)',
    ],
    'facts' => [
        [
            'text' => 'Sales Tax Resale Certificates are invalid for the sale or purchase of tobacco products.',
            'source_url' => 'https://www.mass.gov/guides/sales-and-use-tax',
        ],
        [
            'text' => 'Vendors can confirm customers\' sales and use tax registrations and resale certificates online through MassTaxConnect.',
            'source_url' => 'https://www.mass.gov/guides/sales-and-use-tax',
        ],
        [
            'text' => 'The only taxable services in Massachusetts for resale-certificate purposes are telecommunications services; see 830 CMR 64H.1.6 for their resale rules.',
            'source_url' => 'https://www.mass.gov/regulations/830-CMR-64h81-resale-and-exempt-use-certificates',
        ],
        [
            'text' => 'A Massachusetts business that ships goods to a Massachusetts consumer for a retailer without Massachusetts nexus must collect tax unless it gets a notarized letterhead statement from that retailer (Letter Ruling 85-35; TIR 04-26).',
            'source_url' => 'https://www.mass.gov/guides/sales-and-use-tax',
        ],
        [
            'text' => 'Service businesses unsure whether property they buy qualifies for resale should consult 830 CMR 64H.1.1 (Service Enterprises).',
            'source_url' => 'https://www.mass.gov/doc/form-st-4-sales-tax-resale-certificate/download',
        ],
    ],
    'state_notes' => 'In Massachusetts, buyers use Form ST-4, Sales Tax Resale Certificate, from the Massachusetts Department of Revenue (DOR). The current version is Rev. 8/16. You must hold a valid Massachusetts vendor registration to use it. Enter your business name and address and your Massachusetts Account ID number or Federal ID number. State your type of business and the type of goods or services you are buying, as specifically as you can. Enter the vendor\'s name and address. Check single purchase or blanket. The purchaser signs under the penalties of perjury, with title and date. The form has no expiration date, and the vendor keeps it permanently. Businesses without Massachusetts nexus cannot use ST-4. They may give a signed statement on their letterhead instead. The certificate cannot be used for tobacco products. Vendors can check your registration on MassTaxConnect. If you use goods bought for resale in your business, you owe the tax from the time of first use. Willful misuse can bring criminal penalties of up to one year in prison and fines of $10,000, or $50,000 for a corporation.',
    'sources' => [
        [
            'title' => 'Form ST-4 Sales Tax Resale Certificate (Rev. 8/16)',
            'url' => 'https://www.mass.gov/doc/form-st-4-sales-tax-resale-certificate/download',
        ],
        [
            'title' => '830 CMR 64H.8.1 Resale and Exempt Use Certificates',
            'url' => 'https://www.mass.gov/regulations/830-CMR-64h81-resale-and-exempt-use-certificates',
        ],
        [
            'title' => 'DOR Sales and Use Tax guide',
            'url' => 'https://www.mass.gov/guides/sales-and-use-tax',
        ],
        [
            'title' => 'MA DOR Sales and Use Tax Forms',
            'url' => 'https://www.mass.gov/lists/ma-dor-sales-and-use-tax-forms',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
