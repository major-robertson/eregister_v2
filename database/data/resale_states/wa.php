<?php

/*
 * Washington: Washington State Department of Revenue, Washington Reseller
 * Permit (issued by DOR). Researched 2026-10-01 from dor.wa.gov,
 * app.leg.wa.gov, mtc.gov. Generated once from the EREG-8 resale research;
 * edit this file directly from now on.
 */

return [
    'state' => 'WA',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Washington State Department of Revenue',
        'short' => 'the Department of Revenue (DOR)',
        'url' => 'https://dor.wa.gov/',
    ],
    'resale_page_url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits',
    'form' => [
        'number' => null,
        'title' => 'Washington Reseller Permit (issued by DOR)',
        'pdf_url' => 'https://dor.wa.gov/sites/default/files/2022-03/SampleResellerPermit.pdf',
        'prescribed' => true,
        'revision' => null,
        'notes' => 'There is no buyer-completed Washington resale form. Washington-registered buyers apply to DOR for a Reseller Permit (free) and give sellers a copy. pdf_url points to DOR\'s sample permit. Buyers not required to register in Washington instead give the Streamlined Sales Tax exemption certificate or the MTC uniform certificate (WAC 458-20-102(7)(d)).',
        'pdf_label' => 'Sample reseller permit',
    ],
    'issuer_model' => 'state_issued',
    'registration' => [
        'name' => 'Washington Reseller Permit (requires a Washington business/excise tax registration, UBI)',
        'number_name' => 'Reseller permit number',
        'format' => 'Letter-digit groups as on DOR\'s sample (e.g. \'A14 8694 13\'); the last two digits are the year the permit expires, so the number changes at each renewal.',
        'verify_url' => 'https://secure.dor.wa.gov/gteunauth/?Link=RSPVER',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'WAC 458-20-102(7)(d) (from buyers not required to register in Washington); https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits/wholesalers-need-copy-customers-reseller-permit. WA is listed on the MTC certificate rev. 10/14/22, note 31.',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'WAC 458-20-102(7)(d) (uniform exemption certificate approved by the Streamlined Sales and Use Tax Agreement, from buyers not required to register); Washington is a full SST member.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'WAC 458-20-102(7)(d); DOR \'Accepting a Reseller Permit\' (https://dor.wa.gov/sites/default/files/2026-06/AcceptingResellerPermitTranscript.txt).',
            'notes' => 'Only for buyers not required to register in Washington. They give an SST or MTC certificate showing their home-state number. Buyers registered (or required to be registered) in Washington must give a Washington Reseller Permit.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'WAC 458-20-102(7)(i): a seller that verifies permits electronically at least once per calendar year need not keep a copy for each sale; one permit copy supports purchases while it is valid.',
        ],
    ],
    'expiration' => [
        'label' => 'Usually 4 years',
        'summary' => 'Reseller permits are generally valid for 48 months (four years) from issuance, renewal or reinstatement. They are valid for 24 months for contractors, businesses open less than 12 months, and businesses with no reported income, non-reporting status, or missed returns in the last 12 months. The last two digits of the number show the expiration year. A permit is no longer valid once the business closes.',
        'cite' => 'WAC 458-20-102(4); https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits/reseller-permit-your-questions-answered',
    ],
    'good_faith' => [
        'summary' => 'A seller is not responsible for uncollected sales tax if it has a record of a reseller permit or other valid exemption certificate supporting the sale, valid on the sale date. The seller keeps a copy or electronic verification for five years after last use, and may obtain the documentation within 120 days of the sale. Sellers should verify permits at least once per calendar year. Sellers are not required to accept a permit.',
        'cite' => 'RCW 82.04.470; WAC 458-20-102(7); https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits/wholesalers-need-copy-customers-reseller-permit',
    ],
    'misuse_penalty' => [
        'summary' => 'A buyer who misuses a reseller permit owes the tax, interest and penalties plus a penalty of 50% of the tax due on the improperly purchased item. The permit may also be revoked. If someone uses the permit without the holder\'s knowledge, the penalty applies to the person who used it.',
        'cite' => 'RCW 82.32.291; WAC 458-20-102(9); DOR Sample Reseller Permit',
    ],
    'facts' => [
        [
            'text' => 'A reseller permit cannot be used for personal items, promotional items or gifts, or business supplies, tools and equipment that are not resold.',
            'source_url' => 'https://dor.wa.gov/sites/default/files/2022-03/SampleResellerPermit.pdf',
        ],
        [
            'text' => 'Contractors may get reseller permits for materials and contract labor on retail or wholesale construction, but not for public road construction, U.S. government contracting or speculative building. Contractor permits last 24 months.',
            'source_url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits/reseller-permits-contractors',
        ],
        [
            'text' => 'There is no fee for a reseller permit. DOR aims to process applications within 10 business days but it can take up to 60 days.',
            'source_url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits/reseller-permit-your-questions-answered',
        ],
        [
            'text' => 'Reseller permits are not transferable. A new owner must apply for its own permit.',
            'source_url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits/reseller-permit-your-questions-answered',
        ],
        [
            'text' => 'Sellers can verify many permits at once by uploading a CSV or tab-delimited file to DOR\'s Reseller Permit Verification Service, or check one in Business Lookup.',
            'source_url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permit-verification-service',
        ],
    ],
    'state_notes' => 'Washington does not use a buyer-completed resale certificate. If your business is registered in Washington, apply to the Washington State Department of Revenue for a Reseller Permit. It is free. Keep the original and give a copy to each supplier you buy from for resale. The permit number ends in the two digits of the year it expires. Most permits last four years. Contractors and newer businesses get two-year permits. Watch for the renewal notice about 90 days before expiration, and send suppliers your new permit. If your business is not required to register in Washington, give the supplier a Streamlined Sales Tax exemption certificate or a Multistate Tax Commission uniform certificate with your home-state number. Suppliers keep copies for five years and may check your permit with DOR\'s verification service. Use the permit only for items you will resell or that become part of a product you sell. It does not cover office supplies, tools, gifts or personal items. Misusing a permit brings a penalty of 50% of the tax due, plus the tax, interest and other penalties, under RCW 82.32.291. DOR can also revoke the permit.',
    'sources' => [
        [
            'title' => 'WA DOR Reseller permits',
            'url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits',
        ],
        [
            'title' => 'WA DOR Reseller Permit: Your questions answered',
            'url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits/reseller-permit-your-questions-answered',
        ],
        [
            'title' => 'WA DOR Wholesalers need a copy of customer\'s reseller permit',
            'url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits/wholesalers-need-copy-customers-reseller-permit',
        ],
        [
            'title' => 'WA DOR Reseller permit verification service',
            'url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permit-verification-service',
        ],
        [
            'title' => 'WA DOR Reseller permits for contractors',
            'url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits/reseller-permits-contractors',
        ],
        [
            'title' => 'WA DOR Sample Reseller Permit',
            'url' => 'https://dor.wa.gov/sites/default/files/2022-03/SampleResellerPermit.pdf',
        ],
        [
            'title' => 'WA DOR Accepting a Reseller Permit (video transcript, 2026-06)',
            'url' => 'https://dor.wa.gov/sites/default/files/2026-06/AcceptingResellerPermitTranscript.txt',
        ],
        [
            'title' => 'WAC 458-20-102 Reseller permits',
            'url' => 'https://app.leg.wa.gov/wac/default.aspx?cite=458-20-102',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/22)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
