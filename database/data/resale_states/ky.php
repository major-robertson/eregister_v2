<?php

/*
 * Kentucky: Kentucky Department of Revenue, Form 51A105. Researched 2026-10-01
 * from revenue.ky.gov, apps.legislature.ky.gov, mtc.gov. Generated once from
 * the EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'KY',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Kentucky Department of Revenue',
        'short' => 'DOR',
        'url' => 'https://revenue.ky.gov/',
    ],
    'resale_page_url' => 'https://revenue.ky.gov/News/Publications/Sales%20Tax%20Newsletters/Sales%20Tax%20Facts%20Winter%202025-2026.pdf',
    'form' => [
        'number' => '51A105',
        'title' => 'Resale Certificate',
        'pdf_url' => 'https://revenue.ky.gov/Forms/51A105%20(1-23).pdf',
        'prescribed' => true,
        'revision' => '51A105 (1-23)',
        'notes' => 'Under 103 KAR 31:111 Section 2, a resale certificate must be on Form 51A105, the Streamlined Sales and Use Tax Agreement Certificate of Exemption (Form 51A260), or the MTC Uniform Sales and Use Tax Resale Certificate.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Kentucky Sales and Use Tax Permit',
        'number_name' => 'Kentucky sales and use tax account number',
        'format' => null,
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => '103 KAR 31:111 Section 2; DOR Sales Tax Facts Winter 2025/2026, p. 7; MTC note 14 (resale only, not admissions; acts as a blanket certificate).',
        ],
        'sst' => [
            'value' => true,
            'cite' => '103 KAR 31:111 Sections 2-3 (Form 51A260); KRS 139.270(1)',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => '103 KAR 31:111 Section 3: https://apps.legislature.ky.gov/law/kar/titles/103/031/111/',
            'notes' => 'A nonresident purchaser not required to register in Kentucky may issue 51A105 (noting on its face that it is a nonresident purchaser not required to hold a Kentucky permit, and giving its home-state retail number per DOR FAQ), Form 51A260, or the MTC certificate.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => '103 KAR 31:111 Section 1(2); Form 51A105 Blanket box',
        ],
    ],
    'expiration' => [
        'label' => 'No fixed expiration',
        'summary' => 'No fixed expiration. A blanket certificate covers later purchases without new certificates as long as the character of the purchaser\'s business does not change and the purchases are of the kind it usually buys for resale.',
        'cite' => '103 KAR 31:111 Section 1(2)',
    ],
    'good_faith' => [
        'summary' => 'The seller is relieved of the burden of proof if, within 90 days after the sale, it obtains a fully completed certificate (or captures the SST data elements) and keeps it on file. If not, it is relieved if within 120 days of a DOR request it obtains a fully completed certificate for an exemption that was available, could apply to the item, and is reasonable for the buyer\'s business, or other proof. Relief is lost for fraud, soliciting unlawful exemption claims, or knowing the information was materially false. Sellers that accept a resale certificate from a contractor registered under a 900,000-series consumer number are liable for the tax.',
        'cite' => 'KRS 139.270 (eff. 3-30-2022); 103 KAR 31:111 Section 4; Form 51A105 caution to seller',
    ],
    'misuse_penalty' => [
        'summary' => 'Executing a resale certificate knowing the property will not be resold in the regular course of business, to evade tax, is a Class B misdemeanor. The purchaser is also held liable for the tax originally due and may be assessed penalties.',
        'cite' => 'KRS 139.990(1)(a); KRS 139.270(5)',
    ],
    'facts' => [
        [
            'text' => 'Form 51A105 may not be used to buy accommodations, sewer services, or admissions, and contractors registered under a 900,000-series consumer number may not issue it for any purchase.',
            'source_url' => 'https://revenue.ky.gov/Forms/51A105%20(1-23).pdf',
        ],
        [
            'text' => 'The current 51A105 (1-23) covers tangible personal property, digital property, and taxable services enumerated in KRS 139.200(2)(d)-(ay), reflecting Kentucky\'s expansion of taxable services since 2018.',
            'source_url' => 'https://revenue.ky.gov/Forms/51A105%20(1-23).pdf',
        ],
        [
            'text' => 'Supplies consumed by a service provider in performing a taxable service (for example, chemicals and fuel for a pressure washer) are not eligible for the resale exemption.',
            'source_url' => 'https://revenue.ky.gov/News/Publications/Sales%20Tax%20Newsletters/Sales%20Tax%20Facts%20Winter%202025-2026.pdf',
        ],
        [
            'text' => 'A Kentucky retailer must apply for and keep an active sales and use tax account (through the MyTaxes portal) before it can issue 51A105.',
            'source_url' => 'https://revenue.ky.gov/News/Publications/Sales%20Tax%20Newsletters/Sales%20Tax%20Facts%20Winter%202025-2026.pdf',
        ],
        [
            'text' => 'If a seller receives a certificate late, the burden of proof is on the seller; for example, a late certificate from a restaurant for silverware does not meet it, but one for disposable utensils of a kind the restaurant resells can.',
            'source_url' => 'https://apps.legislature.ky.gov/law/kar/titles/103/031/111/',
        ],
    ],
    'state_notes' => 'In Kentucky, the resale certificate is Form 51A105, Resale Certificate, from the Kentucky Department of Revenue (DOR). The current version is dated 1-23. Kentucky also accepts the Streamlined Sales Tax certificate (Form 51A260) and the MTC Uniform Sales and Use Tax Resale Certificate. On 51A105, check Blanket or Single Purchase. Enter your business name and address and your Kentucky sales and use tax permit account number. Describe what your business sells and what you are buying, and name the seller. An owner, partner or corporate officer signs, gives a title, and dates the form under penalties of perjury. If you are based in another state and not required to register in Kentucky, you may still use 51A105. Write on it that you are a nonresident purchaser not required to hold a Kentucky permit. A blanket certificate has no end date. It stays valid while your business stays the same and you buy the kinds of goods you usually resell. The seller needs the completed certificate within 90 days of the sale. The certificate cannot be used for lodging, sewer services or admissions. Contractors with a consumer number may not use it. Knowingly using a resale certificate to avoid tax is a Class B misdemeanor, and you will owe the tax plus penalties.',
    'sources' => [
        [
            'title' => 'Form 51A105 Resale Certificate (1-23)',
            'url' => 'https://revenue.ky.gov/Forms/51A105%20(1-23).pdf',
        ],
        [
            'title' => '103 KAR 31:111 Sales and purchases for resale',
            'url' => 'https://apps.legislature.ky.gov/law/kar/titles/103/031/111/',
        ],
        [
            'title' => 'KRS 139.270',
            'url' => 'https://apps.legislature.ky.gov/law/statutes/statute.aspx?id=52085',
        ],
        [
            'title' => 'KRS 139.990',
            'url' => 'https://apps.legislature.ky.gov/law/statutes/statute.aspx?id=52087',
        ],
        [
            'title' => 'DOR Sales Tax Facts Winter 2025/2026',
            'url' => 'https://revenue.ky.gov/News/Publications/Sales%20Tax%20Newsletters/Sales%20Tax%20Facts%20Winter%202025-2026.pdf',
        ],
        [
            'title' => 'DOR FAQ Sales and Use Tax',
            'url' => 'https://revenue.ky.gov/Business/Sales-Use-Tax/Documents/FAQ%20Sales%20and%20Use%20Tax%202016.pdf',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
