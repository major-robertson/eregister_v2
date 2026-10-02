<?php

/*
 * Idaho: Idaho State Tax Commission, Form ST-101. Researched 2026-10-01 from
 * tax.idaho.gov, legislature.idaho.gov, law.cornell.edu, mtc.gov. Generated
 * once from the EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'ID',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Idaho State Tax Commission',
        'short' => 'the Tax Commission',
        'url' => 'https://tax.idaho.gov/',
    ],
    'resale_page_url' => 'https://tax.idaho.gov/taxes/sales-use/guides-for-certain-groups/wholesalers/selling-goods/',
    'form' => [
        'number' => 'ST-101',
        'title' => 'Sales Tax Resale or Exemption Certificate',
        'pdf_url' => 'https://tax.idaho.gov/wp-content/uploads/forms/EFO00149/EFO00149_02-27-2025.pdf',
        'prescribed' => true,
        'revision' => 'EFO00149 02-27-2025',
        'notes' => 'Listed on the Tax Commission\'s forms page as \'Sales Tax Resale or Exemption Certificate and Instructions 2024\', revision 02-27-2025. Section 1 is \'Buying for Resale\'. IDAPA 35.01.02.128 also allows the MTC Uniform Sales and Use Tax Certificate. Contractors improving real property use Form ST-103C; vehicle and vessel purchases use Form ST-133.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Idaho Seller\'s Permit (or E911 fee permit for prepaid wireless sellers)',
        'number_name' => 'Idaho seller\'s permit number',
        'format' => '9 digits (e.g. 000123456)',
        'verify_url' => 'https://tax.idaho.gov/validseller',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'IDAPA 35.01.02.128 (ST-101 or \'a Uniform Sales and Use Tax Certificate -- Multi-jurisdiction\'); MTC note 11 (resale of tangible personal property only; must comply with Idaho Code § 63-3622(c)).',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'Idaho is not a Streamlined Sales Tax member; IDAPA 35.01.02.128 names only Form ST-101 and the MTC uniform certificate.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Idaho Code § 63-3622(c); Form ST-101 Section 1 and instructions; IDAPA 35.01.02.128',
            'notes' => 'An Idaho permit number is not required for out-of-state retailers with no Idaho business presence; the certificate indicates that the purchaser is an out-of-state retailer instead of giving a permit number. Wholesalers making no retail sales and retailers selling only through marketplace facilitators are also exempt from the permit-number requirement.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'https://tax.idaho.gov/taxes/sales-use/guides-for-certain-groups/wholesalers/selling-goods/ (\'Keep the certificate and don\'t charge tax on future qualifying sales to the customer\'); IDAPA 35.01.02.128',
        ],
    ],
    'expiration' => [
        'label' => 'No expiration',
        'summary' => 'No expiration is set. The seller keeps the certificate on file and does not charge tax on the customer\'s future qualifying purchases.',
        'cite' => 'https://tax.idaho.gov/taxes/sales-use/guides-for-certain-groups/wholesalers/selling-goods/ ; IDAPA 35.01.02.128',
    ],
    'good_faith' => [
        'summary' => 'A seller is not liable for collecting sales tax on items sold to a customer from whom it obtained a properly executed Form ST-101 if the customer intends to resell the items. The certificate must be fully completed (all applicable areas) and signed and dated, or it is not valid and the seller owes the tax. The seller cannot rely on a certificate when the goods do not reasonably match the buyer\'s business. A seller relieved of collecting tax is also relieved of liability to the purchaser, and the purchaser who gave the certificate bears responsibility for any later audit of the transaction.',
        'cite' => 'Idaho Code § 63-3622(c), (d); IDAPA 35.01.02.128; Form ST-101 instructions (EFO00149 02-27-2025)',
    ],
    'misuse_penalty' => [
        'summary' => 'Giving a resale or exemption certificate with the intent of evading tax is a misdemeanor punishable by a fine of up to $1,000, up to one year in jail, or both. A buyer whose purchases do not qualify is responsible for the tax due. The form warns that false information can bring criminal and civil penalties.',
        'cite' => 'Idaho Code § 63-3622(e); Form ST-101 (EFO00149 02-27-2025)',
    ],
    'facts' => [
        [
            'text' => 'A resale certificate must show the purchaser\'s federal EIN or driver\'s license number and state of issue, as well as its Idaho permit number or a statement that it is an out-of-state retailer.',
            'source_url' => 'https://legislature.idaho.gov/statutesrules/idstat/title63/t63ch36/sect63-3622/',
        ],
        [
            'text' => 'Wholesalers that make no retail sales and retailers that sell only through marketplace facilitators may buy for resale without an Idaho seller\'s permit number by checking the matching box on Form ST-101.',
            'source_url' => 'https://tax.idaho.gov/wp-content/uploads/forms/EFO00149/EFO00149_02-27-2025.pdf',
        ],
        [
            'text' => 'Sellers can validate a nine-digit Idaho seller\'s permit number at tax.idaho.gov/validseller or by contacting the Tax Commission.',
            'source_url' => 'https://tax.idaho.gov/wp-content/uploads/forms/EFO00149/EFO00149_02-27-2025.pdf',
        ],
        [
            'text' => 'Form ST-101 was revised 02-27-2025. It covers resale, production exemptions, exempt buyers and other exempt goods on one form; contractors improving real property must use Form ST-103C instead.',
            'source_url' => 'https://tax.idaho.gov/taxes/sales-use/forms/',
        ],
        [
            'text' => 'IDAPA 35.01.02.128 lets an out-of-state retailer that makes no more than two sales in Idaho in any 12-month period, and is not required to hold an Idaho seller\'s permit, claim the resale exemption.',
            'source_url' => 'https://www.law.cornell.edu/regulations/idaho/IDAPA-35.01.02.128',
        ],
    ],
    'state_notes' => 'In Idaho, use Form ST-101, the Sales Tax Resale or Exemption Certificate, from the Idaho State Tax Commission. The current version is dated 02-27-2025. The Tax Commission also accepts the Multistate Tax Commission\'s uniform certificate for resale. Fill in your name and address and the seller\'s name and address. In Section 1, Buying for Resale, describe your business and the products you sell. Then check the box that applies. Most buyers enter their nine-digit Idaho seller\'s permit number. Out-of-state retailers with no Idaho business presence, wholesalers with no retail sales, and sellers who sell only through a marketplace facilitator do not need an Idaho permit number. Sign and date the form, print your name and title, and enter your federal EIN or driver\'s license number and state. The seller keeps it on file for your future qualifying purchases; it has no set expiration. An incomplete certificate is not valid. If your purchases do not qualify, you owe the tax. Giving a certificate to evade tax is a misdemeanor with a fine of up to $1,000, up to a year in jail, or both.',
    'sources' => [
        [
            'title' => 'Idaho State Tax Commission: Sales and Use Tax Forms',
            'url' => 'https://tax.idaho.gov/taxes/sales-use/forms/',
        ],
        [
            'title' => 'Form ST-101 Sales Tax Resale or Exemption Certificate (EFO00149 02-27-2025)',
            'url' => 'https://tax.idaho.gov/wp-content/uploads/forms/EFO00149/EFO00149_02-27-2025.pdf',
        ],
        [
            'title' => 'Idaho State Tax Commission: Wholesalers, Selling Goods',
            'url' => 'https://tax.idaho.gov/taxes/sales-use/guides-for-certain-groups/wholesalers/selling-goods/',
        ],
        [
            'title' => 'Idaho Code § 63-3622',
            'url' => 'https://legislature.idaho.gov/statutesrules/idstat/title63/t63ch36/sect63-3622/',
        ],
        [
            'title' => 'IDAPA 35.01.02.128 Certificates for Resale and Other Exemption Claims',
            'url' => 'https://www.law.cornell.edu/regulations/idaho/IDAPA-35.01.02.128',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
