<?php

/*
 * District of Columbia: District of Columbia Office of Tax and Revenue, Form
 * OTR-368. Researched 2026-10-01 from otr.cfo.dc.gov, mytax.dc.gov, mtc.gov.
 * Generated once from the EREG-8 resale research; edit this file directly from
 * now on.
 */

return [
    'state' => 'DC',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'District of Columbia Office of Tax and Revenue',
        'short' => 'OTR',
        'url' => 'https://otr.cfo.dc.gov/',
    ],
    'resale_page_url' => 'https://otr.cfo.dc.gov/node/383742',
    'form' => [
        'number' => 'OTR-368',
        'title' => 'Certificate of Resale, District of Columbia Sales and Use Tax',
        'pdf_url' => 'https://otr.cfo.dc.gov/sites/default/files/dc/sites/otr/publication/attachments/OTR-368%20CERT%20OF%20RESALE%2005.25.16.pdf',
        'prescribed' => true,
        'revision' => 'OTR-368 REV 05/16 (latest blank form found on OTR\'s site)',
        'notes' => 'Since November 1, 2017, OTR recognizes certificates of resale only on forms or copies of forms it has authorized (9 DCMR § 414.2, as quoted on OTR\'s Audit Division Exemptions page). An authorized certificate is valid for one year and must include an expiration date. OTR tells purchasers to request the certificate online by filing Form OTR-368 on MyTax.DC.gov (Sales & Use Tax account > Certificates > Certificate of Resale), where the purchaser information is pre-populated.',
    ],
    'issuer_model' => 'state_issued',
    'registration' => [
        'name' => 'District of Columbia Sales and Use Tax registration (via Form FR-500, Combined Business Tax Registration Application)',
        'number_name' => 'DC Sales and Use Tax Account ID / Registration Number',
        'format' => null,
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => false,
            'cite' => 'OTR Audit Division Exemptions page (9 DCMR § 414.2): only certificates on OTR-authorized forms are recognized since November 1, 2017. DC is not listed among the states on the MTC Uniform Resale Certificate (rev. 10/14/2022).',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'DC is not a Streamlined Sales Tax member; only OTR-authorized forms are recognized (OTR Audit Division Exemptions page).',
        ],
        'out_of_state_registration' => [
            'value' => false,
            'cite' => 'OTR-368 instructions; https://otr.cfo.dc.gov/node/383742',
            'notes' => 'The certificate is valid only with the purchaser\'s DC Sales and Use Tax registration number. Purchasers located inside or outside the District must file Form FR-500 with OTR to be eligible to use it.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'OTR-368: \'This certificate shall be considered a part of each order we shall give, provided the order contains our DC Sales and Use Tax Account ID Number\'; validity limited to one year (OTR Audit Division Exemptions page).',
        ],
    ],
    'expiration' => [
        'label' => 'Valid for 1 year',
        'summary' => 'An authorized resale certificate is valid for one year only and must show an expiration date. Purchasers request a new one each year on MyTax.DC.gov. Accepting an expired exemption certificate shows bad faith by the vendor.',
        'cite' => '9 DCMR § 414 as quoted on https://otr.cfo.dc.gov/node/383742',
    ],
    'good_faith' => [
        'summary' => 'The vendor bears the burden of proving a sale is not a retail sale unless it timely accepts, in good faith, a certificate from the purchaser. A vendor must refuse a resale certificate for property or services it knows or should know are not for resale, and must use reasonable judgment; otherwise it is not protected and owes the tax. Accepting an expired certificate shows bad faith. The seller must keep all certificates on file for audit.',
        'cite' => '9 DCMR § 414; https://otr.cfo.dc.gov/node/383742; OTR-368 instructions',
    ],
    'misuse_penalty' => [
        'summary' => 'If the purchaser buys items under the certificate that do not qualify, it should ask the seller to charge tax; otherwise it must report and pay use tax directly to OTR on its sales and use tax return (FR-800A, FR-800M, FR-800Q or FR-800SE). A specific penalty for misusing a DC certificate of resale was not confirmed.',
        'cite' => 'OTR-368 REV 05/16 instructions',
    ],
    'facts' => [
        [
            'text' => 'Since November 1, 2017, OTR recognizes only certificates of resale on forms it has authorized, so general multistate certificates are not accepted for DC.',
            'source_url' => 'https://otr.cfo.dc.gov/node/383742',
        ],
        [
            'text' => 'Purchasers request the Certificate of Resale in MyTax.DC.gov: open the Sales & Use Tax account, choose View other Options, then Certificates > Certificate of Resale, check the pre-populated purchaser information, and submit.',
            'source_url' => 'https://otr.cfo.dc.gov/sites/default/files/dc/sites/otr/publication/attachments/How_to_Request_a_Certificate_of_Resale_OTR-368_1220.pdf',
        ],
        [
            'text' => 'To use a DC certificate of resale, purchasers inside or outside the District must first register with OTR on Form FR-500, Combined Business Tax Registration Application.',
            'source_url' => 'https://otr.cfo.dc.gov/sites/default/files/dc/sites/otr/publication/attachments/OTR-368%20CERT%20OF%20RESALE%2005.25.16.pdf',
        ],
        [
            'text' => 'The certificate covers tangible personal property and services bought for resale or rental in the same form, or for incorporation as a material part of other property produced for resale or rental.',
            'source_url' => 'https://otr.cfo.dc.gov/sites/default/files/dc/sites/otr/publication/attachments/OTR-368%20CERT%20OF%20RESALE%2005.25.16.pdf',
        ],
    ],
    'state_notes' => 'In the District of Columbia, the Office of Tax and Revenue (OTR) uses Form OTR-368, the Certificate of Resale. Since November 1, 2017, OTR only recognizes resale certificates on forms it has authorized. Other states\' forms and multistate certificates are not accepted. First, register for DC sales and use tax by filing Form FR-500 with OTR. This applies whether your business is inside or outside the District. Then request your Certificate of Resale online at MyTax.DC.gov, under Certificates in your Sales & Use Tax account. The certificate must show your DC Sales and Use Tax registration number, and the paper form asks for the signature of the owner or an authorized officer and a date. Give a copy to each supplier. It covers each order you place while it is valid. It is valid for one year only and must show an expiration date, so request a new one every year. A supplier that accepts an expired certificate is treated as acting in bad faith. If you buy items under the certificate that do not qualify, tell the seller to charge tax. Otherwise you must report and pay use tax directly to OTR on your sales and use tax return.',
    'sources' => [
        [
            'title' => 'OTR Audit Division: Exemptions',
            'url' => 'https://otr.cfo.dc.gov/node/383742',
        ],
        [
            'title' => 'OTR-368 Certificate of Resale, REV 05/16',
            'url' => 'https://otr.cfo.dc.gov/sites/default/files/dc/sites/otr/publication/attachments/OTR-368%20CERT%20OF%20RESALE%2005.25.16.pdf',
        ],
        [
            'title' => 'MyTax.DC.gov User Guide: How to Request a Certificate of Resale (OTR-368)',
            'url' => 'https://otr.cfo.dc.gov/sites/default/files/dc/sites/otr/publication/attachments/How_to_Request_a_Certificate_of_Resale_OTR-368_1220.pdf',
        ],
        [
            'title' => 'MyTax.DC.gov',
            'url' => 'https://mytax.dc.gov/_/',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
