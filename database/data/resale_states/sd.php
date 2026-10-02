<?php

/*
 * South Dakota: South Dakota Department of Revenue, Form 2040. Researched
 * 2026-10-01 from dor.sd.gov, dorresources.sd.gov, sdlegislature.gov,
 * streamlinedsalestax.org. Generated once from the EREG-8 resale research;
 * edit this file directly from now on.
 */

return [
    'state' => 'SD',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'South Dakota Department of Revenue',
        'short' => 'the Department of Revenue',
        'url' => 'https://dor.sd.gov/',
    ],
    'resale_page_url' => 'https://dor.sd.gov/businesses/taxes/sales-use-tax/',
    'form' => [
        'number' => '2040',
        'title' => 'Streamlined Sales and Use Tax Certificate of Exemption',
        'pdf_url' => 'https://dorresources.sd.gov/f/2040',
        'prescribed' => true,
        'revision' => 'April 2022 (SD Form 2040); the underlying SSTGB Form F0003 is revised 12/21/2021',
        'notes' => 'South Dakota uses the Streamlined Sales Tax certificate as its exemption certificate (Form 2040, served as a SeamlessDocs fill-in form, not a static PDF). ARSD 64:06:01:08.01 also accepts the MTC uniform certificate or a department-approved substitute. There is no separate resale-only form; the buyer checks reason G, Resale.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'South Dakota sales tax license',
        'number_name' => 'South Dakota sales tax license number',
        'format' => '8 digits followed by a two-letter license type (e.g. ST); may be written with or without dashes. Licenses ending in UT (use tax) or ET (contractor\'s excise tax) cannot be used to buy for resale.',
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'ARSD 64:06:01:08.01 (acceptable forms include the Multistate Tax Commission uniform sales and use tax certificate); https://sdlegislature.gov/api/Rules/Rule/64:06.html?all=true. SD is listed on the MTC certificate rev. 10/14/22 (note 27).',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'ARSD 64:06:01:08.01; SD Form 2040 is the SST certificate; SD is a full SST member state (https://www.streamlinedsalestax.org/Shared-Pages/exemptions-).',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'SD Sales and Use Tax Guide, July 2026, p. 10 (https://dor.sd.gov/media/avyep2sr/2026-7_sales-use-tax-guide.pdf); SSTGB Form F0003 instructions, ID numbers for resale purchases.',
            'notes' => 'A purchaser not registered in South Dakota may give its sales tax ID from any state. A purchaser from a state that issues no sales tax permits may use its FEIN, driver\'s license or state ID number. SD also accepts a foreign (e.g. VAT) number, and lists \'Not Required\' as allowed when no ID exists.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'SD Sales and Use Tax Guide, July 2026, p. 10; SDCL 10-45-61; SSTGB Form F0003 Section 1.',
        ],
    ],
    'expiration' => [
        'label' => 'No expiration',
        'summary' => 'Exemption certificates do not expire unless the information on them changes. The Department recommends updating them every three to four years. A seller may rely on a certificate until the purchaser files a new one. Sellers keep certificates three years from the date filed.',
        'cite' => 'SDCL 10-45-61; SD Sales and Use Tax Guide, July 2026, p. 10',
    ],
    'good_faith' => [
        'summary' => 'A seller who holds a signed exemption certificate showing the purchaser\'s name, address and license number may rely on it and is not responsible for collecting tax on items generally described by it. Under the SST rules the seller is not liable if it gets a fully completed certificate at the time of sale (or within 90 days) and did not fraudulently fail to collect tax or solicit an unlawful claim. If the certificate is missing at audit, the seller has 120 days after the Department\'s request to obtain one in good faith.',
        'cite' => 'SDCL 10-45-61; SDCL 10-45-61.1; SSTGB Form F0003 seller\'s instructions',
    ],
    'misuse_penalty' => [
        'summary' => 'A purchaser who knowingly gives an exemption certificate to evade the tax and does not report it is guilty of a Class 1 misdemeanor. The Secretary of Revenue may also assess a penalty of up to 50% of the tax, in addition to the tax.',
        'cite' => 'SDCL 10-45-61',
    ],
    'facts' => [
        [
            'text' => 'Businesses whose South Dakota permit number contains UT (use tax) or ET (contractor\'s excise tax) cannot buy products or services for resale.',
            'source_url' => 'https://dor.sd.gov/media/avyep2sr/2026-7_sales-use-tax-guide.pdf',
        ],
        [
            'text' => 'Services can be bought for resale only if bought for a current customer, not used by the purchaser in any way, and delivered to the customer without change.',
            'source_url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
        [
            'text' => 'A business that buys an item for resale and later uses it must report and pay use tax on that item.',
            'source_url' => 'https://dor.sd.gov/media/avyep2sr/2026-7_sales-use-tax-guide.pdf',
        ],
        [
            'text' => 'The state sales tax rate is 4.2% (Sales and Use Tax Guide, July 2026), plus any municipal tax.',
            'source_url' => 'https://dor.sd.gov/media/avyep2sr/2026-7_sales-use-tax-guide.pdf',
        ],
        [
            'text' => 'Marketplace providers that meet the $100,000 remote seller threshold collect tax on behalf of their marketplace sellers.',
            'source_url' => 'https://dor.sd.gov/media/avyep2sr/2026-7_sales-use-tax-guide.pdf',
        ],
    ],
    'state_notes' => 'South Dakota buyers use the Streamlined Sales and Use Tax Certificate of Exemption, which the South Dakota Department of Revenue publishes as Form 2040. Check reason G, Resale. The Department also accepts the Multistate Tax Commission uniform certificate. Enter your South Dakota sales tax license number. A license ending in UT or ET cannot be used to buy for resale. If you are not registered in South Dakota, enter the sales tax number from your home state. If your state issues no sales tax permit, use your FEIN or driver\'s license number. You complete and sign the certificate and give it to the seller. Do not send it to the Department. You can give a single-purchase certificate or a blanket certificate for future purchases. Certificates do not expire unless your information changes. The Department recommends updating them every three to four years. The seller keeps the certificate for three years. Services can be bought for resale only if bought for a current customer and passed on unchanged. If you later use an item yourself, you must pay use tax on it. Knowingly using a certificate to evade tax is a Class 1 misdemeanor under SDCL 10-45-61. You may also owe a penalty of up to 50% of the tax.',
    'sources' => [
        [
            'title' => 'SD DOR Sales and Use Tax Guide, July 2026',
            'url' => 'https://dor.sd.gov/media/avyep2sr/2026-7_sales-use-tax-guide.pdf',
        ],
        [
            'title' => 'SD DOR Sales & Use Tax page',
            'url' => 'https://dor.sd.gov/businesses/taxes/sales-use-tax/',
        ],
        [
            'title' => 'SD DOR Form 2040 Streamlined Certificate of Exemption',
            'url' => 'https://dorresources.sd.gov/f/2040',
        ],
        [
            'title' => 'SDCL chapter 10-45 (10-45-61, 10-45-61.1)',
            'url' => 'https://sdlegislature.gov/api/Statutes/10-45.html',
        ],
        [
            'title' => 'ARSD 64:06 (64:06:01:08, 64:06:01:08.01)',
            'url' => 'https://sdlegislature.gov/api/Rules/Rule/64:06.html?all=true',
        ],
        [
            'title' => 'SSTGB Exemptions page',
            'url' => 'https://www.streamlinedsalestax.org/Shared-Pages/exemptions-',
        ],
        [
            'title' => 'SSTGB Form F0003 (Revised 12/21/2021)',
            'url' => 'https://www.streamlinedsalestax.org/docs/default-source/forms/exemption-certificateb926a7ab4a0d43e1ad4fe8eb19e79cbb.pdf?sfvrsn=857843d_5',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/22)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
