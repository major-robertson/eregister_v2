<?php

/*
 * Missouri: Missouri Department of Revenue, Form 149. Researched 2026-10-01
 * from dor.mo.gov, revisor.mo.gov, mtc.gov. Generated once from the EREG-8
 * resale research; edit this file directly from now on.
 */

return [
    'state' => 'MO',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Missouri Department of Revenue',
        'short' => 'the Department of Revenue (DOR)',
        'url' => 'https://dor.mo.gov/',
    ],
    'resale_page_url' => 'https://dor.mo.gov/taxation/business/tax-types/sales-use/exemptions.php',
    'form' => [
        'number' => '149',
        'title' => 'Sales and Use Tax Exemption Certificate',
        'pdf_url' => 'https://dor.mo.gov/forms/149.pdf',
        'prescribed' => true,
        'revision' => 'Revised 08-2026',
        'notes' => 'One form for resale and all other exemptions; the buyer ticks the resale box. Tire and lead-acid battery fees need the separate Form 149T.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Missouri Retail Sales License',
        'number_name' => 'Missouri Tax I.D. Number',
        'format' => '8 digits',
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10-14-2022) lists MO with note 19: https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'Missouri is not a Streamlined Sales Tax member state and is not listed on SSTGB Form F0003; DOR prescribes Form 149 (https://dor.mo.gov/forms/149.pdf).',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Form 149 (Rev. 08-2026), resale section and instructions: https://dor.mo.gov/forms/149.pdf',
            'notes' => 'For tangible personal property the buyer gives its retailer\'s state tax ID and home state; the instructions say the number can come from a Missouri retail license or an out-of-state registration. Missouri retailers must use their Missouri number. Resale of taxable services needs a Missouri retail license. Manufacturers and wholesalers buying for wholesale need no Missouri number.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => '12 CSR 10-107.100(3)(C): a seller may rely on a certificate on file for future sales unless it does not apply or good faith is lost. https://dor.mo.gov/resources/official-final-rules/documents/12_CSR_10-107_100_Use_of_Reliance_on_Exemption_Certificates.pdf',
        ],
    ],
    'expiration' => [
        'label' => 'Update every 5 years',
        'summary' => 'The certificate has no printed expiration date. DOR\'s audit FAQ says a certificate kept by the seller must be updated every five years and that older certificates should be kept with the new ones.',
        'cite' => 'DOR Tax Audit FAQs, https://dor.mo.gov/faq/taxation/business/tax-audits.html (cites 12 CSR 10-107.100; the rule text as amended effective March 30, 2024 does not itself state a five-year period)',
    ],
    'good_faith' => [
        'summary' => 'A seller that receives and accepts an exemption certificate in good faith does not have to collect the tax; if the certificate is invalid the purchaser owes the tax. Good faith means honesty of intention and no knowledge of facts that should prompt inquiry. If the seller does not act in good faith, seller and purchaser are jointly liable. The seller must name the purchaser on each invoice, and an unsigned certificate is invalid.',
        'cite' => 'Section 32.200 RSMo (Art. V); Section 144.210 RSMo; 12 CSR 10-107.100(1)-(3) and example (D)',
    ],
    'misuse_penalty' => [
        'summary' => 'A purchaser who claims an exemption it is not entitled to, or later uses the goods in a taxable way, owes the tax. DOR may collect the tax, interest, additions to tax and penalty directly from the purchaser.',
        'cite' => 'Section 144.210 RSMo (https://revisor.mo.gov/main/OneSection.aspx?section=144.210); 12 CSR 10-107.100(3)(E); MTC certificate note 19',
    ],
    'facts' => [
        [
            'text' => 'A buyer that resells taxable services (restaurants, lodging, amusement, telecommunications, utilities) must hold a Missouri retail license to claim resale; the form says a seller cannot take a services resale certificate in good faith otherwise.',
            'source_url' => 'https://dor.mo.gov/forms/149.pdf',
        ],
        [
            'text' => 'Manufacturers and wholesalers buying for wholesale can claim the exclusion on Form 149 without a Missouri Tax I.D. Number.',
            'source_url' => 'https://dor.mo.gov/forms/149.pdf',
        ],
        [
            'text' => 'Since January 1, 2023, the Section 144.054 RSMo manufacturing exemptions apply to local sales tax as well as state tax.',
            'source_url' => 'https://dor.mo.gov/forms/149.pdf',
        ],
        [
            'text' => 'If a buyer later issues a purchase order saying a specific purchase is taxable, the seller must collect tax on it even with a certificate on file.',
            'source_url' => 'https://dor.mo.gov/resources/official-final-rules/documents/12_CSR_10-107_100_Use_of_Reliance_on_Exemption_Certificates.pdf',
        ],
        [
            'text' => 'When goods bought for resale are taken out of stock for the buyer\'s own use, the buyer owes sales tax at the seller\'s location rate (rule example: mops removed from a grocery\'s inventory).',
            'source_url' => 'https://dor.mo.gov/resources/official-final-rules/documents/12_CSR_10-107_100_Use_of_Reliance_on_Exemption_Certificates.pdf',
        ],
    ],
    'state_notes' => 'Missouri uses Form 149, the Sales and Use Tax Exemption Certificate, from the Missouri Department of Revenue. The same form covers resale and other exemptions. The buyer fills it out, ticks the resale box, signs it and gives it to the seller. Do not send it to the Department. For goods you will resell, enter your state tax ID number and your home state. If you are a Missouri retailer, use your 8-digit Missouri Tax I.D. Number from your retail sales license. Out-of-state buyers can give their home-state registration for goods. To resell taxable services, such as lodging or telecommunications, you must hold a Missouri retail license. One certificate can cover future purchases from the same seller until it no longer applies. The Department\'s audit guidance tells sellers to update certificates every five years, so expect a request for a new one. The seller is protected if it accepts the certificate in good faith. If you use goods bought tax-free for your own business, or claim an exemption you do not qualify for, you owe the tax. The Department can also bill you for interest, additions to tax and penalties under Section 144.210 RSMo.',
    'sources' => [
        [
            'title' => 'Form 149 Sales and Use Tax Exemption Certificate (Rev. 08-2026)',
            'url' => 'https://dor.mo.gov/forms/149.pdf',
        ],
        [
            'title' => '12 CSR 10-107.100 Use of and Reliance on Exemption Certificates',
            'url' => 'https://dor.mo.gov/resources/official-final-rules/documents/12_CSR_10-107_100_Use_of_Reliance_on_Exemption_Certificates.pdf',
        ],
        [
            'title' => 'Missouri DOR Tax Audit FAQs',
            'url' => 'https://dor.mo.gov/faq/taxation/business/tax-audits.html',
        ],
        [
            'title' => 'Section 144.210 RSMo',
            'url' => 'https://revisor.mo.gov/main/OneSection.aspx?section=144.210',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate - Multijurisdiction (rev. 10-14-2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
        [
            'title' => 'Missouri sales/use tax exemptions search',
            'url' => 'https://dor.mo.gov/taxation/business/tax-types/sales-use/exemptions.php',
        ],
    ],
];
