<?php

/*
 * Utah: Utah State Tax Commission, Form TC-721. Researched 2026-10-01 from
 * files.tax.utah.gov, le.utah.gov, law.cornell.edu, streamlinedsalestax.org.
 * Generated once from the EREG-8 resale research; edit this file directly from
 * now on.
 */

return [
    'state' => 'UT',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Utah State Tax Commission',
        'short' => 'the Tax Commission',
        'url' => 'https://tax.utah.gov/',
    ],
    'resale_page_url' => 'https://tax.utah.gov/sales',
    'form' => [
        'number' => 'TC-721',
        'title' => 'Exemption Certificate (Sales, Use, Tourism and Motor Vehicle Rental Tax)',
        'pdf_url' => 'https://tax.utah.gov/forms/current/tc-721.pdf',
        'prescribed' => true,
        'revision' => null,
        'notes' => 'Multi-purpose exemption certificate; the buyer checks \'Resale or Re-lease\'. Governments and schools use TC-721G; religious and charitable organizations use TC-721RC. Utah is a full Streamlined Sales Tax member, so the SST certificate is also accepted, and Utah is listed on the MTC uniform certificate. The direct URL is behind a Cloudflare check; the same file is served at https://files.tax.utah.gov/tax/forms/current/tc-721.pdf.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Utah sales and use tax license',
        'number_name' => 'Sales Tax License Number',
        'format' => null,
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'Utah is listed on the MTC Uniform Sales & Use Tax Resale Certificate rev. 10/14/22 with no Utah restriction note (https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf).',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'Utah is a Streamlined Sales Tax full member (https://www.streamlinedsalestax.org/Shared-Pages/exemptions-); Utah Admin. Code R865-19S-23 allows electronic exemption evidence on the SST governing board form.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Utah State Tax Commission private letter ruling 99-029 (https://files.tax.utah.gov/tax/commission/ruling/99-029.htm); SSTGB Form F0003 instructions.',
            'notes' => 'A purchaser not registered in Utah may enter its home-state sales tax number. Under the SST certificate, Utah also accepts a foreign (e.g. VAT) number.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Utah Publication 25 (Rev. 10/24), Exemption Certificates: a seller may use a certificate on file for future purchases (https://files.tax.utah.gov/tax/forms/2025/pub-25.pdf).',
        ],
    ],
    'expiration' => [
        'label' => 'No fixed expiration',
        'summary' => 'A certificate on file covers future purchases, but the seller must get a new one if more than 12 months have passed since the buyer\'s last purchase. The buyer must tell the seller if the certificate is cancelled or changed. The Commission has also suggested renewing certificates every three years as routine practice.',
        'cite' => 'Utah Publication 25 (Rev. 10/24), Exemption Certificates; private letter ruling 99-029',
    ],
    'good_faith' => [
        'summary' => 'A seller can accept exemption certificates at face value and is not liable for improper exemptions unless it takes part in a fraudulent claim. A seller that takes a certificate with the required information is not liable to collect the tax on that sale. If the certificate is missing, the seller has 120 days after the Commission\'s request to obtain one taken in good faith.',
        'cite' => 'Utah Code Ann. §59-12-106(3); Utah Publication 25 (Rev. 10/24)',
    ],
    'misuse_penalty' => [
        'summary' => 'A buyer who uses or consumes items bought tax free for resale must report and pay the sales tax on its next return. The seller\'s protection does not apply if the purchaser knowingly gave materially false information. General Utah tax penalties and interest apply to unpaid tax.',
        'cite' => 'Form TC-721 resale certification; Utah Code Ann. §59-12-106(3); Utah Publication 58 (Interest and Penalties)',
    ],
    'facts' => [
        [
            'text' => 'Products bought for resale in the regular course of business, either in their original form or as ingredients or components of a manufactured product, are exempt.',
            'source_url' => 'https://files.tax.utah.gov/tax/forms/2025/pub-25.pdf',
        ],
        [
            'text' => 'The TC-721 resale box also covers food, beverages and similar items dispensed from vending machines (Rule R865-19S-74); the buyer reports and pays tax directly on those sales.',
            'source_url' => 'https://files.tax.utah.gov/tax/forms/current/tc-721.pdf',
        ],
        [
            'text' => 'Exemption information may be given on paper (signed) or electronically, but an electronic certificate must contain all the information on the paper form.',
            'source_url' => 'https://files.tax.utah.gov/tax/forms/2025/pub-25.pdf',
        ],
        [
            'text' => 'Sellers must document out-of-state sales with a bill of lading or similar shipping record.',
            'source_url' => 'https://files.tax.utah.gov/tax/forms/2025/pub-25.pdf',
        ],
        [
            'text' => 'Do not send the TC-721 to the Tax Commission. The seller keeps it with its records in case of audit.',
            'source_url' => 'https://files.tax.utah.gov/tax/forms/current/tc-721.pdf',
        ],
    ],
    'state_notes' => 'Utah buyers use Form TC-721, Exemption Certificate, from the Utah State Tax Commission. Check the box for Resale or Re-lease. Enter your business name and address, the seller\'s name, and your Utah sales tax license number. If you are not registered in Utah, enter your home state\'s sales tax number. The buyer signs the paper form. Electronic certificates must carry the same information. Give the certificate to the seller. Do not send it to the Tax Commission. Utah also accepts the Streamlined Sales Tax certificate and the Multistate Tax Commission uniform certificate. The seller may keep your certificate on file for future purchases. If more than 12 months pass between your purchases, the seller must get a new certificate. Tell the seller if your certificate changes or is cancelled. A seller can accept the certificate at face value and is not liable unless it took part in a false claim. If you use or consume anything you bought tax free for resale, you must report and pay the sales tax on your next return. If you knowingly give false information, you owe the tax plus penalties and interest.',
    'sources' => [
        [
            'title' => 'Utah TC-721 Exemption Certificate',
            'url' => 'https://files.tax.utah.gov/tax/forms/current/tc-721.pdf',
        ],
        [
            'title' => 'Utah Publication 25, Sales and Use Tax General Information (Rev. 10/24)',
            'url' => 'https://files.tax.utah.gov/tax/forms/2025/pub-25.pdf',
        ],
        [
            'title' => 'Utah Code Ann. §59-12-106 (eff. 5/3/2023)',
            'url' => 'https://le.utah.gov/xcode/Title59/Chapter12/C59-12-S106_2023050320230503.html',
        ],
        [
            'title' => 'Utah Admin. Code R865-19S-23',
            'url' => 'https://www.law.cornell.edu/regulations/utah/Utah-Admin-Code-R865-19S-23',
        ],
        [
            'title' => 'Utah private letter ruling 99-029',
            'url' => 'https://files.tax.utah.gov/tax/commission/ruling/99-029.htm',
        ],
        [
            'title' => 'SSTGB Exemptions page',
            'url' => 'https://www.streamlinedsalestax.org/Shared-Pages/exemptions-',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/22)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
