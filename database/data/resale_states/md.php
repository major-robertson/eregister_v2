<?php

/*
 * Maryland: Comptroller of Maryland, Suggested Blanket Resale Certificate.
 * Researched 2026-10-01 from marylandtaxes.gov, mgaleg.maryland.gov,
 * dsd.maryland.gov, interactive.marylandtaxes.gov. Generated once from the
 * EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'MD',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Comptroller of Maryland',
        'short' => 'the Comptroller',
        'url' => 'https://www.marylandtaxes.gov/',
    ],
    'resale_page_url' => 'https://www.marylandtaxes.gov/forms/Business_Tax_Tips/bustip4.pdf',
    'form' => [
        'number' => null,
        'title' => 'Suggested Blanket Resale Certificate',
        'pdf_url' => 'https://www.marylandtaxes.gov/forms/compliance_forms/Sample-Resale-Certificate.pdf',
        'prescribed' => false,
        'revision' => null,
        'notes' => 'No specific form is required (COMAR 03.06.01.14). Any signed certificate is accepted if it gives the buyer\'s name, address and 8-digit Maryland sales and use tax registration number and states that the purchase is for resale. The Comptroller publishes a suggested blanket certificate, and its Verify Exempt Status tool can print a prepared resale certificate.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Maryland sales and use tax license',
        'number_name' => 'Maryland sales and use tax registration number',
        'format' => '8 digits, first digit 0 or 1 (temporary show licenses: 10 digits beginning 7125)',
        'verify_url' => 'https://interactive.marylandtaxes.gov/Business/VerifyExempt/User/Home.aspx',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC certificate (rev. 10/14/2022) note 16; COMAR 03.06.01.14 (no particular form required)',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'Maryland is not an SST member; COMAR 03.06.01.14 requires a Maryland registration number on any resale certificate.',
        ],
        'out_of_state_registration' => [
            'value' => false,
            'cite' => 'Md. Code, Tax-Gen. 11-408(b)(1)(iii); COMAR 03.06.01.14; Business Tax Tip #4',
            'notes' => 'Numbers issued by other jurisdictions are not valid, except for sales of antiques or used collectibles to out-of-state buyers not required to hold a Maryland license, who must attach a copy of their home-state license (or a trader\'s license from a state without sales tax). Out-of-state buyers may claim a refund on Form ST 212.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'COMAR 03.06.01.14; Business Tax Tip #4',
        ],
    ],
    'expiration' => [
        'label' => 'Valid until revoked',
        'summary' => 'No fixed expiration. A blanket certificate stays in force until revoked; later purchase orders need only carry the buyer\'s Maryland registration number. Sellers should review certificates periodically for accuracy.',
        'cite' => 'Comptroller Suggested Blanket Resale Certificate; COMAR 03.06.01.14; Business Tax Tip #4',
    ],
    'good_faith' => [
        'summary' => 'The vendor\'s duty to collect tax is waived if it obtains a signed resale certificate with the required content before the sale. If the Comptroller gives notice of intent to assess for missing certificates, the vendor has 60 days from the mailing date to obtain them, or the assessment is final. A vendor may never accept a certificate when it knows or should know the sale is not for resale, and may not accept one for a cash, check or card sale under $200 unless it delivers the goods to the buyer\'s retail place of business.',
        'cite' => 'Md. Code, Tax-Gen. 11-408(b)(3)-(5); COMAR 03.06.01.14',
    ],
    'misuse_penalty' => [
        'summary' => 'A buyer that does not resell the items owes the tax. Sellers that accept certificates they know or should know are not for resale are liable for the uncollected tax plus penalties and interest.',
        'cite' => 'Business Tax Tip #4; Md. Code, Tax-Gen. 11-408(b)(3)',
    ],
    'facts' => [
        [
            'text' => 'A sales and use tax license alone does not exempt purchases; the buyer must give the seller a resale certificate.',
            'source_url' => 'https://www.marylandtaxes.gov/forms/Business_Tax_Tips/bustip4.pdf',
        ],
        [
            'text' => 'Drop shipments: a Maryland vendor selling to an unregistered out-of-state vendor for delivery to a Maryland customer must either get a resale certificate bearing a Maryland registration number or charge the out-of-state vendor tax.',
            'source_url' => 'https://dsd.maryland.gov/regulations/Pages/03.06.01.14.aspx',
        ],
        [
            'text' => 'Federal EINs, Social Security numbers, numbers with letters, and other states\' numbers are not valid on a Maryland resale certificate; religious organizations may use their 8-digit exemption number beginning with 29.',
            'source_url' => 'https://dsd.maryland.gov/regulations/Pages/03.06.01.14.aspx',
        ],
        [
            'text' => 'A buyer that paid tax on items bought for resale may take a credit on its next return, up to the lesser of $1,000 or the tax due, or file Form ST 212 for a refund.',
            'source_url' => 'https://www.marylandtaxes.gov/forms/Business_Tax_Tips/bustip4.pdf',
        ],
        [
            'text' => 'Digital products and digital codes may be bought tax-free for resale, including when incorporated into another digital product, a taxable service, or a physical product.',
            'source_url' => 'https://marylandtaxes.gov/forms/Business_Tax_Tips/bustip29.pdf',
        ],
    ],
    'state_notes' => 'Maryland has no required resale certificate form. The Comptroller of Maryland publishes a Suggested Blanket Resale Certificate that you may use. Any signed certificate works if it gives your business name and address, your Maryland sales and use tax registration number, and a statement that you are buying for resale. Your registration number has 8 digits and starts with 0 or 1. It is on your sales and use tax license. Other states\' numbers are not accepted, except for purchases of antiques and used collectibles. The MTC uniform certificate is accepted only if it shows your Maryland number. Enter the seller\'s name, then sign and date. A blanket certificate stays in force until you revoke it. After that, your purchase orders only need to show your Maryland number. The seller must have the certificate before the sale. Sellers cannot accept a certificate for cash, check or card sales under $200 unless they deliver to your retail store. Sellers can check your number with the Comptroller\'s Verify Exempt Status tool. A seller who knows or should know a sale is not for resale must charge tax. If you use items you bought tax-free, you owe the tax.',
    'sources' => [
        [
            'title' => 'Business Tax Tip #4 - Resale Certificates',
            'url' => 'https://www.marylandtaxes.gov/forms/Business_Tax_Tips/bustip4.pdf',
        ],
        [
            'title' => 'Suggested Blanket Resale Certificate',
            'url' => 'https://www.marylandtaxes.gov/forms/compliance_forms/Sample-Resale-Certificate.pdf',
        ],
        [
            'title' => 'Md. Code, Tax-General 11-408',
            'url' => 'https://mgaleg.maryland.gov/mgawebsite/Laws/StatuteText?article=gtg&section=11-408&enactments=false',
        ],
        [
            'title' => 'COMAR 03.06.01.14 Resale Certificates',
            'url' => 'https://dsd.maryland.gov/regulations/Pages/03.06.01.14.aspx',
        ],
        [
            'title' => 'Verify Exempt Status',
            'url' => 'https://interactive.marylandtaxes.gov/Business/VerifyExempt/User/Home.aspx',
        ],
        [
            'title' => 'Business Tax Tip #29 Sales of Digital Products and Digital Codes',
            'url' => 'https://marylandtaxes.gov/forms/Business_Tax_Tips/bustip29.pdf',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
