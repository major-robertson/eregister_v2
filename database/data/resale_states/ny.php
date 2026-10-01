<?php

/*
 * New York: New York State Department of Taxation and Finance, Form ST-120.
 * Researched 2026-10-01 from tax.ny.gov, www8.tax.ny.gov. Generated once from
 * the EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'NY',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'New York State Department of Taxation and Finance',
        'short' => 'the Tax Department',
        'url' => 'https://www.tax.ny.gov/',
    ],
    'resale_page_url' => 'https://www.tax.ny.gov/pubs_and_bulls/tg_bulletins/st/exemption_certificates_for_sales_tax.htm',
    'form' => [
        'number' => 'ST-120',
        'title' => 'New York State and Local Sales and Use Tax Resale Certificate',
        'pdf_url' => 'https://www.tax.ny.gov/pdf/current_forms/st/st120_fill_in.pdf',
        'prescribed' => true,
        'revision' => '1/26',
        'notes' => 'Part 1 is for registered New York vendors; Part 2 is for non-New York purchasers not required to register. Contractors use Form ST-120.1 instead.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'New York State Certificate of Authority (sales tax vendor registration)',
        'number_name' => 'New York sales tax identification number',
        'format' => null,
        'verify_url' => 'https://www8.tax.ny.gov/STVL/stvlStart',
    ],
    'accepts' => [
        'mtc' => [
            'value' => false,
            'cite' => 'Tax Bulletin \'Exemption Certificates for Sales Tax\' (updated Jan. 21, 2026): exemption certificates of other states or countries are not valid in New York. New York is not on the MTC uniform certificate\'s state list.',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'New York is not a Streamlined Sales Tax member and is not listed on SSTGB Form F0003; same Tax Bulletin.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Form ST-120 (1/26), Part 2 and instructions: https://www.tax.ny.gov/pdf/current_forms/st/st120_fill_in.pdf',
            'notes' => 'Only on Form ST-120, Part 2, by a buyer that is not required to register in New York, gives its home-state (or VAT) registration number, and buys goods either delivered by the seller to its customer or to an unaffiliated fulfillment provider in New York, or resold from a business outside New York. Other states\' certificates are not valid.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Form ST-120 (1/26) instructions: the Blanket certificate box covers all purchases of the same general type; temporary vendors may not issue blanket certificates.',
        ],
    ],
    'expiration' => [
        'label' => 'No fixed expiration',
        'summary' => 'No fixed expiration. A blanket certificate stays in effect while exempt purchases continue; the buyer must give an updated certificate if its address, ID number or other information changes. A temporary vendor\'s certificate shows the expiration date of its registration and is single-use only. Sellers keep certificates at least three years after the related return\'s due date or filing date, if later.',
        'cite' => 'Tax Bulletin \'Exemption Certificates for Sales Tax\' (https://www.tax.ny.gov/pubs_and_bulls/tg_bulletins/st/exemption_certificates_for_sales_tax.htm); Form ST-120 (1/26)',
    ],
    'good_faith' => [
        'summary' => 'The seller is protected if the certificate is properly completed, accepted in good faith, and in the seller\'s possession within 90 days of the transaction. Good faith means the seller has no knowledge the certificate is false or fraudulent and uses reasonable ordinary care. A late certificate puts the burden of proof on both seller and buyer. A timely but incomplete certificate is satisfactory if corrected within a reasonable period.',
        'cite' => 'Form ST-120 (1/26) instructions, To the seller; Tax Bulletin \'Exemption Certificates for Sales Tax\'',
    ],
    'misuse_penalty' => [
        'summary' => 'Besides the tax and interest, misuse can bring a penalty equal to 100% of the tax due, a $50 penalty for each fraudulent certificate, felony prosecution with a fine and possible jail, and revocation of the Certificate of Authority. Doing business in New York without a required Certificate of Authority carries up to $500 for the first day and $200 for each later day, up to $10,000.',
        'cite' => 'Form ST-120 (1/26), Misuse of this certificate; Tax Law section 1838 (certificate deemed filed with the Department)',
    ],
    'facts' => [
        [
            'text' => 'The January 2026 revision of ST-120 adds hotel and short-term rental unit operators to Part 1 and a separate line for restaurant-type food, heated food or heated drink bought for resale.',
            'source_url' => 'https://www.tax.ny.gov/pdf/current_forms/st/st120_fill_in.pdf',
        ],
        [
            'text' => 'A business not otherwise required to register may keep goods at an unaffiliated New York fulfillment service provider without having to register for sales tax.',
            'source_url' => 'https://www.tax.ny.gov/pdf/current_forms/st/st120_fill_in.pdf',
        ],
        [
            'text' => 'Contractors may not use ST-120 to buy materials and supplies; they use Form ST-120.1, Contractor Exempt Purchase Certificate.',
            'source_url' => 'https://www.tax.ny.gov/pdf/current_forms/st/st120_fill_in.pdf',
        ],
        [
            'text' => 'ST-120 cannot be used to buy motor fuel or diesel motor fuel for resale.',
            'source_url' => 'https://www.tax.ny.gov/pdf/current_forms/st/st120_fill_in.pdf',
        ],
        [
            'text' => 'A buyer that uses goods bought for resale in New York must report and pay the tax directly to the state.',
            'source_url' => 'https://www.tax.ny.gov/pdf/current_forms/st/st120_fill_in.pdf',
        ],
    ],
    'state_notes' => 'New York uses Form ST-120, the Resale Certificate, from the New York State Department of Taxation and Finance. If you are registered in New York, complete Part 1 and enter the sales tax identification number from your Certificate of Authority. If your business is outside New York and not required to register here, complete Part 2 with your home-state registration number. Part 2 only covers goods shipped to your customers or a fulfillment provider in New York, or goods you resell from outside the state. Describe your business and what you sell. An owner, partner or authorized person signs and dates it. Give it to the seller within 90 days of the purchase. Mark it as single-use or blanket. A blanket certificate covers later purchases of the same kind until your information changes. Temporary vendors may only issue single-use certificates. New York does not accept other states\' certificates, the Multistate Tax Commission form, or the Streamlined form. Sellers can check your number with the Department\'s vendor lookup. Misuse can lead to a penalty equal to the full tax, $50 for each false certificate, criminal charges and loss of your Certificate of Authority.',
    'sources' => [
        [
            'title' => 'Form ST-120 Resale Certificate (1/26)',
            'url' => 'https://www.tax.ny.gov/pdf/current_forms/st/st120_fill_in.pdf',
        ],
        [
            'title' => 'Tax Bulletin: Exemption Certificates for Sales Tax (updated Jan. 21, 2026)',
            'url' => 'https://www.tax.ny.gov/pubs_and_bulls/tg_bulletins/st/exemption_certificates_for_sales_tax.htm',
        ],
        [
            'title' => 'Registered Sales Tax Vendor Look Up',
            'url' => 'https://www8.tax.ny.gov/STVL/stvlStart',
        ],
        [
            'title' => 'Online Services for businesses',
            'url' => 'https://www.tax.ny.gov/online/bus.htm',
        ],
    ],
];
