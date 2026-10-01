<?php

/*
 * Oklahoma: Oklahoma Tax Commission, no prescribed form. Researched 2026-10-01
 * from oklahoma.gov, oktap.tax.ok.gov, mtc.gov. Generated once from the EREG-8
 * resale research; edit this file directly from now on.
 */

return [
    'state' => 'OK',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Oklahoma Tax Commission',
        'short' => 'the OTC',
        'url' => 'https://oklahoma.gov/tax.html',
    ],
    'resale_page_url' => 'https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/general/Publication-D.pdf',
    'form' => [
        'number' => null,
        'title' => null,
        'pdf_url' => null,
        'prescribed' => false,
        'revision' => null,
        'notes' => 'The OTC prescribes no resale certificate form. Under OAC 710:65-7-8 the seller needs a copy of the buyer\'s Oklahoma sales tax permit (or its name, address, permit number and expiration date), a statement that the items are for resale, the buyer\'s signature, and a resale certification on the invoice or a separate document. The MTC uniform certificate may replace the permit copy, and the OTC posts the SST Certificate of Exemption (F0003): https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/streamlined-sales-tax/F0003ExemptionCertificate.pdf. Vendor guidance is Publication D (revised December 2025).',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Oklahoma Sales Tax Permit',
        'number_name' => 'Sales tax permit number (Sales Account ID / site permit number)',
        'format' => 'Publication D\'s sample permit shows a Sales Account ID like STS-15740582-04 and a separate 9-digit site permit number',
        'verify_url' => 'https://oktap.tax.ok.gov/OkTAP/Web/',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'Publication D (Dec. 2025) and MTC note 24: accepted in lieu of a copy of the purchaser\'s sales tax permit; vendor must have it within 90 days and accept it in good faith (OAC 710:65-7-6, 7-8).',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'Oklahoma is an SST full member; the OTC publishes SSTGB Form F0003 on its site (https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/streamlined-sales-tax/F0003ExemptionCertificate.pdf).',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'SSTGB Form F0003 instructions (drop shippers may accept another state\'s ID; Oklahoma accepts a foreign VAT number); SSUTA drop-shipment rule.',
            'notes' => 'OAC 710:65-7-8 and 68 O.S. 1365(C) are written around a resale number issued by the Commission; a non-Oklahoma number is clearly accepted for drop shipments under the SST certificate. Acceptance outside drop shipments was not confirmed.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'OAC 710:65-7-8(5): with regular purchases and a certification that all are for resale, later purchases need no further certification until the permit expires.',
        ],
    ],
    'expiration' => [
        'label' => 'Lasts as long as the permit',
        'summary' => 'Resale documentation is good until the buyer\'s sales tax permit expires. For continued sales the vendor must have a permit on file for each renewal interval; if no renewal interval is set by statute, the period is deemed three years.',
        'cite' => 'OAC 710:65-7-6(b)(2) and 710:65-7-8(5), reprinted in Publication D (Dec. 2025)',
    ],
    'good_faith' => [
        'summary' => 'A vendor is relieved of the tax if it in good faith timely accepts properly completed documentation. Good faith requires strict compliance with the requirements, and timely means the vendor has the documents within 90 days after the sale. Without the buyer\'s Commission-issued resale number, a sale is presumed not to be for resale.',
        'cite' => '68 O.S. 1361.1; OAC 710:65-7-6(b); OAC 710:65-3-33(c) and 68 O.S. 1365(C) (Publication D)',
    ],
    'misuse_penalty' => [
        'summary' => 'If the OTC finds a buyer improperly presented exemption documents or used the property for a non-exempt purpose, the buyer owes the tax and can be fined $500, and the OTC may collect from the buyer instead of the vendor.',
        'cite' => 'Publication D (revised December 2025), Vendors Duties and Liabilities: https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/general/Publication-D.pdf',
    ],
    'facts' => [
        [
            'text' => 'Contractors are consumers by statute and pay sales tax on materials, supplies and equipment used to improve real property; they cannot buy those items for resale.',
            'source_url' => 'https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/general/Publication-D.pdf',
        ],
        [
            'text' => 'A vendor holding a current sales tax permit can buy a monthly-updated file of all permit holders by filing Form 13-98 with a $150 annual fee.',
            'source_url' => 'https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/general/Publication-D.pdf',
        ],
        [
            'text' => 'Vendors keep exemption documents with the invoice for 3 years from the invoice date or the date the tax was remitted, whichever is later.',
            'source_url' => 'https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/general/Publication-D.pdf',
        ],
        [
            'text' => 'If a permit copy is not provided and the number has not been verified before, the vendor must verify it with the Taxpayer Assistance Division or the permit list.',
            'source_url' => 'https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/general/Publication-D.pdf',
        ],
        [
            'text' => 'OkTAP lets anyone find a sales tax account, exemption account or permit using a valid account or permit number.',
            'source_url' => 'https://oklahoma.gov/tax/businesses/sales-use-tax.html',
        ],
    ],
    'state_notes' => 'Oklahoma does not have an official resale certificate form. The Oklahoma Tax Commission instead lists what a seller must keep. Give your supplier a copy of your Oklahoma sales tax permit. If you cannot, give your business name, address, permit number and the permit\'s expiration date. Add a signed statement that you are buying the items to resell them. You can also use the Multistate Tax Commission uniform certificate or the Streamlined Sales Tax certificate of exemption. The seller must have your documents within 90 days of the sale. If you buy regularly and state that all purchases are for resale, one certification covers later purchases until your permit expires. When you renew your permit, expect the seller to ask for the new one. Sellers can look up your permit in OkTAP, the Commission\'s online system. Contractors cannot buy building materials for resale. If you misuse resale documents or use the items yourself, you owe the tax and can be fined $500.',
    'sources' => [
        [
            'title' => 'Publication D: Oklahoma Sales Tax Vendor Responsibilities - Exempt Sales (revised December 2025)',
            'url' => 'https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/general/Publication-D.pdf',
        ],
        [
            'title' => 'OTC Sales and Use Tax page',
            'url' => 'https://oklahoma.gov/tax/businesses/sales-use-tax.html',
        ],
        [
            'title' => 'SSTGB Form F0003 Certificate of Exemption (OTC copy)',
            'url' => 'https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/streamlined-sales-tax/F0003ExemptionCertificate.pdf',
        ],
        [
            'title' => 'OAC 710:65 Sales and Use Tax rules',
            'url' => 'https://oklahoma.gov/content/dam/ok/en/tax/documents/resources/rules-and-policies/agency-rules/Chapter65-2022.pdf',
        ],
        [
            'title' => 'OkTAP',
            'url' => 'https://oktap.tax.ok.gov/OkTAP/Web/',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10-14-2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
