<?php

/*
 * Louisiana: Louisiana Department of Revenue, Form R-1064. Researched
 * 2026-10-01 from revenue.louisiana.gov, legis.la.gov, dam.ldr.la.gov,
 * remotesellers.louisiana.gov. Generated once from the EREG-8 resale research;
 * edit this file directly from now on.
 */

return [
    'state' => 'LA',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Louisiana Department of Revenue',
        'short' => 'LDR',
        'url' => 'https://revenue.louisiana.gov/',
    ],
    'resale_page_url' => 'https://revenue.louisiana.gov/businesses/general-resources/resale-certificate/',
    'form' => [
        'number' => 'R-1064',
        'title' => 'Louisiana Resale Certificate',
        'pdf_url' => null,
        'prescribed' => true,
        'revision' => null,
        'notes' => 'LDR issues the certificate to each qualifying registered dealer through LaTAP; there is no blank form for the buyer to fill in. Older LDR guidance (Revenue Ruling 09-002) called it Form R-1042; current LDR FAQs call it Form R-1064. Remote sellers registered with the Louisiana Sales and Use Tax Commission for Remote Sellers apply to that Commission for a separate Louisiana Resale Certificate (form updated 5/13/24), which it authorizes for three years.',
    ],
    'issuer_model' => 'state_issued',
    'registration' => [
        'name' => 'Louisiana sales tax registration with LDR (LDR account number)',
        'number_name' => 'LDR Account Number (or Location ID for consolidated filers); remote sellers use a 9-digit Remote Seller account number from the Commission',
        'format' => '10-digit LDR account number (e.g. 1234567001) or 11-character Location ID (e.g. B12345678901)',
        'verify_url' => 'https://latap.revenue.louisiana.gov/_/#1',
    ],
    'accepts' => [
        'mtc' => [
            'value' => false,
            'cite' => 'LDR FAQ: \'Louisiana does not accept other state exemptions or the multi-state exemption certificate.\' https://revenue.louisiana.gov/tax-education-and-faqs/faqs/sales-tax/do-i-have-to-get-an-exemption-certificate-on-all-my-customers-making-an-exempt-purchase/',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'Louisiana is not an SST member; LDR does not accept other state or multistate exemption certificates (same LDR FAQ).',
        ],
        'out_of_state_registration' => [
            'value' => false,
            'cite' => 'LDR FAQ (same URL)',
            'notes' => 'Another state\'s registration or resale certificate is not accepted. Out-of-state dealers need a Louisiana-issued resale certificate (from LDR, or from the Remote Sellers Commission for remote sellers). Sales in bona fide interstate commerce are documented by invoice and bill of lading, and drop shipments by a drop shipment letter, not by a resale certificate.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'The LDR-issued certificate covers all of the dealer\'s purchases for resale while it is valid; it is not issued per transaction. https://revenue.louisiana.gov/businesses/general-resources/resale-certificate/',
        ],
    ],
    'expiration' => [
        'label' => 'Valid for 1 year',
        'summary' => 'LDR resale certificates are now valid for one year from the approval date and must be renewed annually through LaTAP (previously three years). A certificate renews automatically 60 days before expiration if the dealer\'s returns and payments are current; otherwise the dealer renews within 45 days of the expiration date. R.S. 47:13 allows automatic renewals for up to three years and lets LDR suspend a delinquent dealer\'s certificate. Remote Seller Commission certificates expire three years after authorization.',
        'cite' => 'LDR Resale Certificate page and FAQ \'When does a business need to renew\'; La. R.S. 47:13(B), (D); Remote Sellers Commission Resale Certificate (updated 5/13/24)',
    ],
    'good_faith' => [
        'summary' => 'Sales for resale must be made in strict compliance with LDR rules; a dealer whose sale for resale is not in strict compliance is liable for the tax. Sales not supported by a properly executed resale certificate are deemed retail sales. LDR provides a LaTAP lookup so the seller can confirm the purchaser holds a valid Louisiana resale exemption; records update daily.',
        'cite' => 'La. R.S. 47:301(10)(a); LDR Revenue Ruling 09-002; LDR Resale Certificate page',
    ],
    'misuse_penalty' => [
        'summary' => 'Items bought for the purchaser\'s own use are taxable and the purchaser must pay the tax. LDR may suspend the resale certificate. On the Remote Sellers Commission certificate, a purchaser who fraudulently signs without intent to resell is subject to all penalties in Title 47, and tax, penalties and interest may be pursued against the seller or purchaser.',
        'cite' => 'La. R.S. 47:13(B); Remote Sellers Commission Resale Certificate (updated 5/13/24); LDR Revenue Ruling 09-002',
    ],
    'facts' => [
        [
            'text' => 'A local collector must accept the LDR resale certificate if the dealer includes its parish of principal place of business and local sales tax account number on it, but for an intra-parish dealer-to-dealer sale the parish may require its own local exemption certificate.',
            'source_url' => 'https://legis.la.gov/Legis/Law.aspx?d=101815',
        ],
        [
            'text' => 'The resale exclusion covers resale of tangible personal property, digital products, and services taxed under R.S. 47:301.3, the service and digital product provisions added by Act 10 of the 2024 Third Extraordinary Session.',
            'source_url' => 'https://legis.la.gov/Legis/Law.aspx?d=101815',
        ],
        [
            'text' => 'Materials bought for further processing qualify only if they become a recognizable, identifiable and beneficial component of the product sold; for example, frying oil qualifies but pan-coating oil does not.',
            'source_url' => 'https://dam.ldr.la.gov/lawspolicies/RR09002.pdf',
        ],
        [
            'text' => 'To apply or renew, a dealer needs its LDR account numbers for all locations, addresses, NAICS code, email, and resale inventory purchase amounts for the last two years.',
            'source_url' => 'https://revenue.louisiana.gov/businesses/general-resources/resale-certificate/',
        ],
        [
            'text' => 'Exemptions are not transferable; the certificate holder must be the purchaser.',
            'source_url' => 'https://revenue.louisiana.gov/tax-education-and-faqs/faqs/sales-tax/do-i-have-to-get-an-exemption-certificate-on-all-my-customers-making-an-exempt-purchase/',
        ],
    ],
    'state_notes' => 'Louisiana works differently from most states. The Louisiana Department of Revenue (LDR) issues the resale certificate itself, Form R-1064, Louisiana Resale Certificate. You do not fill in a blank form. You must first register with LDR for sales tax. You then request the certificate in LDR\'s online system, LaTAP. LDR asks for your account numbers, NAICS code, and resale purchase amounts for the last two years. Give your supplier a copy of the certificate LDR issues to you. It shows your 10-digit LDR account number. Certificates are now valid for one year and must be renewed each year. LDR renews them automatically if your returns and payments are current. Louisiana does not accept other states\' certificates or the multistate (MTC) certificate. Remote sellers apply instead to the Louisiana Sales and Use Tax Commission for Remote Sellers, whose certificate lasts three years. Parishes may require their own local certificate for some sales within the same parish. Sellers can check your certificate with LDR\'s LaTAP lookup. The certificate covers only goods and taxable services you resell. Items you use yourself are taxable. A seller that accepts an improper certificate can owe the tax. LDR can suspend the certificate of a dealer that falls behind on its taxes.',
    'sources' => [
        [
            'title' => 'LDR Resale Certificate page',
            'url' => 'https://revenue.louisiana.gov/businesses/general-resources/resale-certificate/',
        ],
        [
            'title' => 'LDR FAQ: When does a business need to renew the Louisiana Resale Certificate(s)?',
            'url' => 'https://revenue.louisiana.gov/tax-education-and-faqs/faqs/louisiana-resale-certificate/when-does-a-business-need-to-renew-the-louisiana-resale-certificates/',
        ],
        [
            'title' => 'LDR FAQ: Who qualifies for a Louisiana Resale Certificate?',
            'url' => 'https://revenue.louisiana.gov/tax-education-and-faqs/faqs/louisiana-resale-certificate/who-qualifies-for-a-louisiana-resale-certificate/',
        ],
        [
            'title' => 'LDR FAQ: Do I have to get an exemption certificate on all my customers?',
            'url' => 'https://revenue.louisiana.gov/tax-education-and-faqs/faqs/sales-tax/do-i-have-to-get-an-exemption-certificate-on-all-my-customers-making-an-exempt-purchase/',
        ],
        [
            'title' => 'La. R.S. 47:301',
            'url' => 'https://legis.la.gov/Legis/Law.aspx?d=101815',
        ],
        [
            'title' => 'La. R.S. 47:13',
            'url' => 'https://www.legis.la.gov/legis/Law.aspx?d=861120',
        ],
        [
            'title' => 'LDR Revenue Ruling 09-002',
            'url' => 'https://dam.ldr.la.gov/lawspolicies/RR09002.pdf',
        ],
        [
            'title' => 'Louisiana Sales and Use Tax Commission for Remote Sellers: Resale Certificate (updated 5/13/24)',
            'url' => 'https://remotesellers.louisiana.gov/Documents/Resale%20Certificate_updated%205.13.24.pdf',
        ],
    ],
];
