<?php

/*
 * Wyoming: Wyoming Department of Revenue, Excise Tax Division, Form F0003.
 * Researched 2026-10-01 from excise-tax-div.wyo.gov, law.cornell.edu,
 * wyoleg.gov, streamlinedsalestax.org. Generated once from the EREG-8 resale
 * research; edit this file directly from now on.
 */

return [
    'state' => 'WY',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Wyoming Department of Revenue, Excise Tax Division',
        'short' => 'the Excise Tax Division',
        'url' => 'https://excise-tax-div.wyo.gov/',
    ],
    'resale_page_url' => 'https://excise-tax-div.wyo.gov/general-administrative/excise-tax-faqs',
    'form' => [
        'number' => 'F0003',
        'title' => 'Streamlined Sales and Use Tax Agreement Certificate of Exemption',
        'pdf_url' => 'https://drive.google.com/file/d/18L7GmhBzRgIQ-B5t6fSN9qmn78v-3PZk/view?usp=sharing',
        'prescribed' => true,
        'revision' => 'SSTGB F0003 Revised 12/21/2021 (per SSTGB; the Division links its copy from Google Drive)',
        'notes' => 'Wyoming uses only the Streamlined Sales Tax certificate. Its rules require exemption certificates to be in the format prescribed by the Streamlined Sales and Use Tax Agreement (Wyo. Admin. Code 011.0002.2 §7(b)(i)). The Division\'s own link points to a Google Drive copy; the canonical SSTGB copy is https://www.streamlinedsalestax.org/docs/default-source/forms/exemption-certificateb926a7ab4a0d43e1ad4fe8eb19e79cbb.pdf?sfvrsn=857843d_5.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Wyoming sales/use tax license (vendor\'s license)',
        'number_name' => 'Wyoming sales tax license number',
        'format' => null,
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => false,
            'cite' => 'Wyo. Admin. Code 011.0002.2 §7(b)(i) (certificates \'shall be in a format as prescribed by the Streamlined Sales and Use Tax Agreement\'), https://www.law.cornell.edu/regulations/wyoming/011-2-Wyo-Code-R-SS-2-7; Wyoming is not listed on the MTC certificate rev. 10/14/22.',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'Excise Tax FAQs (\'Wyoming uses the Streamlined Sales Tax Agreement exemption certificate\'), https://excise-tax-div.wyo.gov/general-administrative/excise-tax-faqs; Wyo. Admin. Code 011.0002.2 §7(b)(i).',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'SSTGB Form F0003 instructions (purchaser not registered in the state may give a sales tax ID issued by any state; Wyoming also accepts a foreign tax ID).',
            'notes' => 'Applies to buyers not required to register in Wyoming. The Division\'s FAQ says unlicensed vendors cannot claim a resale exemption \'using Wyoming information\', so Wyoming sellers who make Wyoming sales need a Wyoming license.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'SSTGB Form F0003 Section 1 (unchecked single-purchase box makes a blanket certificate, effective while purchases are no more than 12 months apart).',
        ],
    ],
    'expiration' => [
        'label' => 'No fixed expiration',
        'summary' => 'No fixed expiration. Under the SST certificate rules, a blanket certificate stays in effect until the purchaser cancels it, as long as purchases are no more than 12 months apart.',
        'cite' => 'SSTGB Form F0003 instructions, Section 1',
    ],
    'good_faith' => [
        'summary' => 'A vendor is relieved of the tax if it obtains a fully completed SST certificate, or captures the same data elements, within 90 days of the sale. If not, it has 120 days after a Department request to obtain a certificate taken in good faith: the exemption was available on the sale date where the sale is sourced, could apply to the item, and is reasonable for the buyer\'s type of business.',
        'cite' => 'Wyo. Admin. Code 011.0002.2 §7(b)(ii)-(iii)',
    ],
    'misuse_penalty' => [
        'summary' => 'The purchaser is liable for the tax and interest, and possibly civil and criminal penalties, if not eligible for the exemption. Under Wyoming\'s sales tax enforcement statute, a deficiency due to fraud with intent to evade carries a 25% penalty plus interest, and violations without a specific penalty are misdemeanors.',
        'cite' => 'SSTGB Form F0003 purchaser warning; Wyo. Stat. §39-15-108(b)(ii) and (b)(vii)',
    ],
    'facts' => [
        [
            'text' => 'Non-taxable transactions, including sales for resale, must be shown separately from taxable charges on sales invoices.',
            'source_url' => 'https://www.law.cornell.edu/regulations/wyoming/011-2-Wyo-Code-R-SS-2-7',
        ],
        [
            'text' => 'Wyoming issues sales tax licenses to vendors. Licensed vendors claim the resale exemption by completing the SST exemption certificate for their suppliers.',
            'source_url' => 'https://excise-tax-div.wyo.gov/general-administrative/excise-tax-faqs',
        ],
        [
            'text' => 'The SST certificate form is free and is linked from the Division\'s Streamlined Sales Tax Project page along with Wyoming\'s exemption matrix.',
            'source_url' => 'https://excise-tax-div.wyo.gov/streamlined-sales-tax-project',
        ],
        [
            'text' => 'Sellers must use the SST governing board\'s standard format when exemptions are claimed electronically.',
            'source_url' => 'https://www.law.cornell.edu/regulations/wyoming/011-2-Wyo-Code-R-SS-2-7',
        ],
    ],
    'state_notes' => 'Wyoming buyers use the Streamlined Sales and Use Tax Agreement Certificate of Exemption, Form F0003. The Wyoming Department of Revenue\'s Excise Tax Division links it from its website for free. Wyoming rules require this format, so other certificates, such as the Multistate Tax Commission form, should not be used. Check reason G, Resale. Enter your Wyoming sales tax license number. If you are not required to be licensed in Wyoming, enter the sales tax number your home state issued. Fill in every field and sign the paper form. Give it to your supplier and keep a copy. Do not send it to the Department. Leave the single purchase box empty to make it a blanket certificate. It stays in effect while you buy from that supplier at least once every 12 months, or until you cancel it. Your supplier is protected if it gets a completed certificate within 90 days of the sale. If you are not entitled to the exemption, you owe the tax and interest. Fraud with intent to evade tax adds a 25% penalty under Wyoming Statute 39-15-108, and other violations are misdemeanors.',
    'sources' => [
        [
            'title' => 'Wyoming Excise Tax Division: Excise Tax FAQs',
            'url' => 'https://excise-tax-div.wyo.gov/general-administrative/excise-tax-faqs',
        ],
        [
            'title' => 'Wyoming Excise Tax Division: Streamlined Sales Tax Project',
            'url' => 'https://excise-tax-div.wyo.gov/streamlined-sales-tax-project',
        ],
        [
            'title' => 'Wyo. Admin. Code 011.0002.2 §7 Non-Taxable and Exempt Sales Transactions',
            'url' => 'https://www.law.cornell.edu/regulations/wyoming/011-2-Wyo-Code-R-SS-2-7',
        ],
        [
            'title' => 'Wyoming Statutes Title 39 (incl. §39-15-108)',
            'url' => 'https://wyoleg.gov/statutes/compress/title39.pdf',
        ],
        [
            'title' => 'SSTGB Form F0003 (Revised 12/21/2021)',
            'url' => 'https://www.streamlinedsalestax.org/docs/default-source/forms/exemption-certificateb926a7ab4a0d43e1ad4fe8eb19e79cbb.pdf?sfvrsn=857843d_5',
        ],
        [
            'title' => 'SSTGB Exemptions page',
            'url' => 'https://www.streamlinedsalestax.org/Shared-Pages/exemptions-',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/22)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
