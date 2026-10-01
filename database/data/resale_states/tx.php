<?php

/*
 * Texas: Texas Comptroller of Public Accounts, Form 01-339. Researched
 * 2026-10-01 from comptroller.texas.gov, law.cornell.edu, texas.public.law,
 * mtc.gov. Generated once from the EREG-8 resale research; edit this file
 * directly from now on.
 */

return [
    'state' => 'TX',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Texas Comptroller of Public Accounts',
        'short' => 'the Comptroller',
        'url' => 'https://comptroller.texas.gov/',
    ],
    'resale_page_url' => 'https://comptroller.texas.gov/taxes/sales/faq/resale.php',
    'form' => [
        'number' => '01-339',
        'title' => 'Texas Sales and Use Tax Resale Certificate / Exemption Certification',
        'pdf_url' => 'https://comptroller.texas.gov/forms/01-339.pdf',
        'prescribed' => true,
        'revision' => 'Rev.4-13/8',
        'notes' => 'Front is the resale certificate; back is the Texas Sales and Use Tax Exemption Certification (no number required). A Spanish version, 01-339-S, is also published. Rule 3.285 also lets a seller accept the MTC uniform certificate.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Texas Sales and Use Tax Permit',
        'number_name' => 'Texas taxpayer number (sales and use tax permit number)',
        'format' => '11 digits',
        'verify_url' => 'https://mycpa.cpa.state.tx.us/staxpayersearch/',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => '34 Tex. Admin. Code §3.285 (seller may accept the Uniform Sales and Use Tax Certificate-Multijurisdiction promulgated by the Multistate Tax Commission); https://www.law.cornell.edu/regulations/texas/34-Tex-Admin-Code-SS-3-285. TX is also listed on the MTC certificate rev. 10/14/22, note 29.',
        ],
        'sst' => [
            'value' => false,
            'cite' => '34 Tex. Admin. Code §3.285: the Streamlined Sales and Use Tax Agreement Certificate of Exemption may not be accepted as a resale certificate.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => '34 Tex. Admin. Code §3.285; Form 01-339 has a field for the out-of-state retailer\'s registration number or Mexico RFC number.',
            'notes' => 'Out-of-state retailers not required to hold a Texas permit may give their home-state registration number. Retailers based in Mexico give their RFC number and a copy of their Mexico registration form.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => '34 Tex. Admin. Code §3.285(c); https://comptroller.texas.gov/taxes/sales/faq/resale.php',
        ],
    ],
    'expiration' => [
        'label' => 'No expiration',
        'summary' => 'No expiration. A blanket resale certificate may be relied on until the purchaser revokes it in writing. Sellers keep certificates at least four years from the date of sale and through any period in which tax may be assessed.',
        'cite' => '34 Tex. Admin. Code §3.285(c); https://comptroller.texas.gov/taxes/sales/faq/resale.php',
    ],
    'good_faith' => [
        'summary' => 'A seller does not owe tax on a sale when it accepts a properly completed resale certificate at or before the time of the transaction and does not know, and has no reason to know, that the sale is not for resale. If the purchaser\'s business would not normally resell the item, the seller should question the certificate.',
        'cite' => 'Tex. Tax Code §151.054; 34 Tex. Admin. Code §3.285; https://comptroller.texas.gov/taxes/sales/faq/resale.php',
    ],
    'misuse_penalty' => [
        'summary' => 'Knowingly giving a false resale certificate, or one for items bought for use, is a criminal offense graded by the tax evaded: Class C misdemeanor under $20, Class B $20 to $199, Class A $200 to $749, third-degree felony $750 to $19,999, second-degree felony $20,000 or more. The purchaser also owes the tax on any item used rather than resold.',
        'cite' => 'Tex. Tax Code §151.707; Form 01-339 certification text; https://comptroller.texas.gov/taxes/sales/faq/resale.php',
    ],
    'facts' => [
        [
            'text' => 'Items may be bought for resale only if they will be resold, rented or leased within the United States, its territories and possessions, or Mexico. Resale to other countries does not qualify.',
            'source_url' => 'https://comptroller.texas.gov/taxes/sales/faq/resale.php',
        ],
        [
            'text' => 'A resale certificate can also cover taxable services performed on resale inventory, property bought to lease or rent, and property used in a taxable service when control passes to the customer.',
            'source_url' => 'https://comptroller.texas.gov/taxes/sales/faq/resale.php',
        ],
        [
            'text' => 'A Texas seller may accept a resale certificate from an out-of-state retailer and ship the item directly to that retailer\'s customer in Texas (drop shipment).',
            'source_url' => 'https://www.law.cornell.edu/regulations/texas/34-Tex-Admin-Code-SS-3-285',
        ],
        [
            'text' => 'Since the April 26, 2022 amendment to Rule 3.285, a seller has 90 days (previously 60) to produce resale or exemption certificates after the Comptroller asks for them in writing during an audit.',
            'source_url' => 'https://comptroller.texas.gov/taxes/tax-policy-news/2022-july.php',
        ],
        [
            'text' => 'Do not send the completed certificate to the Comptroller. The purchaser gives it to the supplier, who keeps it for at least four years.',
            'source_url' => 'https://comptroller.texas.gov/forms/01-339.pdf',
        ],
    ],
    'state_notes' => 'Texas buyers use Form 01-339, the Texas Sales and Use Tax Resale Certificate, published by the Texas Comptroller of Public Accounts. You fill it out and give it to your supplier. Do not send it to the Comptroller. Enter your 11-digit Texas sales and use tax permit number. If you are an out-of-state retailer without a Texas permit, enter your home-state registration number instead. Retailers based in Mexico enter their RFC number and attach their Mexico registration. Describe the items you are buying and the kind of items you normally sell. The purchaser signs and dates the form. You can give one blanket certificate that covers future purchases. It does not expire. Your supplier can rely on it until you revoke it in writing. The supplier keeps it for at least four years. You may only buy items tax free if you will resell, rent or lease them in the United States or Mexico. If you use an item yourself, you owe the tax on it. Knowingly giving a false resale certificate is a crime under Texas Tax Code section 151.707. Depending on the tax evaded, it ranges from a Class C misdemeanor to a second-degree felony. Texas also accepts the Multistate Tax Commission uniform certificate, but not the Streamlined Sales Tax certificate.',
    'sources' => [
        [
            'title' => 'Texas Comptroller: Resale Certificate FAQs',
            'url' => 'https://comptroller.texas.gov/taxes/sales/faq/resale.php',
        ],
        [
            'title' => 'Texas Comptroller: Sales and Use Tax Forms',
            'url' => 'https://comptroller.texas.gov/taxes/sales/forms/',
        ],
        [
            'title' => 'Form 01-339 (Rev.4-13/8)',
            'url' => 'https://comptroller.texas.gov/forms/01-339.pdf',
        ],
        [
            'title' => '34 Tex. Admin. Code §3.285 Resale Certificate; Sales for Resale',
            'url' => 'https://www.law.cornell.edu/regulations/texas/34-Tex-Admin-Code-SS-3-285',
        ],
        [
            'title' => 'Tex. Tax Code §151.707',
            'url' => 'https://texas.public.law/statutes/tex._tax_code_section_151.707',
        ],
        [
            'title' => 'Texas Comptroller: Tax Policy News, July 2022',
            'url' => 'https://comptroller.texas.gov/taxes/tax-policy-news/2022-july.php',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/22)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
