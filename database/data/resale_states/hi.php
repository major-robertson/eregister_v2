<?php

/*
 * Hawaii: Hawaii Department of Taxation, Form G-17. Researched 2026-10-01 from
 * tax.hawaii.gov, files.hawaii.gov, mtc.gov. Generated once from the EREG-8
 * resale research; edit this file directly from now on.
 */

return [
    'state' => 'HI',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Hawaii Department of Taxation',
        'short' => 'DOTAX',
        'url' => 'https://tax.hawaii.gov/',
    ],
    'resale_page_url' => 'https://tax.hawaii.gov/forms/a1_b2_1geuse/',
    'form' => [
        'number' => 'G-17',
        'title' => 'Resale Certificate for Goods, General Form 1',
        'pdf_url' => 'https://files.hawaii.gov/tax/forms/current/g17.pdf',
        'prescribed' => true,
        'revision' => 'Rev. 2016',
        'notes' => 'Hawaii prescribes three resale certificates for goods: two general forms (G-17 General Form 1 and G-18 General Form 2) and a special form (G-19) that sellers may use for sales to contractors on a particular project (HAR § 18-237-13-02(d)). Sales of services and amusements that qualify as wholesale use Form G-82 (Rev. 2016). The certificate is given to the seller and not sent to DOTAX.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Hawaii General Excise Tax (GET) License',
        'number_name' => 'Hawaii Tax Identification Number (GE number)',
        'format' => 'GE-XXX-XXX-XXXX-XX (GE followed by 12 digits, as laid out on Form G-17)',
        'verify_url' => 'https://dotax.ehawaii.gov/tls/app',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC Uniform Resale Certificate (rev. 10/14/2022) note 10, citing DOTAX Tax Information Release 93-5 (Nov. 10, 1993) and TIR 98-8 (Oct. 30, 1998): the certificate lets the seller claim the lower wholesale GET rate (or no GET on imported goods resold at wholesale); not usable for services bought for resale (note 5).',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'Hawaii is not a Streamlined Sales Tax member; HAR § 18-237-13-02(d) prescribes Hawaii\'s own forms, and only the MTC form is recognized by TIR 93-5.',
        ],
        'out_of_state_registration' => [
            'value' => false,
            'cite' => 'Form G-17 (purchaser certifies it holds a Hawaii Tax Identification No. under the General Excise Tax Law); HAR § 18-237-13-02(d)(5)(B)',
            'notes' => 'The prescribed certificate is built around the purchaser\'s Hawaii GE license; a purchaser must revoke its certificates if it no longer holds a GE license. No DOTAX guidance found accepting another state\'s registration number in its place.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'HAR § 18-237-13-02(d)(2)(A): a general-form certificate applies to each sale by that seller to that purchaser until revoked in writing.',
        ],
    ],
    'expiration' => [
        'label' => 'Valid until revoked',
        'summary' => 'No expiration. A general-form resale certificate applies to every sale of goods from that seller to the purchaser until the purchaser revokes it in writing, except orders the purchaser specifies in writing are not covered. The purchaser must revoke it if it no longer holds a GE license, its license number changes, or facts change materially. GE licenses require one-time registration and no renewal.',
        'cite' => 'HAR § 18-237-13-02(d)(2)(A), (d)(5)(B); https://tax.hawaii.gov/geninfo/licensing/',
    ],
    'good_faith' => [
        'summary' => 'Hawaii\'s GET is on the seller, and the certificate supports the seller\'s use of the 0.5% wholesale rate instead of the 4% retail rate. The seller must refuse a certificate that is not in proper form, or when, given the property, the buyer\'s business and its trade practices, the sales appear not to be wholesale. It must inquire when it has reason to believe some sales are not wholesale, and keep all certificates and notices. Without a proper certificate, sales are presumed not to be at wholesale, unless the seller sells exclusively at wholesale.',
        'cite' => 'HAR § 18-237-13-02(d)(2)(C), (d)(4)',
    ],
    'misuse_penalty' => [
        'summary' => 'If a sale under the certificate is not in fact a wholesale sale, the purchaser must pay the seller, on demand, the additional tax assessed against the seller (for example $4 instead of $0.50 on a $100 sale). The purchaser signs under the penalties of HRS § 231-36, which covers false and fraudulent statements, and the purchaser\'s duties are enforced under the penalties of HRS §§ 237-41, 237-48 and 231-34.',
        'cite' => 'HAR § 18-237-13-02(d)(2)(B), (d)(5); Form G-17 (Rev. 2016); HRS § 237-13(2)(F)(i)',
    ],
    'facts' => [
        [
            'text' => 'Hawaii has a general excise tax on the seller, not a sales tax on the buyer. A resale certificate lets the seller pay GET at the 0.5% wholesale rate instead of 4% on sales of goods for resale.',
            'source_url' => 'https://files.hawaii.gov/tax/legal/har/har_237.pdf',
        ],
        [
            'text' => 'For imported goods, no GET applies when the purchaser certifies to the seller who imported the goods into Hawaii that it will resell them at wholesale (MTC note 10, citing DOTAX TIR 98-8).',
            'source_url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
        [
            'text' => 'Form G-19 is a special resale certificate sellers may use for sales of materials to contractors for a particular project. Wholesale sales of services and amusements are documented on Form G-82.',
            'source_url' => 'https://tax.hawaii.gov/forms/a1_b2_1geuse/',
        ],
        [
            'text' => 'Anyone can look up a Hawaii tax license with \'Search Tax Licenses\' on Hawaii Tax Online. A GE license costs $20 and is a one-time registration.',
            'source_url' => 'https://tax.hawaii.gov/geninfo/licensing/',
        ],
        [
            'text' => 'HAR § 18-237-13-02.01 sets the GET rules for out-of-state sellers and drop shipments of goods delivered to customers in Hawaii.',
            'source_url' => 'https://files.hawaii.gov/tax/legal/har/har_237.pdf',
        ],
    ],
    'state_notes' => 'In Hawaii, use Form G-17, the Resale Certificate for Goods (General Form 1), from the Hawaii Department of Taxation (DOTAX). Hawaii has a general excise tax (GET) on the seller, not a sales tax. Your certificate lets the seller pay the 0.5% wholesale rate instead of the 4% retail rate. Enter the seller\'s name and address. Enter your Hawaii Tax Identification Number, which starts with GE. Describe the nature of your business and check whether you buy for resale at retail or at wholesale. Sign and add your printed name, title and the date. An owner, partner, member, officer or authorized agent may sign. The certificate covers every purchase of goods from that seller until you revoke it in writing. You must revoke it if you no longer hold a GE license or your license number changes. The seller keeps it; do not send it to DOTAX. If a purchase was not really for resale, you must pay the seller the extra tax it is charged. You sign under the penalties of Hawaii law for false statements.',
    'sources' => [
        [
            'title' => 'DOTAX General Excise and Use Tax forms',
            'url' => 'https://tax.hawaii.gov/forms/a1_b2_1geuse/',
        ],
        [
            'title' => 'Form G-17 Resale Certificate General Form 1 (Rev. 2016)',
            'url' => 'https://files.hawaii.gov/tax/forms/current/g17.pdf',
        ],
        [
            'title' => 'HAR Chapter 18-237 (unofficial compilation as of 12/31/2025)',
            'url' => 'https://files.hawaii.gov/tax/legal/har/har_237.pdf',
        ],
        [
            'title' => 'DOTAX Licensing Information',
            'url' => 'https://tax.hawaii.gov/geninfo/licensing/',
        ],
        [
            'title' => 'TIR 93-5: Use in Hawaii of the Uniform Sales and Use Tax Certificate',
            'url' => 'https://files.hawaii.gov/tax/legal/tir/1990_09/tir93-5.pdf',
        ],
        [
            'title' => 'TIR 98-8',
            'url' => 'https://files.hawaii.gov/tax/legal/tir/1990_09/tir98-8.pdf',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
