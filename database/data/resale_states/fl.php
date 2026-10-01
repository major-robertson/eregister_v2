<?php

/*
 * Florida: Florida Department of Revenue, Form DR-13. Researched 2026-10-01
 * from floridarevenue.com, flsenate.gov, law.cornell.edu, mtc.gov. Generated
 * once from the EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'FL',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Florida Department of Revenue',
        'short' => 'the Department of Revenue',
        'url' => 'https://floridarevenue.com/',
    ],
    'resale_page_url' => 'https://floridarevenue.com/taxes/taxesfees/Pages/annual_resale_certificate_sut.aspx',
    'form' => [
        'number' => 'DR-13',
        'title' => 'Florida Annual Resale Certificate for Sales Tax',
        'pdf_url' => null,
        'prescribed' => true,
        'revision' => null,
        'notes' => 'State-issued, not a blank form: the Department issues each active registered dealer a new Annual Resale Certificate (Form DR-13) every year, which the dealer downloads and prints at floridarevenue.com/taxes/printcertificate. There is no public blank PDF. Guidance is in brochure GT-800060 (R. 10/25). The communications services tax has a separate annual resale certificate.',
    ],
    'issuer_model' => 'state_issued',
    'registration' => [
        'name' => 'Florida Certificate of Registration (Form DR-11) as a sales and use tax dealer',
        'number_name' => 'Florida Annual Resale Certificate number (sales tax certificate number)',
        'format' => '13 digits',
        'verify_url' => 'https://floridarevenue.com/taxes/certificates',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC Uniform Resale Certificate (rev. 10/14/2022) note 8: Florida allows the MTC certificate for resale purchases, but the selling dealer must also obtain a resale authorization number from the Department (floridarevenue.com/taxes/certificates or 877-357-3725) using the purchaser\'s Florida Annual Resale Certificate number.',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'Florida is not a Streamlined Sales Tax member; § 212.07(1)(b), Fla. Stat., and brochure GT-800060 document resale sales only with the Annual Resale Certificate or a Department authorization number.',
        ],
        'out_of_state_registration' => [
            'value' => false,
            'cite' => '§ 212.07(1)(b), Fla. Stat.; Fla. Admin. Code R. 12A-1.0015(3)',
            'notes' => 'A Florida resale purchase needs a Florida Annual Resale Certificate or authorization number. Narrow exception: a sale to a nonresident dealer is exempt if the seller gets a signed statement that the dealer will transport the goods outside Florida for resale, with evidence of authority to do business in its home state (which may be, but need not be, its home-state sales tax registration number).',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Brochure GT-800060 (a copy of the customer\'s current Annual Resale Certificate documents its resale purchases; annual vendor authorization numbers for regular customers); § 212.07(1)(b), Fla. Stat. (dealer may rely on a certificate without annual verification for recurring sales)',
        ],
    ],
    'expiration' => [
        'label' => 'Expires December 31 each year',
        'summary' => 'Annual Resale Certificates expire every December 31. Each November the Department makes the next year\'s certificate available online to active registered dealers. Certificates for new locations issued from mid-October run through the following calendar year. A dealer making recurring sales to a purchaser on a continual basis may rely on a certificate valid when received without annual verification.',
        'cite' => 'Brochure GT-800060 (R. 10/25); § 212.07(1)(b), Fla. Stat.',
    ],
    'good_faith' => [
        'summary' => 'A seller documents each resale by keeping a copy of the buyer\'s current Annual Resale Certificate (paper or electronic, kept three years), by getting a transaction authorization number for each sale, or by getting an annual vendor authorization number for each regular customer. A seller that accepts a copy of the certificate is not liable if the buyer later turns out not to be an active registered dealer at the time. The seller should not accept a certificate if it knows or has reason to believe the goods are not for resale, such as a car dealer buying office supplies.',
        'cite' => '§ 212.07(1)(b), Fla. Stat.; brochure GT-800060 (R. 10/25)',
    ],
    'misuse_penalty' => [
        'summary' => 'A person who fraudulently issues a certificate claiming exemption to evade tax owes the tax plus a mandatory penalty of 200 percent of the tax and can be convicted of a third-degree felony. If goods bought for resale are later used instead of resold, the buyer must report and pay use tax and surtax.',
        'cite' => '§ 212.085, Fla. Stat.; brochure GT-800060 (R. 10/25)',
    ],
    'facts' => [
        [
            'text' => 'The Annual Resale Certificate needs no signature. Giving the certificate or its number to a seller certifies that the items or services will be resold.',
            'source_url' => 'https://floridarevenue.com/Forms_library/current/brochure/gt800060.pdf',
        ],
        [
            'text' => 'Certificates are issued only to dealers with an active sales tax account. A dealer on inactive status, or with only a use tax account, does not receive one.',
            'source_url' => 'https://floridarevenue.com/Forms_library/current/brochure/gt800060.pdf',
        ],
        [
            'text' => 'The resale exemption covers resale or re-rental of tangible property, re-rental of transient rental property, resale of services, parts incorporated into repairs, and ingredients or components of products made for sale. Service providers such as attorneys, accountants and doctors are generally end users and do not qualify.',
            'source_url' => 'https://floridarevenue.com/Forms_library/current/brochure/gt800060.pdf',
        ],
        [
            'text' => 'Sellers can verify a certificate number and get a transaction authorization number online, by phone at 877-357-3725, or with the free FL Tax mobile app. Each authorization number is good for one purchase only.',
            'source_url' => 'https://floridarevenue.com/Forms_library/current/brochure/gt800060.pdf',
        ],
        [
            'text' => 'Sales to a nonresident dealer who will transport the goods outside Florida for resale are exempt with a signed statement from that dealer (Rule 12A-1.0015(3), last amended June 14, 2022).',
            'source_url' => 'https://www.law.cornell.edu/regulations/florida/Fla-Admin-Code-Ann-R-12A-1-0015',
        ],
    ],
    'state_notes' => 'In Florida, the Florida Department of Revenue issues the resale certificate itself. When you register as a sales and use tax dealer, you receive a Florida Annual Resale Certificate for Sales Tax (Form DR-13). Each November you can download and print the next year\'s certificate from the Department\'s website. To buy for resale tax-free, give your supplier a copy of your current certificate or its 13-digit number. No signature is needed. Giving the certificate certifies that you will resell what you buy. Your supplier can verify the number online, by phone or with the FL Tax app. Some suppliers accept the Multistate Tax Commission\'s uniform certificate, but only with your Florida Annual Resale Certificate number, and the supplier must still verify it with the Department. Certificates expire every December 31. Only active registered dealers receive one. If you use goods you bought for resale, you must pay use tax and surtax on them. Fraudulent use of a resale certificate to avoid tax is a third-degree felony with a penalty of 200 percent of the tax.',
    'sources' => [
        [
            'title' => 'Florida DOR: Annual Resale Certificate for Sales Tax',
            'url' => 'https://floridarevenue.com/taxes/taxesfees/Pages/annual_resale_certificate_sut.aspx',
        ],
        [
            'title' => 'Florida DOR brochure GT-800060 (R. 10/25), Florida Annual Resale Certificate for Sales Tax',
            'url' => 'https://floridarevenue.com/Forms_library/current/brochure/gt800060.pdf',
        ],
        [
            'title' => 'Florida DOR certificate verification',
            'url' => 'https://floridarevenue.com/taxes/certificates',
        ],
        [
            'title' => '§ 212.07, Fla. Stat. (2025)',
            'url' => 'https://www.flsenate.gov/Laws/Statutes/2025/212.07',
        ],
        [
            'title' => '§ 212.085, Fla. Stat. (2025)',
            'url' => 'https://www.flsenate.gov/Laws/Statutes/2025/212.085',
        ],
        [
            'title' => 'Fla. Admin. Code R. 12A-1.0015',
            'url' => 'https://www.law.cornell.edu/regulations/florida/Fla-Admin-Code-Ann-R-12A-1-0015',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
