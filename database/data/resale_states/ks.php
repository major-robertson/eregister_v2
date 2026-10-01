<?php

/*
 * Kansas: Kansas Department of Revenue, Form ST-28A. Researched 2026-10-01
 * from ksrevenue.gov, kdor.ks.gov, mtc.gov. Generated once from the EREG-8
 * resale research; edit this file directly from now on.
 */

return [
    'state' => 'KS',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Kansas Department of Revenue',
        'short' => 'KDOR',
        'url' => 'https://www.ksrevenue.gov/',
    ],
    'resale_page_url' => 'https://www.ksrevenue.gov/pdf/pub1520.pdf',
    'form' => [
        'number' => 'ST-28A',
        'title' => 'Resale Exemption Certificate',
        'pdf_url' => 'https://www.ksrevenue.gov/pdf/st28a.pdf',
        'prescribed' => true,
        'revision' => 'Rev. 6-09',
        'notes' => 'ST-28A is for buyers registered to collect Kansas sales tax and requires a Kansas sales tax registration number. Wholesalers and out-of-state buyers not registered in Kansas use the Multi-Jurisdiction Exemption Certificate ST-28M (Rev. 7-08) or the Streamlined Sales Tax certificate PR-78SSTA. Retailer/contractors use ST-28W.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Kansas Retailers\' Sales Tax Registration Certificate',
        'number_name' => 'Kansas sales tax registration (account) number',
        'format' => '004-XXXXXXXXXF-0X: tax type 004, then the federal EIN (or a K or A number of 8 digits) followed by F, then a 2-digit suffix, e.g. 004-481880059F-01',
        'verify_url' => 'https://www.kdor.ks.gov/Apps/Misc/Miscellaneous/CertDefaultCheckRegNum',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC certificate (rev. 10/14/2022) note 13 lists Kansas; the purchaser must enter a valid Kansas registration number; resale or ingredient/component use only; not for contractors. https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'Pub KS-1520 (Rev. 6-12-25): Form PR-78SSTA (Streamlined Sales Tax Agreement Certificate of Exemption) may be used in place of ST-28A. https://www.ksrevenue.gov/pdf/pub1520.pdf',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Pub KS-1520 (Rev. 6-12-25), Resale section; ST-28A back page',
            'notes' => 'Not on ST-28A, which needs a Kansas number. Wholesalers and out-of-state buyers not registered in Kansas may use ST-28M (listing their registrations in other states) or PR-78SSTA. For drop shipments into Kansas, an out-of-state retailer without Kansas nexus may give a resale certificate from any state; one with Kansas nexus must give its Kansas number.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Pub KS-1520: all certificates in the booklet may be used as blanket certificates.',
        ],
    ],
    'expiration' => [
        'label' => 'No fixed expiration',
        'summary' => 'No fixed expiration. A blanket certificate need not be renewed or updated while there is a recurring business relationship, meaning no more than 12 months pass between sales.',
        'cite' => 'Pub KS-1520 (Rev. 6-12-25), Blanket Exemption Certificates; ST-28A instructions',
    ],
    'good_faith' => [
        'summary' => 'A certificate relieves the seller from collecting tax if the seller obtained the required identifying information and the reason for the exemption. The seller should verify the identity of the buyer, keep the fully completed certificate for at least three years, and obtain it at the time of sale and no later than 90 days after. A resale certificate without a Kansas registration number (other than in the drop-shipment case) is not acceptable and is a common audit error.',
        'cite' => 'Pub KS-1520 (Rev. 6-12-25), Accepting Exemption Certificates and Common Errors; K.S.A. 79-3651',
    ],
    'misuse_penalty' => [
        'summary' => 'A buyer who issues an exemption certificate to unlawfully avoid tax for business or personal gain is guilty of a misdemeanor, punishable by a fine of up to $1,000, up to one year in jail, or both. For misuse of a resale certificate, the director may also increase the penalty by $250 or 10 times the tax due, whichever is greater, for each transaction.',
        'cite' => 'K.S.A. 79-3651(g); Pub KS-1520 (Rev. 6-12-25), Penalties for Misuse',
    ],
    'facts' => [
        [
            'text' => 'Only inventory bought for resale is exempt. Tools, equipment, fixtures and supplies are taxable, and items bought must match the type of business (a clothing store may buy apparel, not other goods).',
            'source_url' => 'https://www.ksrevenue.gov/pdf/st28a.pdf',
        ],
        [
            'text' => 'Contractors, subcontractors and repairmen may not use ST-28A to buy materials, parts or tools; retailer/contractors use ST-28W for resale inventory. Taxable labor services from another contractor can be bought tax-free only with a Project Exemption Certificate.',
            'source_url' => 'https://www.ksrevenue.gov/pdf/st28a.pdf',
        ],
        [
            'text' => 'A Kansas sales tax number by itself is not a \'tax-exempt number\'; it only shows the buyer is a registered retailer. A certificate is still required.',
            'source_url' => 'https://www.ksrevenue.gov/pdf/pub1520.pdf',
        ],
        [
            'text' => 'Wholesalers are not required to register with KDOR unless they make retail sales, including sales to employees; they buy inventory with ST-28M.',
            'source_url' => 'https://www.ksrevenue.gov/pdf/st28m.pdf',
        ],
        [
            'text' => 'Sellers should keep certificates for at least three years from the date of sale, the usual Kansas audit period.',
            'source_url' => 'https://www.ksrevenue.gov/pdf/pub1520.pdf',
        ],
    ],
    'state_notes' => 'In Kansas, a registered retailer buying inventory uses Form ST-28A, the Resale Exemption Certificate, from the Kansas Department of Revenue (KDOR). The current version is Rev. 6-09. You may also use the Streamlined Sales Tax certificate, Form PR-78SSTA. On ST-28A you must enter your Kansas sales tax registration number. It looks like 004-481880059F-01: the tax type 004, your federal EIN followed by F, and a two-digit suffix. Describe what you sell and what you are buying, and enter the seller\'s name and address. The buyer signs and dates the form. If you are a wholesaler or an out-of-state business not registered in Kansas, use Form ST-28M, the Multi-Jurisdiction Exemption Certificate, instead. A certificate can cover all future purchases from the same seller. It does not need renewal as long as no more than 12 months pass between purchases. The seller keeps it for at least three years. Contractors may not use ST-28A for materials or tools. Misusing a certificate to avoid tax is a misdemeanor with a fine of up to $1,000, up to a year in jail, or both. KDOR can also add a penalty of $250 or 10 times the tax, whichever is greater, for each misused purchase.',
    'sources' => [
        [
            'title' => 'Form ST-28A (Rev. 6-09)',
            'url' => 'https://www.ksrevenue.gov/pdf/st28a.pdf',
        ],
        [
            'title' => 'Form ST-28M (Rev. 7-08)',
            'url' => 'https://www.ksrevenue.gov/pdf/st28m.pdf',
        ],
        [
            'title' => 'Pub KS-1520 Exemption Certificates (Rev. 6-12-25)',
            'url' => 'https://www.ksrevenue.gov/pdf/pub1520.pdf',
        ],
        [
            'title' => 'KDOR exemption certificate validation',
            'url' => 'https://www.kdor.ks.gov/Apps/Misc/Miscellaneous/CertDefaultCheckRegNum',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
