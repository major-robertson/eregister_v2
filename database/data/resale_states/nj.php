<?php

/*
 * New Jersey: New Jersey Division of Taxation (Department of the Treasury),
 * Form ST-3. Researched 2026-10-01 from nj.gov, law.cornell.edu. Generated
 * once from the EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'NJ',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'New Jersey Division of Taxation (Department of the Treasury)',
        'short' => 'the Division of Taxation',
        'url' => 'https://www.nj.gov/treasury/taxation/',
    ],
    'resale_page_url' => 'https://www.nj.gov/treasury/taxation/pdf/pubs/sales/su6.pdf',
    'form' => [
        'number' => 'ST-3',
        'title' => 'New Jersey Sales Tax Resale Certificate',
        'pdf_url' => 'https://www.nj.gov/treasury/taxation/pdf/other_forms/sales/st3.pdf',
        'prescribed' => true,
        'revision' => '3-23',
        'notes' => 'Out-of-state sellers that are not registered and not required to register in New Jersey use Form ST-3NR, Resale Certificate for Non-New Jersey Sellers (rev. 3-17): https://www.nj.gov/treasury/taxation/pdf/other_forms/sales/st3nr.pdf. Form ST-SST (Streamlined) may be used in place of most Division certificates.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'New Jersey Certificate of Authority (Sales and Use Tax)',
        'number_name' => 'New Jersey Taxpayer Identification Number',
        'format' => '12 digits: the FEIN followed by a 3-digit suffix (usually 000)',
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'S&U-6 (Rev. 6/24), Drop Shipment Transactions: an out-of-state retailer may give the MTC Uniform certificate; N.J.A.C. 18:24-10.5 allows a Division-approved multi-jurisdictional certificate from out-of-state sellers. https://www.nj.gov/treasury/taxation/pdf/pubs/sales/su6.pdf',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'S&U-6 (Rev. 6/24): purchasers may issue and New Jersey sellers may accept Form ST-SST in lieu of the general use certificates. New Jersey is an SST full member.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Form ST-3NR (3-17) and S&U-6 (Rev. 6/24), Unregistered Purchasers and Form ST-3NR sections; N.J.A.C. 18:24-10.5',
            'notes' => 'Only for buyers that are not registered and not required to be registered in New Jersey, and are registered in another state. They give their out-of-state registration number on ST-3NR, an out-of-state resale certificate, MTC or ST-SST. Not available if the buyer was required to register in New Jersey.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Form ST-3 instructions, Blanket Certificates; N.J.A.C. 18:24-10.5',
        ],
    ],
    'expiration' => [
        'label' => 'No fixed expiration',
        'summary' => 'No fixed expiration. A blanket certificate covers repeat purchases of the same general type while the seller has a recurring business relationship with the buyer, meaning no more than 12 months pass between sales. Sellers keep certificates for four years from the last sale covered.',
        'cite' => 'Form ST-3 (3-23) instructions; N.J.A.C. 18:24-10.5',
    ],
    'good_faith' => [
        'summary' => 'A registered seller is relieved of liability if it receives a fully completed certificate within 90 days of the sale, even if the buyer claimed the exemption improperly; the buyer then owes the tax. In an audit, the seller has at least 120 days after the Division\'s request to get a fully completed certificate taken in good faith or other proof. Relief is lost if the seller knew or had reason to know the information was materially false.',
        'cite' => 'Form ST-3 (3-23) instructions, Accepting the Certificate; N.J.A.C. 18:24-10.5',
    ],
    'misuse_penalty' => [
        'summary' => 'A purchaser who claims an improper exemption is responsible for the tax, interest and penalties. The buyer signs ST-3 under the penalties for perjury and false swearing.',
        'cite' => 'S&U-6 (Rev. 6/24), Streamlined Sales and Use Tax Agreement section; Form ST-3 certification',
    ],
    'facts' => [
        [
            'text' => 'A seller must itself be registered with New Jersey to accept an exemption certificate.',
            'source_url' => 'https://www.nj.gov/treasury/taxation/pdf/other_forms/sales/st3.pdf',
        ],
        [
            'text' => 'On a drop shipment, an out-of-state retailer may give a New Jersey supplier its home-state resale certificate, the MTC certificate, Form ST-3NR or Form ST-SST.',
            'source_url' => 'https://www.nj.gov/treasury/taxation/pdf/pubs/sales/su6.pdf',
        ],
        [
            'text' => 'Contractors cannot issue Form ST-3 for materials and supplies; work for exempt organizations or qualified UEZ businesses uses Form ST-13 or UZ-4.',
            'source_url' => 'https://www.nj.gov/treasury/taxation/pdf/other_forms/sales/st3.pdf',
        ],
        [
            'text' => 'ST-3 also covers property used in performing a taxable service on personal property when it becomes part of the property serviced, such as auto parts a service station installs.',
            'source_url' => 'https://www.nj.gov/treasury/taxation/pdf/other_forms/sales/st3.pdf',
        ],
        [
            'text' => 'Under a blanket certificate, each later invoice must show the purchaser\'s name, address and identification number.',
            'source_url' => 'https://www.nj.gov/treasury/taxation/pdf/other_forms/sales/st3.pdf',
        ],
    ],
    'state_notes' => 'New Jersey uses Form ST-3, the Resale Certificate, from the New Jersey Division of Taxation. Only a buyer that holds a New Jersey Certificate of Authority can issue it. Enter your 12-digit New Jersey Taxpayer Identification Number, which is usually your federal EIN plus three digits. Fill in your name and address, your type of business, what you sell and what you are buying. Then check why you are buying it, such as resale in its present form. An owner, partner or officer signs it. Give it to the seller and do not mail it to the Division. You can mark it for a single purchase or as a blanket certificate. A blanket certificate stays good while you keep buying from that seller at least once every 12 months. If your business is out of state and not required to register in New Jersey, use Form ST-3NR with your home-state registration number instead. The Streamlined Sales and Use Tax certificate (Form ST-SST) is also accepted. If you claim an exemption you do not qualify for, you owe the tax plus interest and penalties. You sign under penalty of perjury and false swearing.',
    'sources' => [
        [
            'title' => 'Form ST-3 Resale Certificate (3-23)',
            'url' => 'https://www.nj.gov/treasury/taxation/pdf/other_forms/sales/st3.pdf',
        ],
        [
            'title' => 'Form ST-3NR Resale Certificate for Non-New Jersey Sellers (3-17)',
            'url' => 'https://www.nj.gov/treasury/taxation/pdf/other_forms/sales/st3nr.pdf',
        ],
        [
            'title' => 'Tax Topic Bulletin S&U-6 Sales Tax Exemption Administration (Rev. 6/24)',
            'url' => 'https://www.nj.gov/treasury/taxation/pdf/pubs/sales/su6.pdf',
        ],
        [
            'title' => 'N.J.A.C. 18:24-10.5 Exemption certificates (Cornell LII)',
            'url' => 'https://www.law.cornell.edu/regulations/new-jersey/N-J-A-C-18-24-10-5',
        ],
        [
            'title' => 'Starting a Business in New Jersey (Division of Taxation guide)',
            'url' => 'https://nj.gov/treasury/taxation/documents/pdf/guides/Starting-a-Business-in-New-Jersey.pdf',
        ],
    ],
];
