<?php

/*
 * Maine: Maine Revenue Services, Resale Certificate (issued by the State Tax
 * Assessor). Researched 2026-10-01 from maine.gov, legislature.maine.gov,
 * portal.maine.gov, mtc.gov. Generated once from the EREG-8 resale research;
 * edit this file directly from now on.
 */

return [
    'state' => 'ME',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Maine Revenue Services',
        'short' => 'MRS',
        'url' => 'https://www.maine.gov/revenue/',
    ],
    'resale_page_url' => 'https://www.maine.gov/future/sites/maine.gov.revenue/files/inline-files/IB54ResaleCertificates022020.pdf',
    'form' => [
        'number' => null,
        'title' => 'Resale Certificate (issued by the State Tax Assessor)',
        'pdf_url' => null,
        'prescribed' => true,
        'revision' => null,
        'notes' => 'Maine Revenue Services issues a Resale Certificate to each registered retailer expecting or reporting $3,000 or more in annual gross sales (36 M.R.S. 1754-B(2-B), (2-C)). The retailer copies it, lists the items, names the supplier, signs and dates the copy. Nonresident retailers not registered in Maine may instead use the MTC Uniform Sales & Use Tax Certificate or a signed statement meeting Rule 301 Section 5.',
    ],
    'issuer_model' => 'state_issued',
    'registration' => [
        'name' => 'Maine Retailer Certificate (sales tax registration) plus MRS-issued Resale Certificate',
        'number_name' => 'Maine sales tax registration number (shown on the Resale Certificate with a certificate number and expiration date)',
        'format' => null,
        'verify_url' => 'https://portal.maine.gov/certlookup/',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'Rule 301 Section 5(2) (amended Feb. 25, 2025): https://www.maine.gov/revenue/sites/maine.gov.revenue/files/inline-files/Rule_301_February_2025.pdf; IB 54 Section 6',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'Maine is not an SST member; Rule 301 names only the MRS Resale Certificate, the MTC certificate, or a Section 5 statement.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Rule 301 Section 5(1); IB 54 Section 6',
            'notes' => 'A nonresident retailer not required to register in Maine may give its home-state sales tax registration number on the MTC certificate (or a signed statement under penalties of perjury with evidence of home-state registration), declaring the goods are for resale outside Maine. A home-state resale certificate itself is not valid in Maine.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => '36 M.R.S. 1754-B(2-B), (2-C); IB 54 Section 4: a true copy on file with a vendor covers later purchases while valid.',
        ],
    ],
    'expiration' => [
        'label' => 'Multi-year, ends December 31',
        'summary' => 'Resale Certificates expire December 31. A new certificate issued January 1 to September 30 runs through that year plus 3 more calendar years; one issued October 1 to December 31 runs to the end of the 4th following year. MRS reviews retailers each November 1 and reissues certificates for 5 calendar years to retailers with $3,000 or more in gross sales over the prior 12 months. The buyer must give suppliers copies of each new certificate.',
        'cite' => '36 M.R.S. 1754-B(2-B) and (2-C) (as amended by PL 2019, c. 401); IB 54',
    ],
    'good_faith' => [
        'summary' => 'A seller is relieved of liability if the buyer states the purchase is for resale, the items are of the type the buyer ordinarily resells as shown on its certificate, and the seller has a signed copy of a certificate valid on the sale date. A seller that accepts a certificate valid on its face is not liable even if the buyer was not actually active. Relief does not apply if the seller fraudulently fails to collect tax, solicits misuse, knows the buyer is out of business, or has reason to believe the goods will not be resold. Invoices must be marked, e.g. \'No Maine sales tax due, for resale.\'',
        'cite' => 'Rule 301 Sections 2 and 3 (amended Feb. 25, 2025); IB 54 Section 5',
    ],
    'misuse_penalty' => [
        'summary' => 'Using a resale certificate to buy without tax, knowing tax is due, is intentional tax evasion: a Class D crime if the tax is $2,000 or less, a Class C crime if over $2,000, in addition to other penalties.',
        'cite' => '36 M.R.S. 184-A; Rule 301 Section 8',
    ],
    'facts' => [
        [
            'text' => 'Retailers reporting less than $3,000 in annual gross sales do not get a Resale Certificate; they pay tax on inventory purchases and claim a credit on their sales tax return.',
            'source_url' => 'https://www.maine.gov/revenue/sites/maine.gov.revenue/files/inline-files/Rule_301_February_2025.pdf',
        ],
        [
            'text' => 'An active registered retailer may not pay tax to suppliers and then claim a credit for items it ordinarily buys for resale; it must use its Resale Certificate.',
            'source_url' => 'https://www.maine.gov/revenue/sites/maine.gov.revenue/files/inline-files/Rule_301_February_2025.pdf',
        ],
        [
            'text' => 'A sales tax registration number alone does not support a resale claim, and sellers must keep resale certificates for at least six years.',
            'source_url' => 'https://www.maine.gov/future/sites/maine.gov.revenue/files/inline-files/IB54ResaleCertificates022020.pdf',
        ],
        [
            'text' => 'Packaging materials are exempt on a separate certificate, Form ST-A-120; manufacturers buying ingredients or component parts use the Industrial Users Exemption Certificate ST-A-117, not the MTC form.',
            'source_url' => 'https://www.maine.gov/revenue/sites/maine.gov.revenue/files/inline-files/Rule_301_February_2025.pdf',
        ],
        [
            'text' => 'Rule 301 was last amended February 25, 2025; it treats lessors as retailers and covers sales for subsequent lease or rental and certain sales to service providers.',
            'source_url' => 'https://www.maine.gov/revenue/sites/maine.gov.revenue/files/inline-files/Rule_301_February_2025.pdf',
        ],
    ],
    'state_notes' => 'In Maine, the resale certificate is issued by Maine Revenue Services (MRS), not filled in from a blank form. When you register as a retailer and expect at least $3,000 in yearly sales, MRS sends you a Resale Certificate. It shows your business name, sales tax registration number, business type, certificate number and expiration date. Keep the original. Give each supplier a copy. On the copy, list the items you will buy for resale, enter the supplier\'s name, then sign and date it. A copy on file covers later purchases while the certificate is valid. Tell the supplier at each purchase that it is for resale. Certificates expire December 31. MRS reissues them for five calendar years if you reported $3,000 or more in sales; send suppliers the new copy. If your business is outside Maine and not registered here, you may use the MTC Uniform Sales & Use Tax Certificate with your home-state number, for goods you will resell outside Maine. Sellers can check certificates with MRS\'s online certificate lookup. Knowingly using a resale certificate to avoid tax is a crime: Class D if the tax is $2,000 or less, Class C if more.',
    'sources' => [
        [
            'title' => 'MRS Rule 301, Sales for Resale and Sales of Packaging Materials (amended Feb. 25, 2025)',
            'url' => 'https://www.maine.gov/revenue/sites/maine.gov.revenue/files/inline-files/Rule_301_February_2025.pdf',
        ],
        [
            'title' => 'MRS Instructional Bulletin No. 54, Resale Certificates',
            'url' => 'https://www.maine.gov/future/sites/maine.gov.revenue/files/inline-files/IB54ResaleCertificates022020.pdf',
        ],
        [
            'title' => '36 M.R.S. 1754-B',
            'url' => 'https://legislature.maine.gov/statutes/36/title36sec1754-B.pdf',
        ],
        [
            'title' => '36 M.R.S. 184-A',
            'url' => 'https://legislature.maine.gov/statutes/36/title36sec184-A.pdf',
        ],
        [
            'title' => 'MRS certificate lookup',
            'url' => 'https://portal.maine.gov/certlookup/',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
