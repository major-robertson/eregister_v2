<?php

/*
 * Ohio: Ohio Department of Taxation, Form STEC B. Researched 2026-10-01 from
 * dam.assets.ohio.gov, codes.ohio.gov, mtc.gov. Generated once from the EREG-8
 * resale research; edit this file directly from now on.
 */

return [
    'state' => 'OH',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Ohio Department of Taxation',
        'short' => 'the Department of Taxation',
        'url' => 'https://tax.ohio.gov/',
    ],
    'resale_page_url' => 'https://tax.ohio.gov/business/sales-and-use-tax/exemption-certificates',
    'form' => [
        'number' => 'STEC B',
        'title' => 'Sales and Use Tax Blanket Exemption Certificate',
        'pdf_url' => 'https://dam.assets.ohio.gov/image/upload/tax.ohio.gov/forms/fill-in/sales_and_use/exemption_certificates/st_stec_b_fi.pdf',
        'prescribed' => true,
        'revision' => 'Rev. 7/25',
        'notes' => 'Ohio has no resale-only form. Buyers use STEC B for continuing purchases from one vendor or STEC U (Rev. 7/25, https://dam.assets.ohio.gov/image/upload/tax.ohio.gov/forms/fill-in/sales_and_use/exemption_certificates/st_stec_u_fi.pdf) for a single purchase, writing \'resale\' as the reason. Substitute certificates with the required data elements, the SST certificate (incorporated in Ohio Adm.Code 5703-9-03) and the MTC certificate are also allowed.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Ohio Vendor\'s License (county or transient), or seller\'s use tax account for out-of-state sellers',
        'number_name' => 'Vendor\'s license number',
        'format' => null,
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC Uniform Resale Certificate (rev. 10-14-2022) lists OH, note 23: the buyer must state which reason for exemption applies, or the certificate is disallowed on audit.',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'Ohio Adm.Code 5703-9-03 incorporates the SST Certificate of Exemption (revised December 2021); Ohio is an SST full member.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'STEC B / STEC U (Rev. 7/25): \'Vendor\'s license number, if applicable\'; Ohio Adm.Code 5703-9-03 requires a tax ID issued by Ohio \'if any\'.',
            'notes' => 'Ohio does not require an Ohio number from a buyer that has none. The SST instructions say a buyer not required to register in Ohio may enter any Ohio-issued tax ID, or \'Not Required\'.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Form STEC B (Rev. 7/25), Sales and Use Tax Blanket Exemption Certificate.',
        ],
    ],
    'expiration' => [
        'label' => 'No expiration',
        'summary' => 'No expiration date is set on STEC B. It covers all purchases from the named vendor made under the certificate until the buyer\'s basis for the claim changes or it is withdrawn.',
        'cite' => 'Form STEC B (Rev. 7/25); R.C. 5739.03(B)',
    ],
    'good_faith' => [
        'summary' => 'A vendor that obtains a fully completed exemption certificate is relieved of collecting the tax on the covered sales; if the exemption was improper, the buyer owes the tax. Relief does not apply to vendors who fraudulently fail to collect, solicit unlawful claims, or accept a certificate the state has said is unavailable. If no certificate is obtained within 90 days after the sale, tax is presumed to apply; after an assessment notice the vendor has 120 days to prove the sale was not taxable.',
        'cite' => 'R.C. 5739.03(B) (effective Sept. 30, 2025): https://codes.ohio.gov/ohio-revised-code/section-5739.03',
    ],
    'misuse_penalty' => [
        'summary' => 'A buyer who improperly claims an exemption owes the tax. Presenting a false certificate to a vendor is prohibited; a first offense is fined $25 to $100, with higher fines and up to 60 days in jail for individuals on later offenses.',
        'cite' => 'R.C. 5739.03(B); R.C. 5739.26; R.C. 5739.99(A) (https://codes.ohio.gov/ohio-revised-code/section-5739.99)',
    ],
    'facts' => [
        [
            'text' => 'An exemption certificate missing any required element (name and address, any Ohio tax ID, type of business, reason, and signature on paper) is invalid.',
            'source_url' => 'https://codes.ohio.gov/ohio-administrative-code/rule-5703-9-03',
        ],
        [
            'text' => 'Construction contractors cannot use STEC B or STEC U for materials incorporated into real property under an exempt construction contract; they follow Ohio Adm.Code 5703-9-14 and use Form STEC CO.',
            'source_url' => 'https://dam.assets.ohio.gov/image/upload/tax.ohio.gov/forms/fill-in/sales_and_use/exemption_certificates/st_stec_b_fi.pdf',
        ],
        [
            'text' => 'Vendors of motor vehicles, titled watercraft and titled outboard motors may buy those items under the resale exception with STEC B or STEC U.',
            'source_url' => 'https://dam.assets.ohio.gov/image/upload/tax.ohio.gov/forms/fill-in/sales_and_use/exemption_certificates/st_stec_b_fi.pdf',
        ],
        [
            'text' => 'The Department publishes a weekly List of Active Vendors report with county and transient vendor\'s licenses, direct pay permits and out-of-state seller\'s use tax accounts.',
            'source_url' => 'https://dam.assets.ohio.gov/image/upload/tax.ohio.gov/sales_and_use/vendors%20license%20information%20page%20.pdf',
        ],
        [
            'text' => 'Both STEC B and STEC U were revised in July 2025 (Rev. 7/25).',
            'source_url' => 'https://dam.assets.ohio.gov/image/upload/tax.ohio.gov/forms/fill-in/sales_and_use/exemption_certificates/st_stec_u_fi.pdf',
        ],
    ],
    'state_notes' => 'Ohio has no separate resale form. The Ohio Department of Taxation uses general exemption certificates. For ongoing purchases from one supplier, use Form STEC B, the Sales and Use Tax Blanket Exemption Certificate. For a single purchase, use Form STEC U. Write the supplier\'s name, then state your reason, such as "resale." Ohio disallows a certificate that does not give a valid reason. Add your name, type of business and address. Enter your Ohio vendor\'s license number if you have one. Buyers without an Ohio number can still use the form. Sign, add your title and date it. A blanket certificate has no set expiration and covers all your purchases from that supplier while the reason still applies. The Streamlined Sales Tax certificate and the Multistate Tax Commission certificate are also accepted. The supplier should have the certificate within 90 days of the sale, or tax is presumed due. If you claim an exemption you are not entitled to, you owe the tax. Giving a supplier a false certificate is also an offense, with a fine of $25 to $100 for a first offense.',
    'sources' => [
        [
            'title' => 'STEC B Sales and Use Tax Blanket Exemption Certificate (Rev. 7/25)',
            'url' => 'https://dam.assets.ohio.gov/image/upload/tax.ohio.gov/forms/fill-in/sales_and_use/exemption_certificates/st_stec_b_fi.pdf',
        ],
        [
            'title' => 'STEC U Sales and Use Tax Unit Exemption Certificate (Rev. 7/25)',
            'url' => 'https://dam.assets.ohio.gov/image/upload/tax.ohio.gov/forms/fill-in/sales_and_use/exemption_certificates/st_stec_u_fi.pdf',
        ],
        [
            'title' => 'R.C. 5739.03',
            'url' => 'https://codes.ohio.gov/ohio-revised-code/section-5739.03',
        ],
        [
            'title' => 'R.C. 5739.99',
            'url' => 'https://codes.ohio.gov/ohio-revised-code/section-5739.99',
        ],
        [
            'title' => 'Ohio Adm.Code 5703-9-03 Exemption certificate forms',
            'url' => 'https://codes.ohio.gov/ohio-administrative-code/rule-5703-9-03',
        ],
        [
            'title' => 'Using the List of Active Vendors Report',
            'url' => 'https://dam.assets.ohio.gov/image/upload/tax.ohio.gov/sales_and_use/vendors%20license%20information%20page%20.pdf',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10-14-2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
