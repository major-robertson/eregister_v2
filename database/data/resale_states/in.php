<?php

/*
 * Indiana: Indiana Department of Revenue, Form ST-105. Researched 2026-10-01
 * from forms.in.gov, in.gov, secure.in.gov, codes.findlaw.com. Generated once
 * from the EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'IN',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Indiana Department of Revenue',
        'short' => 'DOR',
        'url' => 'https://www.in.gov/dor/',
    ],
    'resale_page_url' => 'https://www.in.gov/dor/tax-forms/business/sales-tax-forms/',
    'form' => [
        'number' => 'ST-105',
        'title' => 'General Sales Tax Exemption Certificate (State Form 49065)',
        'pdf_url' => 'https://forms.in.gov/Download.aspx?id=2717',
        'prescribed' => true,
        'revision' => 'State Form 49065 (R7 / 6-23)',
        'notes' => 'ST-105 is a multi-use exemption certificate; the resale box is \'Sales to a retailer, wholesaler, or manufacturer for resale only.\' Indiana also accepts the Streamlined Sales Tax Certificate of Exemption (SSTGB Form F0003). Vehicle and watercraft dealers buying from other dealers use ST-105D.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Registered Retail Merchant Certificate (RRMC)',
        'number_name' => 'Indiana Taxpayer Identification Number (TID) and location (LOC) number',
        'format' => '10-digit TID followed by a 3-digit LOC number',
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => false,
            'cite' => 'Indiana is not listed on the MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022): https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf. DOR guidance names only ST-105 and the SST certificate (Indiana Audit Manual; Sales Tax Information Bulletin #57).',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'Indiana is a full SST member and accepts the SST Certificate of Exemption (F0003): https://secure.in.gov/dor/files/audit-manual.pdf; https://secure.in.gov/dor/files/sib57.pdf',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'ST-105 Section 1 and instructions: https://forms.in.gov/Download.aspx?id=2717',
            'notes' => 'A purchaser without an Indiana TID may enter its State Tax ID Number from another state and the state of issue. DOR may ask for proof of the other-state registration.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'ST-105 Section 3 (blanket or single purchase); IC 6-2.5-8-8(d)',
        ],
    ],
    'expiration' => [
        'label' => 'No fixed expiration',
        'summary' => 'No fixed expiration is set by statute or on Form ST-105. A blanket certificate stays in effect for the purchases it describes. Sellers must keep certificates on file to support exempt sales.',
        'cite' => 'IC 6-2.5-8-8; Form ST-105 (R7 / 6-23); 45 IAC 2.2-8-12',
    ],
    'good_faith' => [
        'summary' => 'A properly completed exemption certificate relieves the seller of liability for collecting the tax. All sections of ST-105 must be complete or the exemption is not valid and the seller is responsible for the tax. A seller that accepts an incomplete certificate is not relieved unless it obtains a fully completed certificate within 90 days after the sale, or within 120 days after DOR requests substantiation.',
        'cite' => 'IC 6-2.5-8-8(e)-(f); Indiana DOR Audit Manual (exemption certificates relieve the seller); ST-105 instructions',
    ],
    'misuse_penalty' => [
        'summary' => 'The purchaser signs under penalties of perjury. Negligent or intentional misuse, or fraudulent use, of the certificate can subject the signer personally and the business to tax, interest, and civil or criminal penalties.',
        'cite' => 'Form ST-105 (R7 / 6-23), Section 5 certification',
    ],
    'facts' => [
        [
            'text' => 'ST-105 cannot be used to buy utilities, vehicles, watercraft, aircraft or gasoline, and may not be issued by a nonprofit organization.',
            'source_url' => 'https://forms.in.gov/Download.aspx?id=2717',
        ],
        [
            'text' => 'A valid ST-105 also serves as an exemption certificate for the county innkeeper\'s tax and local food and beverage tax.',
            'source_url' => 'https://forms.in.gov/Download.aspx?id=2717',
        ],
        [
            'text' => 'Drop shipments: the reseller may give the supplier an ST-105 or SST Form F0003; the reseller\'s customer cannot issue a certificate to the supplier. Since January 1, 2024, the remote seller threshold is $100,000 in gross revenue only; the 200-transaction test was removed by SEA 228 (2024).',
            'source_url' => 'https://secure.in.gov/dor/files/sib57.pdf',
        ],
        [
            'text' => 'If all sections cannot be completed, the seller must charge tax and the purchaser can file a refund claim on Form GA-110L.',
            'source_url' => 'https://forms.in.gov/Download.aspx?id=2717',
        ],
        [
            'text' => 'Under 45 IAC 2.2-8-12, sellers must keep exemption certificates for at least three years; without one, the burden is on the seller to prove tax was collected or the item was used for an exempt purpose.',
            'source_url' => 'https://www.law.cornell.edu/regulations/indiana/45-IAC-2.2-8-12',
        ],
    ],
    'state_notes' => 'In Indiana, buyers use Form ST-105, the General Sales Tax Exemption Certificate, from the Indiana Department of Revenue (DOR). The current version is State Form 49065 (R7 / 6-23). Indiana also accepts the Streamlined Sales Tax certificate (Form F0003). On ST-105, enter your business name and address. Then give your Indiana Taxpayer Identification Number (TID), which is 10 digits, plus the 3-digit location (LOC) number. Both appear on your Registered Retail Merchant Certificate. If you are based in another state and not registered in Indiana, enter your tax ID from your home state and name that state. Add the seller\'s name and address. Check blanket or single purchase and describe the items. Check the resale box. The purchaser signs and dates the form and prints a name and title. Every section must be filled in, or the seller must charge tax. The form has no expiration date. The seller keeps it on file; do not send it to DOR. ST-105 cannot be used for utilities, vehicles, watercraft, aircraft or gasoline. You sign under penalties of perjury. If you misuse the certificate, you and your business can owe the tax, interest, and civil or criminal penalties.',
    'sources' => [
        [
            'title' => 'Form ST-105 (R7 / 6-23)',
            'url' => 'https://forms.in.gov/Download.aspx?id=2717',
        ],
        [
            'title' => 'Indiana DOR Sales Tax Forms',
            'url' => 'https://www.in.gov/dor/tax-forms/business/sales-tax-forms/',
        ],
        [
            'title' => 'Sales Tax Information Bulletin #57, Drop Shipments (March 2024)',
            'url' => 'https://secure.in.gov/dor/files/sib57.pdf',
        ],
        [
            'title' => 'Indiana DOR Revised 2026 Audit Manual',
            'url' => 'https://secure.in.gov/dor/files/audit-manual.pdf',
        ],
        [
            'title' => 'IC 6-2.5-8-8 (FindLaw, cross-check of statute text)',
            'url' => 'https://codes.findlaw.com/in/title-6-taxation/in-code-sect-6-2-5-8-8/',
        ],
        [
            'title' => '45 IAC 2.2-8-12',
            'url' => 'https://www.law.cornell.edu/regulations/indiana/45-IAC-2.2-8-12',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
