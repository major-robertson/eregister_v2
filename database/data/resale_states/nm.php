<?php

/*
 * New Mexico: New Mexico Taxation and Revenue Department, Form Type 2 NTTC.
 * Researched 2026-10-01 from tax.newmexico.gov, realfile.tax.newmexico.gov,
 * srca.nm.gov, mtc.gov. Generated once from the EREG-8 resale research; edit
 * this file directly from now on.
 */

return [
    'state' => 'NM',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'New Mexico Taxation and Revenue Department',
        'short' => 'TRD',
        'url' => 'https://www.tax.newmexico.gov/',
    ],
    'resale_page_url' => 'https://www.tax.newmexico.gov/businesses/non-taxable-transaction-certificates-nttc/',
    'form' => [
        'number' => 'Type 2 NTTC',
        'title' => 'Nontaxable Transaction Certificate, Type 2 (resale of tangible personal property or licenses; ingredients for manufacturers; property for lease)',
        'pdf_url' => null,
        'prescribed' => true,
        'revision' => null,
        'notes' => 'NTTCs are issued by TRD, not downloaded. A registered buyer applies in the Taxpayer Access Point (TAP) or on Form ACD-31050 (https://realfile.tax.newmexico.gov/acd-31050.pdf), then executes the certificate to a seller in TAP. Type 2 and Type 5 are executed immediately in TAP. Type 5 covers services for resale. Out-of-state buyers not required to register may instead give the MTC uniform certificate, a Border States certificate, or a seller-obtained NTTC-OSB. Guidance: FYI-204 (Rev. 8/24/2026).',
    ],
    'issuer_model' => 'state_issued',
    'registration' => [
        'name' => 'New Mexico gross receipts tax registration (Business Tax Identification Number with a gross receipts tax account)',
        'number_name' => 'New Mexico Business Tax Identification Number (NMBTIN)',
        'format' => '11 digits',
        'verify_url' => 'https://tap.state.nm.us/TAP/_/',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'FYI-204 Rev. 8/24/2026, Other Acceptable Certificates: https://realfile.tax.newmexico.gov/FYI-204.pdf; 3.2.201.13 NMAC; MTC certificate note 21',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'New Mexico is not an SST member; FYI-204 lists only the NTTC-OSB, MTC and Border States certificates as other acceptable certificates.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'FYI-204 Rev. 8/24/2026 (Type OSB NTTCs; MTC; Border State certificates); 3.2.201.17 NMAC',
            'notes' => 'TRD says other states\' resale certificates are not valid in New Mexico. A buyer not required to register in New Mexico may use its home-state registration number on an NTTC-OSB (obtained by the seller, who must collect proof of the buyer\'s out-of-state registration), on the MTC certificate, or on a Border States certificate (AZ, CA, OK, TX, UT, Mexico, goods transported out).',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'FYI-204, Using NTTCs: the seller can refer an unlimited number of transactions to one NTTC of the matching type from the buyer.',
        ],
    ],
    'expiration' => [
        'label' => 'No fixed expiration',
        'summary' => 'NTTCs do not expire on a schedule; TRD says the 1992-series paper NTTCs have no expiration date. NTTCs under an NMBTIN stop being valid for transactions after the buyer\'s gross receipts tax account is closed. TRD can suspend a buyer\'s right to use NTTCs for misuse.',
        'cite' => 'TRD NTTC page; FYI-204 Rev. 8/24/2026, Business Closure; Section 7-9-44 NMSA 1978; 3.2.201.15 NMAC',
    ],
    'good_faith' => [
        'summary' => 'A seller that accepts a properly executed NTTC in good faith has conclusive evidence that the receipts are deductible. The seller must check that the transaction is the kind the NTTC type covers. In an audit the seller has 60 days from notice to produce NTTCs; after that, other evidence may be allowed under Section 7-9-43.',
        'cite' => 'Section 7-9-43 NMSA 1978; 3.2.201.8 and 3.2.201.10 NMAC; FYI-204, Good Faith Acceptance of an NTTC',
    ],
    'misuse_penalty' => [
        'summary' => 'A buyer who uses property or services other than as the NTTC states, or gives false information, owes the gross receipts tax the seller would have owed, plus penalty and interest. TRD can suspend the buyer\'s right to execute NTTCs.',
        'cite' => 'Section 7-9-43 NMSA 1978; Section 7-9-44 NMSA 1978; FYI-204, Gross Receipts Tax Liability for Misuse of NTTCs',
    ],
    'facts' => [
        [
            'text' => 'New Mexico has a gross receipts tax, owed by the seller, not a sales tax; a resale certificate here is a deduction document called a Nontaxable Transaction Certificate.',
            'source_url' => 'https://www.tax.newmexico.gov/businesses/non-taxable-transaction-certificates-nttc/',
        ],
        [
            'text' => 'Services bought for resale need a Type 5 NTTC; the MTC certificate cannot be used for them.',
            'source_url' => 'https://realfile.tax.newmexico.gov/FYI-204.pdf',
        ],
        [
            'text' => 'NTTCs executed in TAP appear in both the buyer\'s and the seller\'s TAP accounts and need not be printed.',
            'source_url' => 'https://realfile.tax.newmexico.gov/FYI-204.pdf',
        ],
        [
            'text' => 'NTTCs may not be used for business services deducted as ordinary business expenses, for capitalized services, or for personal use; a car dealer that uses parts in its own vehicles owes the tax.',
            'source_url' => 'https://realfile.tax.newmexico.gov/FYI-204.pdf',
        ],
        [
            'text' => 'FYI-204, the Department\'s NTTC guide, was revised August 24, 2026.',
            'source_url' => 'https://realfile.tax.newmexico.gov/FYI-204.pdf',
        ],
    ],
    'state_notes' => 'New Mexico does not use an ordinary resale certificate. The New Mexico Taxation and Revenue Department issues Nontaxable Transaction Certificates, called NTTCs. For goods you will resell, you need a Type 2 NTTC. For services you will resell, you need a Type 5 NTTC. First register for gross receipts tax and get your 11-digit New Mexico Business Tax Identification Number. Then apply for NTTCs in the Taxpayer Access Point (TAP) or on Form ACD-31050. In TAP you execute the NTTC to your supplier by entering the supplier\'s tax ID. Both of you can then see it in TAP. One NTTC covers all your later purchases of that kind from that supplier. It stays valid until your gross receipts tax account closes. Other states\' resale certificates are not valid in New Mexico. If your business is out of state and not required to register here, you may give the Multistate Tax Commission certificate for goods, or ask the seller for an NTTC-OSB. If you misuse an NTTC, you owe the tax the seller would have paid, plus penalty and interest. The Department can also suspend your right to use NTTCs.',
    'sources' => [
        [
            'title' => 'TRD Non-Taxable Transaction Certificates (NTTC)',
            'url' => 'https://www.tax.newmexico.gov/businesses/non-taxable-transaction-certificates-nttc/',
        ],
        [
            'title' => 'FYI-204 Nontaxable Transaction Certificates (Rev. 8/24/2026)',
            'url' => 'https://realfile.tax.newmexico.gov/FYI-204.pdf',
        ],
        [
            'title' => 'Form ACD-31050 Application for Nontaxable Transaction Certificates',
            'url' => 'https://realfile.tax.newmexico.gov/acd-31050.pdf',
        ],
        [
            'title' => '3.2.201 NMAC Gross Receipts - Deductions - General Provisions',
            'url' => 'https://www.srca.nm.gov/parts/title03/03.002.0201.html',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10-14-2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
