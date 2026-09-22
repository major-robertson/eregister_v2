<?php

/*
| State facts for the sales tax and resale certificate ads pages. Each entry
| is what the state's own agency says (read 2026-09-22); keep the wording
| cautious ("the Comptroller says to allow") and update the timing when an
| agency changes it. States not listed here get the generic copy.
|
| term            what the state calls the sales tax registration
| agency          who issues it
| state_fee       the state's own charge, separate from our fee
| state_timing    how long the state takes once the application is in
| resale_form     the resale certificate the state expects from a buyer
| resale_note     one line on how resale certificates work there
*/

return [
    'TX' => [
        'term' => 'Sales and Use Tax Permit',
        'agency' => 'Texas Comptroller of Public Accounts',
        'state_fee' => 'No state fee. The Comptroller can ask for a security bond.',
        'state_timing' => 'The Comptroller says to allow 2 to 3 weeks to receive the permit.',
        'resale_form' => 'Form 01-339, Texas Sales and Use Tax Resale Certificate',
        'resale_note' => 'You fill in Form 01-339 with your Texas taxpayer number and give it to each vendor.',
    ],
    'NY' => [
        'term' => 'Certificate of Authority',
        'agency' => 'New York State Department of Taxation and Finance',
        'state_fee' => 'No state fee.',
        'state_timing' => 'New York asks you to apply at least 20 days before you start selling.',
        'resale_form' => 'Form ST-120, Resale Certificate',
        'resale_note' => 'New York does not accept the multistate (MTC) form, so each vendor gets a completed ST-120.',
    ],
    'FL' => [
        'term' => 'Sales Tax Registration',
        'agency' => 'Florida Department of Revenue',
        'state_fee' => 'No fee for an online registration ($5 by paper).',
        'state_timing' => 'The Department says to allow about 3 business days for an online application.',
        'resale_form' => 'Form DR-13, Florida Annual Resale Certificate for Sales Tax',
        'resale_note' => 'Florida issues the Annual Resale Certificate itself once you are registered, and renews it every November.',
    ],
    'CA' => [
        'term' => "Seller's Permit",
        'agency' => 'California Department of Tax and Fee Administration (CDTFA)',
        'state_fee' => 'No fee. The CDTFA can ask for a security deposit.',
        'state_timing' => 'The CDTFA can often issue the permit the same day for an online application.',
        'resale_form' => 'Form CDTFA-230, General Resale Certificate',
        'resale_note' => "Your seller's permit number goes on a CDTFA-230 for each vendor you buy from for resale.",
    ],
    'IL' => [
        'term' => 'Certificate of Registration',
        'agency' => 'Illinois Department of Revenue',
        'state_fee' => 'No state fee.',
        'state_timing' => 'Online registrations through MyTax Illinois are usually processed in 1 to 2 business days.',
        'resale_form' => 'Form CRT-61, Certificate of Resale',
        'resale_note' => 'Illinois expects a CRT-61 (or a certificate with the same information) with your Illinois account ID.',
    ],
    'GA' => [
        'term' => 'Sales and Use Tax Number',
        'agency' => 'Georgia Department of Revenue',
        'state_fee' => 'No state fee.',
        'state_timing' => 'Georgia usually emails the account number within 15 minutes of an online registration.',
        'resale_form' => 'Form ST-5, Sales Tax Certificate of Exemption',
        'resale_note' => 'Georgia accepts the ST-5 and the Streamlined Sales Tax certificate for resale purchases.',
    ],
    'NJ' => [
        'term' => 'Certificate of Authority',
        'agency' => 'New Jersey Division of Taxation',
        'state_fee' => 'No state fee.',
        'state_timing' => 'New Jersey asks you to register at least 15 business days before you start selling.',
        'resale_form' => 'Form ST-3, Resale Certificate',
        'resale_note' => 'Registered businesses use the ST-3 with their New Jersey tax ID for resale purchases.',
    ],
    'PA' => [
        'term' => 'Sales, Use and Hotel Occupancy Tax License',
        'agency' => 'Pennsylvania Department of Revenue',
        'state_fee' => 'No state fee.',
        'state_timing' => 'Pennsylvania typically issues the license in 7 to 10 business days.',
        'resale_form' => 'Form REV-1220, Pennsylvania Exemption Certificate',
        'resale_note' => 'Resale purchases in Pennsylvania use the REV-1220 with your sales tax license number.',
    ],
    'NC' => [
        'term' => 'Certificate of Registration',
        'agency' => 'North Carolina Department of Revenue',
        'state_fee' => 'No fee.',
        'state_timing' => 'The account number comes back right away online; the certificate is mailed within about 10 business days.',
        'resale_form' => 'Form E-595E, Streamlined Sales and Use Tax Certificate of Exemption',
        'resale_note' => 'North Carolina uses the Streamlined Sales Tax certificate (E-595E) for resale purchases.',
    ],
    'MI' => [
        'term' => 'Sales Tax License',
        'agency' => 'Michigan Department of Treasury',
        'state_fee' => 'No state fee.',
        'state_timing' => 'Michigan usually issues the license in about 7 business days for an online registration.',
        'resale_form' => 'Form 3372, Michigan Sales and Use Tax Certificate of Exemption',
        'resale_note' => 'Resale purchases in Michigan use Form 3372 with your sales tax license number.',
    ],
];
