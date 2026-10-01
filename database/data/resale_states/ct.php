<?php

/*
 * Connecticut: Connecticut Department of Revenue Services, Sales and Use Tax
 * Resale Certificate. Researched 2026-10-01 from portal.ct.gov, mtc.gov.
 * Generated once from the EREG-8 resale research; edit this file directly from
 * now on.
 */

return [
    'state' => 'CT',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Connecticut Department of Revenue Services',
        'short' => 'DRS',
        'url' => 'https://portal.ct.gov/drs',
    ],
    'resale_page_url' => 'https://portal.ct.gov/DRS/DRS-Forms/Sales-Tax-Forms/Other-SUT-Forms',
    'form' => [
        'number' => null,
        'title' => 'Sales and Use Tax Resale Certificate',
        'pdf_url' => 'https://portal.ct.gov/-/media/drs/forms/1995forms/resale-certificate.pdf',
        'prescribed' => true,
        'revision' => '1995 (date shown on DRS forms page)',
        'notes' => 'DRS\'s form has no form number. Conn. Agencies Regs. § 12-426-1(b) says the form is prescribed by the Commissioner; IP 2009(15) says a buyer may give the DRS form \'or a certificate that substantially resembles the official DRS form\'. The DRS PDF has two pages: the text of Regulations 1 and 23 and the certificate itself.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Connecticut Sales and Use Tax Permit',
        'number_name' => 'Connecticut Tax Registration Number',
        'format' => null,
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'IP 2009(15): \'DRS accepts the Multistate Tax Commission\'s Uniform Sales & Use Tax Certificate -- Multijurisdiction as a valid resale certificate, but not as a valid exemption certificate for any other purpose.\' MTC note 7.',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'Connecticut is not a Streamlined Sales Tax member; IP 2009(15) names only the DRS certificate, a substantially similar certificate, or the MTC certificate.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'IP 2009(15), Questions 1, 4 and 5',
            'notes' => 'An issuer may hold a DRS permit \'or is similarly registered by the revenue agency of another state\'. An out-of-state business not required to hold a Connecticut permit should enter the tax ID number from its home state (or, if none, its FEIN) and attach a statement that it is not required to hold a Connecticut permit because it makes no sales in Connecticut or no sales otherwise subject to Connecticut tax. A Connecticut retailer may accept a resale certificate from a business outside Connecticut buying to resell outside Connecticut, whether or not that state has a sales tax.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Conn. Agencies Regs. § 12-426-1(b); IP 2009(15) Question 6 (mark the certificate \'Blanket Certificate\')',
        ],
    ],
    'expiration' => [
        'label' => 'Renew blanket every 3 years',
        'summary' => 'A single-purchase certificate covers that purchase. A blanket certificate is valid only for the items described and only while the buyer is reselling them, and must be renewed at least every three years from the date it is issued.',
        'cite' => 'Conn. Agencies Regs. § 12-426-1(d); IP 2009(15) Question 6',
    ],
    'good_faith' => [
        'summary' => 'The seller bears the burden of proving a sale was not at retail unless it takes a resale certificate in good faith from a person engaged in selling the property or service who intends to resell it. Good faith is questioned if the seller knows facts suggesting the buyer will not resell, for example that the buyer does not sell that kind of merchandise. The item must be similar to what the seller could reasonably assume the buyer sells. A seller cannot accept a bare tax registration number instead of a certificate. Faxed copies of a completed certificate are acceptable.',
        'cite' => 'Conn. Agencies Regs. § 12-426-1(a), (c); Conn. Gen. Stat. § 12-410; IP 2009(15) Questions 3 and 7',
    ],
    'misuse_penalty' => [
        'summary' => 'DRS can assess tax against a retailer that accepts an improper certificate, against a buyer that issues one improperly, or against both. Knowingly making a false statement on a resale certificate is punishable by a fine of up to $5,000, up to five years in prison, or both.',
        'cite' => 'IP 2009(15) Question 9; certificate declaration \'under the penalties of false statement\'',
    ],
    'facts' => [
        [
            'text' => 'Connecticut\'s resale certificate also covers taxable services bought to resell without change, and services enumerated in Conn. Gen. Stat. § 12-407(a)(2)(I) that become an integral, inseparable part of another enumerated service.',
            'source_url' => 'https://portal.ct.gov/-/media/DRS/Publications/pubsip/2009/IP0915pdf.pdf',
        ],
        [
            'text' => 'A contractor who does not sell at retail may not issue a resale certificate for goods consumed in a construction contract.',
            'source_url' => 'https://portal.ct.gov/-/media/DRS/Publications/pubsip/2009/IP0915pdf.pdf',
        ],
        [
            'text' => 'Connecticut sales and use tax permits expire every two years and are renewed automatically at no cost while the account is active and in good standing. The registration fee for a new permit is $100.',
            'source_url' => 'https://portal.ct.gov/DRS/Sales-Tax/Renewal-of-Your-Sales-Tax-Permit',
        ],
        [
            'text' => 'Sellers should keep resale certificates for at least six years and should not mail them to DRS.',
            'source_url' => 'https://portal.ct.gov/-/media/DRS/Publications/pubsip/2009/IP0915pdf.pdf',
        ],
        [
            'text' => 'The resale certificate includes leases and rentals of tangible personal property; services may only be sold or purchased, not rented or leased.',
            'source_url' => 'https://portal.ct.gov/-/media/drs/forms/1995forms/resale-certificate.pdf',
        ],
    ],
    'state_notes' => 'In Connecticut, use the Sales and Use Tax Resale Certificate from the Department of Revenue Services (DRS). DRS also accepts the Multistate Tax Commission\'s Uniform Sales & Use Tax Certificate as a resale certificate. Fill in your business name and address and the seller\'s name. Enter your Connecticut Tax Registration Number as it appears on your Sales and Use Tax Permit. If you are an out-of-state business not required to hold a Connecticut permit, enter your home-state tax ID number, or your FEIN if you have none, and attach a statement explaining why you do not need a Connecticut permit. Describe what you sell and what you are buying. An owner, partner or corporate officer signs it. You can use it for one purchase, or mark it \'Blanket Certificate\' for ongoing purchases. A blanket certificate should be renewed at least every three years. The seller keeps it and must accept it in good faith. If you use items you bought tax-free, you must pay the tax. Knowingly making a false statement on a resale certificate can bring a fine of up to $5,000, up to five years in prison, or both.',
    'sources' => [
        [
            'title' => 'DRS Other Sales and Use Tax Forms',
            'url' => 'https://portal.ct.gov/DRS/DRS-Forms/Sales-Tax-Forms/Other-SUT-Forms',
        ],
        [
            'title' => 'DRS Sales and Use Tax Resale Certificate (with Regulations 1 and 23)',
            'url' => 'https://portal.ct.gov/-/media/drs/forms/1995forms/resale-certificate.pdf',
        ],
        [
            'title' => 'IP 2009(15) Notice to Retailers on Sales and Use Tax Resale Certificates',
            'url' => 'https://portal.ct.gov/-/media/DRS/Publications/pubsip/2009/IP0915pdf.pdf',
        ],
        [
            'title' => 'DRS: Renewal of Your Sales Tax Permit',
            'url' => 'https://portal.ct.gov/DRS/Sales-Tax/Renewal-of-Your-Sales-Tax-Permit',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
