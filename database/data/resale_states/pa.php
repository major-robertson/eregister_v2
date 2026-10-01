<?php

/*
 * Pennsylvania: Pennsylvania Department of Revenue, Form REV-1220. Researched
 * 2026-10-01 from pa.gov, pacodeandbulletin.gov, data.pa.gov, mtc.gov.
 * Generated once from the EREG-8 resale research; edit this file directly from
 * now on.
 */

return [
    'state' => 'PA',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Pennsylvania Department of Revenue',
        'short' => 'the Department of Revenue',
        'url' => 'https://www.pa.gov/agencies/revenue',
    ],
    'resale_page_url' => 'https://www.pa.gov/agencies/revenue/resources/tax-types-and-information/sales-use-and-hotel-occupancy-tax',
    'form' => [
        'number' => 'REV-1220',
        'title' => 'Pennsylvania Exemption Certificate',
        'pdf_url' => 'https://www.pa.gov/content/dam/copapwp-pagov/en/revenue/documents/formsandpublications/formsforbusinesses/sut/documents/rev-1220.pdf',
        'prescribed' => true,
        'revision' => 'REV-1220 (TR) 07-23',
        'notes' => 'One certificate for all exemptions, used as a unit (one transaction) or blanket (multiple transactions) certificate; reason 3 is resale. It also covers hotel occupancy tax, PTA taxes and fees and vehicle rental tax.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Pennsylvania Sales, Use and Hotel Occupancy Tax License',
        'number_name' => 'PA Sales Tax License ID',
        'format' => '8 digits',
        'verify_url' => 'https://data.pa.gov/Licenses-Certificates/Sales-Use-Hotel-Occupancy-Tax-Licenses-and-Certifi/ugeq-ckxd',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'Department SUT Audit Manual (Rev. 5/30/2024): the MTC certificate may be used only to claim the resale exemption under 61 Pa. Code 32.3; MTC note 25.',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'Pennsylvania is not a Streamlined Sales Tax member and is not listed on SSTGB Form F0003.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'REV-1220 (TR) 07-23, reason 3 and instructions; MTC note 25',
            'notes' => 'A buyer without a PA Sales Tax License ID may still claim resale on REV-1220 (or the MTC form) by explaining on line 8 why a PA number is not required, for example because it is registered only in another state.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'REV-1220 checkbox \'Pennsylvania Tax Blanket Exemption Certificate (use for multiple transactions)\'; 61 Pa. Code 32.2',
        ],
    ],
    'expiration' => [
        'label' => 'No expiration',
        'summary' => 'Neither the form nor 61 Pa. Code 32.2 sets an expiration date for a blanket certificate. Each transaction under a blanket certificate is treated separately, and the seller must exercise good faith in each one.',
        'cite' => '61 Pa. Code 32.2 (https://www.pacodeandbulletin.gov/Display/pacode?file=/secure/pacode/data/061/chapter32/s32.2.html)',
    ],
    'good_faith' => [
        'summary' => 'The seller must accept the certificate in good faith: it is properly completed, in the seller\'s possession within 60 days of the sale, contains nothing the seller knows or has reason to know is false, and the property is consistent with the exemption claimed. An invalid certificate may leave the seller owing the tax.',
        'cite' => 'REV-1220 (TR) 07-23 instructions, Acceptance and Validity; 61 Pa. Code 32.2',
    ],
    'misuse_penalty' => [
        'summary' => 'A false or fraudulent statement on an exemption certificate is a misdemeanor punishable by up to 1 year in prison, a fine up to $1,000, or both. The buyer also owes the tax on items not used as claimed.',
        'cite' => '61 Pa. Code 32.2',
    ],
    'facts' => [
        [
            'text' => 'Pennsylvania\'s state rate is 6%, with an extra local 1% in Allegheny County and 2% in Philadelphia; local tax is origin-based.',
            'source_url' => 'https://www.pa.gov/content/dam/copapwp-pagov/en/revenue/documents/taxlawpoliciesbulletinsnotices/auditmanuals/documents/sut_audit_manual.pdf',
        ],
        [
            'text' => 'REV-1220 cannot be used to obtain a sales tax license or exempt status, and cannot be used to claim exemption when registering a vehicle (use MV-1 or MV-4ST).',
            'source_url' => 'https://www.pa.gov/content/dam/copapwp-pagov/en/revenue/documents/formsandpublications/formsforbusinesses/sut/documents/rev-1220.pdf',
        ],
        [
            'text' => 'For canned software accessed remotely and billed to a Pennsylvania address, the buyer must state the total number of licenses and how many are used outside Pennsylvania.',
            'source_url' => 'https://www.pa.gov/content/dam/copapwp-pagov/en/revenue/documents/formsandpublications/formsforbusinesses/sut/documents/rev-1220.pdf',
        ],
        [
            'text' => 'The Department publishes a monthly-refreshed open data list of active sales tax licenses, exemption and wholesaler certificates.',
            'source_url' => 'https://data.pa.gov/Licenses-Certificates/Sales-Use-Hotel-Occupancy-Tax-Licenses-and-Certifi/ugeq-ckxd',
        ],
        [
            'text' => 'The date of the purchaser\'s signature is used to decide whether the certificate was provided within 60 days of the sale.',
            'source_url' => 'https://www.pa.gov/content/dam/copapwp-pagov/en/revenue/documents/taxlawpoliciesbulletinsnotices/auditmanuals/documents/sut_audit_manual.pdf',
        ],
    ],
    'state_notes' => 'Pennsylvania uses Form REV-1220, the Pennsylvania Exemption Certificate, from the Pennsylvania Department of Revenue. It covers resale and other exemptions. Check whether it is for one transaction or a blanket certificate for many. Enter the seller\'s name and address. Check reason 3, property or services to be resold. Enter your 8-digit PA Sales Tax License ID. If you do not have one, explain on line 8 why a Pennsylvania number is not required. Then sign and date it. Give it to the seller, not the Department. The seller must have it within 60 days of the sale. A blanket certificate has no set expiration, but the seller must still act in good faith on each sale. The Multistate Tax Commission certificate is also accepted, but only for resale. Pennsylvania does not take the Streamlined Sales Tax form. Sellers can check licenses on the state\'s open data list. A false statement on the certificate is a misdemeanor, with up to one year in prison and a $1,000 fine.',
    'sources' => [
        [
            'title' => 'REV-1220 Pennsylvania Exemption Certificate (TR 07-23)',
            'url' => 'https://www.pa.gov/content/dam/copapwp-pagov/en/revenue/documents/formsandpublications/formsforbusinesses/sut/documents/rev-1220.pdf',
        ],
        [
            'title' => '61 Pa. Code 32.2 Exemption certificates',
            'url' => 'https://www.pacodeandbulletin.gov/Display/pacode?file=/secure/pacode/data/061/chapter32/s32.2.html',
        ],
        [
            'title' => 'Sales and Use Tax Audit Manual (Rev. 5/30/2024)',
            'url' => 'https://www.pa.gov/content/dam/copapwp-pagov/en/revenue/documents/taxlawpoliciesbulletinsnotices/auditmanuals/documents/sut_audit_manual.pdf',
        ],
        [
            'title' => 'Open Data PA: Sales, Use & Hotel Occupancy Tax Licenses and Certificates',
            'url' => 'https://data.pa.gov/Licenses-Certificates/Sales-Use-Hotel-Occupancy-Tax-Licenses-and-Certifi/ugeq-ckxd',
        ],
        [
            'title' => 'Sales, Use and Hotel Occupancy Tax (Department of Revenue)',
            'url' => 'https://www.pa.gov/agencies/revenue/resources/tax-types-and-information/sales-use-and-hotel-occupancy-tax',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10-14-2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
