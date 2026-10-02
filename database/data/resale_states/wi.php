<?php

/*
 * Wisconsin: Wisconsin Department of Revenue, Form S-211. Researched
 * 2026-10-01 from revenue.wi.gov, mtc.gov. Generated once from the EREG-8
 * resale research; edit this file directly from now on.
 */

return [
    'state' => 'WI',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Wisconsin Department of Revenue',
        'short' => 'the Department of Revenue (DOR)',
        'url' => 'https://www.revenue.wi.gov/',
    ],
    'resale_page_url' => 'https://www.revenue.wi.gov/Pages/FAQS/pcs-sales.aspx',
    'form' => [
        'number' => 'S-211',
        'title' => 'Wisconsin Sales and Use Tax Exemption Certificate',
        'pdf_url' => 'https://www.revenue.wi.gov/DORForms/s-211f.pdf',
        'prescribed' => true,
        'revision' => 'R. 6-22',
        'notes' => 'Use of a DOR-designed certificate is not required by law; a substitute with all essential information in a DOR-approved form is acceptable (Wis. Admin. Code Tax 11.14(2)(b)). DOR forms: S-211, S-211E (electronic) and S-211-SST (Wisconsin version of the Streamlined certificate, https://www.revenue.wi.gov/DORForms/exemptcertf.pdf). The buyer checks Resale and enters its seller\'s permit or use tax certificate number.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Wisconsin seller\'s permit (or use tax registration certificate)',
        'number_name' => 'Seller\'s permit number / Wisconsin tax account number',
        'format' => null,
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'Wisconsin is listed on the MTC Uniform Sales & Use Tax Resale Certificate rev. 10/14/22, note 32 (resale exemption only, no other exemptions).',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'Wisconsin Publication 201, Part \'Records to Keep - Exempt Sales, Exemption Certificates\' (Form S-211-SST); Wisconsin is a full SST member.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Wis. Admin. Code Tax 11.14(6)(b)3.b (reproduced in Publication 201, Appendix D).',
            'notes' => 'A person registered as a seller in another state who makes no retail sales in Wisconsin may enter the name of that state and the permit number it issued. Drop shipments to Wisconsin customers are allowed on an out-of-state purchaser\'s resale certificate even without a Wisconsin permit. Wholesalers selling only for resale may write \'wholesale only\'.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Form S-211 \'Continuous\' box; Wis. Admin. Code Tax 11.14(5).',
        ],
    ],
    'expiration' => [
        'label' => 'No expiration',
        'summary' => 'Continuous (blanket) certificates do not expire and need not be renewed at any set interval. DOR says they should be renewed at reasonable intervals in case of a business change, a registration number change, or the business closing. A purchaser who wants to stop a continuous certificate must notify the seller in writing.',
        'cite' => 'Wis. Admin. Code Tax 11.14(5); Publication 201',
    ],
    'good_faith' => [
        'summary' => 'A seller is relieved of liability if it obtains a fully completed certificate before the sale or within 90 days after it, unless the seller fraudulently fails to collect tax or solicits an unlawful claim. Without a certificate, the seller may capture the required data elements within 90 days, or within 120 days after a DOR request obtain a certificate in good faith: the exemption was legal on the sale date, could apply to the item, and is reasonable for the buyer\'s business.',
        'cite' => 'Wis. Admin. Code Tax 11.14(3)-(4); Publication 201',
    ],
    'misuse_penalty' => [
        'summary' => 'A purchaser who uses a certificate in a way Wisconsin law does not allow, or gives the seller incorrect information, owes the tax and a penalty of $250 for each invoice involved. Knowingly giving a resale or exemption certificate to evade tax is also a ground for criminal charges.',
        'cite' => 'Form S-211 (R. 6-22) caution; Publication 201, \'Records to Keep\' and \'Criminal Charges\'',
    ],
    'facts' => [
        [
            'text' => 'A wholesaler that sells only to other sellers for resale may write \'wholesale only\' in place of a seller\'s permit number.',
            'source_url' => 'https://www.revenue.wi.gov/DOR%20Publications/pb201.pdf',
        ],
        [
            'text' => 'A seller may accept a resale certificate from an out-of-state purchaser and drop ship to a Wisconsin consumer even if the purchaser has no Wisconsin seller\'s permit.',
            'source_url' => 'https://www.revenue.wi.gov/DOR%20Publications/pb201.pdf',
        ],
        [
            'text' => 'Items bought for resale and then used, other than for retention, demonstration or display while held for sale, are taxable to the purchaser when first used.',
            'source_url' => 'https://www.revenue.wi.gov/DOR%20Publications/pb201.pdf',
        ],
        [
            'text' => 'Under a continuous certificate, a purchaser cannot cancel it for one order with a \'this time only\' purchase order; it must rescind in writing.',
            'source_url' => 'https://www.revenue.wi.gov/DOR%20Publications/pb201.pdf',
        ],
        [
            'text' => 'If the certificate is not fully completed, the seller must charge sales tax. Do not send it to DOR.',
            'source_url' => 'https://www.revenue.wi.gov/DORForms/s-211f.pdf',
        ],
    ],
    'state_notes' => 'Wisconsin buyers usually give suppliers Form S-211, the Wisconsin Sales and Use Tax Exemption Certificate, from the Wisconsin Department of Revenue. You may also use Form S-211-SST, the Streamlined certificate. The Multistate Tax Commission certificate is accepted for resale only. Check Resale and enter your Wisconsin seller\'s permit number. If you are registered in another state and make no retail sales in Wisconsin, enter that state and its permit number. Wholesalers who sell only for resale may write \'wholesale only\'. Fill in every field and sign a paper certificate. Choose single purchase or continuous. A continuous certificate does not expire, but renew it if your business or permit number changes. To stop it, tell the seller in writing. Give the certificate to the seller, not to the Department. If it is not fully completed, the seller must charge tax. A seller who gets a complete certificate within 90 days of the sale is not liable for the tax. If you later use an item yourself, you owe the tax. Using a certificate in a way the law does not allow, or giving false information, brings a $250 penalty for each invoice.',
    'sources' => [
        [
            'title' => 'Wisconsin Form S-211 (R. 6-22)',
            'url' => 'https://www.revenue.wi.gov/DORForms/s-211f.pdf',
        ],
        [
            'title' => 'Wisconsin Form S-211-SST',
            'url' => 'https://www.revenue.wi.gov/DORForms/exemptcertf.pdf',
        ],
        [
            'title' => 'Wisconsin Publication 201, Sales and Use Tax Information (incl. Appendix D, Tax 11.14)',
            'url' => 'https://www.revenue.wi.gov/DOR%20Publications/pb201.pdf',
        ],
        [
            'title' => 'Wisconsin DOR Sales and Use Tax FAQs',
            'url' => 'https://www.revenue.wi.gov/Pages/FAQS/pcs-sales.aspx',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/22)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
