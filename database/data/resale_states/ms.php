<?php

/*
 * Mississippi: Mississippi Department of Revenue, No resale certificate form:
 * a copy of your Mississippi sales tax permit. Researched 2026-10-01 from
 * dor.ms.gov, sos.ms.gov, tap.dor.ms.gov, mtc.gov. Generated once from the
 * EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'MS',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Mississippi Department of Revenue',
        'short' => 'MDOR',
        'url' => 'https://www.dor.ms.gov/',
    ],
    'resale_page_url' => 'https://www.dor.ms.gov/business/business-tax-frequently-asked-questions',
    'form' => [
        'number' => null,
        'title' => 'No resale certificate form; the purchaser\'s Mississippi Sales Tax Permit (or Seller\'s Use Tax Permit) is the evidence',
        'pdf_url' => null,
        'prescribed' => false,
        'revision' => null,
        'notes' => 'MDOR does not prescribe a resale certificate and does not accept or use blanket certificates. A wholesale (resale) sale must be supported by an invoice and a copy of the buyer\'s DOR-issued permit (or a letter ruling) kept by the seller (35 Miss. Admin. Code Pt. IV, R. 3.01.300).',
        'label' => 'No resale certificate form: a copy of your Mississippi sales tax permit',
    ],
    'issuer_model' => 'state_issued',
    'registration' => [
        'name' => 'Mississippi Sales Tax Permit (in-state) or Seller\'s Use Tax Permit (out-of-state)',
        'number_name' => 'Mississippi sales tax permit / account number',
        'format' => null,
        'verify_url' => 'https://tap.dor.ms.gov/_/',
    ],
    'accepts' => [
        'mtc' => [
            'value' => false,
            'cite' => 'MDOR FAQ: \'the Mississippi Department of Revenue does not accept or use blanket certificates\'; customers must give a DOR-issued permit, Material Purchase Certificate, Direct Pay Permit or letter ruling. Mississippi is not listed on the MTC certificate (rev. 10/14/2022). https://www.dor.ms.gov/business/business-tax-frequently-asked-questions',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'Mississippi is not an SST member state; MDOR FAQ requires a DOR-issued permit or letter rather than a certificate.',
        ],
        'out_of_state_registration' => [
            'value' => false,
            'cite' => '35 Miss. Admin. Code Pt. IV, R. 3.05.104; MDOR FAQ (wholesale merchants)',
            'notes' => 'Limited exception: property bought in Mississippi for resale in another state by a dealer holding a valid sales tax permit (or equivalent) for that state is exempt. Out-of-state businesses that want to buy tax-free for resale into Mississippi register for a Mississippi Seller\'s Use Tax permit.',
        ],
        'blanket' => [
            'value' => false,
            'cite' => 'MDOR FAQ: \'Does the Mississippi Department of Revenue accept blanket certificates as valid letters of exemption? No.\'',
        ],
    ],
    'expiration' => [
        'label' => 'Permit does not expire',
        'summary' => 'No certificate to expire. The sales tax permit does not expire while the holder stays in the same business at the same location.',
        'cite' => 'MDOR Registration Information for Sales and Use Tax Applicants (per search summary; see unconfirmed)',
    ],
    'good_faith' => [
        'summary' => 'A wholesale sale is exempt when made in good faith to a retailer that regularly sells that property and is licensed under Miss. Code Ann. 27-65-27 if located in Mississippi. The seller must keep, in chronological order for three years, invoices showing date, vendor and buyer names and addresses, items and price, plus a copy of the buyer\'s permit or letter ruling. Failing these requirements subjects the seller to the retail rate of tax on those sales. The seller bears the burden of proving a sale is exempt.',
        'cite' => '35 Miss. Admin. Code Pt. IV, R. 3.01.101 and 3.01.300 (rev. eff. Dec. 7, 2023); MDOR Business Tax FAQ',
    ],
    'misuse_penalty' => [
        'summary' => 'Items bought tax-free for resale but withdrawn from inventory for use by the owner, employees or others are subject to sales tax, and the cost of goods bought for resale but given away or used must be included in gross sales with tax paid. Deficient or delinquent tax carries penalty and interest.',
        'cite' => '35 Miss. Admin. Code Pt. IV, R. 3.01.103 and Subpart 2; MDOR Business Tax FAQ',
    ],
    'facts' => [
        [
            'text' => 'A business that sells only at wholesale need not register, but to buy wholesale purchases tax-free it must register for a Mississippi sales tax permit (in-state) or Seller\'s Use Tax permit (out-of-state) and then file returns, even with no taxable sales.',
            'source_url' => 'https://www.dor.ms.gov/business/business-tax-frequently-asked-questions',
        ],
        [
            'text' => 'Contractors buy component materials tax-free only with a Material Purchase Certificate under Miss. Code Ann. 27-65-21, not a resale document.',
            'source_url' => 'https://www.dor.ms.gov/business/business-tax-frequently-asked-questions',
        ],
        [
            'text' => 'Wholesale sales of medical cannabis may only be made to licensed cultivators, processors or dispensaries, which must give their sales tax number and ten-digit license number.',
            'source_url' => 'https://www.sos.ms.gov/adminsearch/ACCode/00000851c.pdf',
        ],
        [
            'text' => 'Food and drink placed in full-service vending machines is taxed at an 8% wholesale rate; all other wholesale sales are exempt.',
            'source_url' => 'https://www.sos.ms.gov/adminsearch/ACCode/00000851c.pdf',
        ],
        [
            'text' => 'Remote sellers with more than $250,000 in Mississippi sales in a 12-month period must register to collect seller\'s use tax.',
            'source_url' => 'https://www.dor.ms.gov/business/business-tax-frequently-asked-questions',
        ],
    ],
    'state_notes' => 'Mississippi has no official resale certificate form. The Mississippi Department of Revenue (MDOR) does not accept or use blanket certificates. Instead, a business buying for resale gives its supplier the Mississippi Sales Tax Permit that MDOR issued to it. Out-of-state businesses buying for resale into Mississippi register for a Seller\'s Use Tax permit. You apply for either permit online through MDOR\'s Taxpayer Access Point (TAP). The supplier keeps a copy of your permit with its invoices. Each invoice must show the date, both parties\' names and addresses, the items and the price. The supplier keeps these records for at least three years. A separate exception applies when you buy goods in Mississippi to resell in another state and you hold a sales tax permit in that state. Sellers can verify permit numbers through TAP. The permit does not need to be renewed while you stay in the same business at the same location. If you take items bought for resale out of inventory for your own use, you owe sales tax on them. A seller without the required records owes tax at the retail rate.',
    'sources' => [
        [
            'title' => 'MDOR Business Tax Frequently Asked Questions',
            'url' => 'https://www.dor.ms.gov/business/business-tax-frequently-asked-questions',
        ],
        [
            'title' => '35 Miss. Admin. Code Part IV, Sales and Use Tax (R. 3.01 Wholesale Sales, rev. eff. Dec. 7, 2023; R. 3.05 Interstate Commerce)',
            'url' => 'https://www.sos.ms.gov/adminsearch/ACCode/00000851c.pdf',
        ],
        [
            'title' => 'MDOR Verify a Sales Tax Number',
            'url' => 'https://www.dor.ms.gov/node/18',
        ],
        [
            'title' => 'MDOR Taxpayer Access Point (TAP)',
            'url' => 'https://tap.dor.ms.gov/_/',
        ],
        [
            'title' => 'MDOR Registration Information for Sales and Use Tax Applicants',
            'url' => 'https://www.dor.ms.gov/business/sales-use-tax/registration-information-sales-and-use-tax-applicants',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
