<?php

/*
 * Michigan: Michigan Department of Treasury, Form 3372. Researched 2026-10-01
 * from michigan.gov, mtc.gov. Generated once from the EREG-8 resale research;
 * edit this file directly from now on.
 */

return [
    'state' => 'MI',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Michigan Department of Treasury',
        'short' => 'Treasury',
        'url' => 'https://www.michigan.gov/treasury',
    ],
    'resale_page_url' => 'https://www.michigan.gov/taxes/rep-legal/rab/2024-revenue-administrative-bulletins/revenue-administrative-bulletin-2024-11',
    'form' => [
        'number' => '3372',
        'title' => 'Michigan Sales and Use Tax Certificate of Exemption',
        'pdf_url' => 'https://www.michigan.gov/taxes/-/media/Project/Websites/taxes/Forms/SUW/3372.pdf',
        'prescribed' => false,
        'revision' => 'Rev. 02-25',
        'notes' => 'Form 3372 is Treasury\'s multi-purpose exemption form (box \'For Resale at Retail\' or \'For Resale at Wholesale\'). Under RAB 2024-11, Treasury also accepts the MTC Uniform certificate, the SST Certificate of Exemption, a qualifying purchase order, or the required information in another format such as a signed letter on the purchaser\'s letterhead.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Michigan sales tax license',
        'number_name' => 'Michigan sales tax license number (or use tax registration number for lessors)',
        'format' => null,
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'RAB 2024-11, Part 2(C); MTC certificate note 17',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'RAB 2024-11, Part 2(E); Michigan is an SST member state',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'RAB 2024-11, Part 1; MCL 205.68(1)',
            'notes' => 'For a resale claim the seller must record the purchaser\'s sales tax license number only \'if the purchaser has a sales tax license\'; an out-of-state reseller not licensed in Michigan can claim the resale exemption with its exemption certificate (e.g. MTC or SST form listing its home-state registration).',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'MCL 205.68(7); MCL 205.104a(6); RAB 2024-11, Part 3; Form 3372 Section 1',
        ],
    ],
    'expiration' => [
        'label' => 'Up to 4 years',
        'summary' => 'A blanket certificate remains in effect for four years unless the parties agree in writing on a shorter period. If the seller and purchaser have a recurring business relationship (no more than 12 months between sales), it never needs to be renewed or updated. Form 3372 offers both options: a recurring-relationship blanket, or a blanket with an expiration date of up to four years.',
        'cite' => 'MCL 205.68(7); MCL 205.104a(6); RAB 2024-11, Part 3; Form 3372 instructions',
    ],
    'good_faith' => [
        'summary' => 'A seller that obtains a properly completed certificate (or the required data elements) within 120 days after the sale is generally not liable for the tax even if the purchaser improperly claims an exemption; the purchaser is then liable. Relief does not apply in limited cases such as fraud by the seller. A seller missing a certificate may still obtain one or prove the sale was exempt by other means by the later of 120 days after Treasury\'s request or other listed audit dates. Sellers may not rely on a \'tax exempt number\' in place of a valid claim.',
        'cite' => 'MCL 205.62(5)-(7); MCL 205.104b(5)-(7); RAB 2024-11, Parts 6-7; RAB 2016-14 (cited in MTC note 17)',
    ],
    'misuse_penalty' => [
        'summary' => 'A purchaser who improperly claims an exemption is liable for the tax, penalty, and interest, with limited exceptions. The certificate is signed under penalty of perjury.',
        'cite' => 'Form 3372 (Rev. 02-25) instructions; MCL 205.62(5); MCL 205.104b(5)',
    ],
    'facts' => [
        [
            'text' => 'Michigan does not issue tax exemption numbers; a seller may not rely on a number in place of a valid exemption claim. For a resale claim, the seller does record the buyer\'s sales tax license number if it has one.',
            'source_url' => 'https://www.michigan.gov/taxes/-/media/Project/Websites/taxes/Forms/SUW/3372.pdf',
        ],
        [
            'text' => 'Sellers must keep exemption records for at least four years from when the tax was due, as completed certificates or the same data in another format such as a spreadsheet.',
            'source_url' => 'https://www.michigan.gov/taxes/rep-legal/rab/2024-revenue-administrative-bulletins/revenue-administrative-bulletin-2024-11',
        ],
        [
            'text' => 'A signature is required only on a paper certificate; electronic certificates need none.',
            'source_url' => 'https://www.michigan.gov/taxes/-/media/Project/Websites/taxes/Forms/SUW/3372.pdf',
        ],
        [
            'text' => 'Sales of alcohol to liquor licensees for resale (including micro brewers since 2024 PA 61 and 63) are documented by recording the purchaser\'s Michigan Liquor Control Commission license number.',
            'source_url' => 'https://www.michigan.gov/taxes/rep-legal/rab/2024-revenue-administrative-bulletins/revenue-administrative-bulletin-2024-11',
        ],
        [
            'text' => 'A lessor that elects to pay use tax on rental receipts must give the seller its sales tax license or use tax registration number; contractors claiming an exemption attach Form 3520.',
            'source_url' => 'https://www.michigan.gov/taxes/-/media/Project/Websites/taxes/Forms/SUW/3372.pdf',
        ],
    ],
    'state_notes' => 'In Michigan, buyers use Form 3372, Michigan Sales and Use Tax Certificate of Exemption, from the Michigan Department of Treasury. The current version is Rev. 02-25. Treasury also accepts the MTC uniform certificate, the Streamlined Sales Tax certificate, or a signed letter with the same information. On Form 3372, choose a one-time purchase or a blanket certificate. Enter the seller\'s name and address. Check whether the claim covers all items or only listed items. Check \'For Resale at Retail\' and enter your Michigan sales tax license number, or \'For Resale at Wholesale.\' Out-of-state buyers without a Michigan license may still claim resale. The purchaser completes Section 4 and signs if the form is on paper. A blanket certificate lasts four years, or less if you agree. It never needs renewing while you buy from the seller at least once every 12 months. Michigan does not issue tax-exempt numbers, so the seller needs the completed form. A seller with a completed certificate is generally protected. If you claim an exemption you do not qualify for, you owe the tax, penalty and interest. Do not send the form to Treasury unless asked.',
    'sources' => [
        [
            'title' => 'Form 3372 (Rev. 02-25)',
            'url' => 'https://www.michigan.gov/taxes/-/media/Project/Websites/taxes/Forms/SUW/3372.pdf',
        ],
        [
            'title' => 'Revenue Administrative Bulletin 2024-11',
            'url' => 'https://www.michigan.gov/taxes/rep-legal/rab/2024-revenue-administrative-bulletins/revenue-administrative-bulletin-2024-11',
        ],
        [
            'title' => 'Exemptions FAQ',
            'url' => 'https://www.michigan.gov/taxes/business-taxes/sales-use-tax/information/exemptions-faq',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
