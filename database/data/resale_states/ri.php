<?php

/*
 * Rhode Island: Rhode Island Department of Revenue, Division of Taxation,
 * Sales and Use Tax Resale Certificate. Researched 2026-10-01 from tax.ri.gov,
 * rules.sos.ri.gov, webserver.rilegislature.gov, mtc.gov. Generated once from
 * the EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'RI',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Rhode Island Department of Revenue, Division of Taxation',
        'short' => 'the Division of Taxation',
        'url' => 'https://tax.ri.gov/',
    ],
    'resale_page_url' => 'https://tax.ri.gov/forms/business-tax-forms/sales-excise-forms',
    'form' => [
        'number' => null,
        'title' => 'Sales and Use Tax Resale Certificate',
        'pdf_url' => 'https://tax.ri.gov/sites/g/files/xkgbur541/files/forms/1997/excise/resale.pdf',
        'prescribed' => true,
        'revision' => null,
        'notes' => 'The Division\'s form carries no form number or revision date (it is filed under 1997 on the Division\'s site). Businesses that sell at wholesale only use the separate Sales and Use Tax Wholesaler\'s-Resale Certificate: https://tax.ri.gov/sites/g/files/xkgbur541/files/forms/2021/Excise/Sales-%26-Use/Resale-Cert-%28Wholesaler%29.pdf. 280-RICR-20-70-41 also lets sellers accept the Streamlined Sales Tax Certificate of Exemption.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Rhode Island Permit to Make Sales at Retail (sales and use tax permit)',
        'number_name' => 'Permit to Make Sales at Retail number',
        'format' => null,
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC Uniform Resale Certificate (rev. 10-14-2022) lists RI, note 26: allowed only to claim resale when the item will be resold in the same form, not for any other exemption.',
        ],
        'sst' => [
            'value' => true,
            'cite' => '280-RICR-20-70-41 (effective 01/04/2022): sellers may accept the Streamlined Sales Tax Certificate of Exemption; the Division posts the SST form.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Division General FAQs (https://tax.ri.gov/guidance/information-and-faqs/general-faqs); SST Form F0003 instructions',
            'notes' => 'A buyer not required to hold a Rhode Island permit (for example, no sales in Rhode Island, or wholesale only) writes an appropriate notation on the Rhode Island certificate in place of a permit number. On the SST certificate an unregistered buyer may give another state\'s ID. The Division says retailers should not accept other states\' exemption certificates for exempt organizations.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => '280-RICR-20-70-41: certificates may cover a continuing line of purchases if plainly marked \'Blanket Certificate\'.',
        ],
    ],
    'expiration' => [
        'label' => 'No expiration',
        'summary' => 'No expiration is set for a resale certificate. A blanket certificate covers a continuing line of purchases for resale. Separately, Rhode Island sales tax permits expire every June 30 and must be renewed each year.',
        'cite' => '280-RICR-20-70-41; Division General FAQs',
    ],
    'good_faith' => [
        'summary' => 'Sellers who accept a properly completed resale certificate are relieved of liability for improperly claimed exemptions, absent fraud or collusion. Without a certificate, all receipts are presumed taxable and the burden of proof is on the seller.',
        'cite' => 'R.I. Gen. Laws 44-18-25; 280-RICR-20-70-41',
    ],
    'misuse_penalty' => [
        'summary' => 'The buyer signs the certificate under penalties of perjury. If the buyer uses the property for anything other than retention, demonstration or display while holding it for sale, that use is taxable to the buyer, measured by its cost.',
        'cite' => 'R.I. Gen. Laws 44-18-26; Rhode Island Resale Certificate certification',
    ],
    'facts' => [
        [
            'text' => 'Flea market vendors may not issue resale certificates; they give a copy of their Flea Market Vendor\'s Permit instead.',
            'source_url' => 'https://rules.sos.ri.gov/regulations/part/280-20-70-41',
        ],
        [
            'text' => 'Wholesalers and distributors must keep invoices showing the buyer\'s correct name and address and the purchase date to support exempt sales.',
            'source_url' => 'https://rules.sos.ri.gov/regulations/part/280-20-70-41',
        ],
        [
            'text' => 'Each business location needs its own permit, and a change in ownership or business structure requires a new permit.',
            'source_url' => 'https://tax.ri.gov/guidance/information-and-faqs/general-faqs',
        ],
        [
            'text' => 'A buyer that only rents out property while holding it for sale may elect to pay tax measured by its cost.',
            'source_url' => 'https://webserver.rilegislature.gov/Statutes/TITLE44/44-18/44-18-27.htm',
        ],
        [
            'text' => 'If goods bought for resale are commingled with similar goods, sales from the mix count first as sales of the resale goods.',
            'source_url' => 'https://webserver.rilegislature.gov/Statutes/TITLE44/44-18/44-18-28.htm',
        ],
    ],
    'state_notes' => 'Rhode Island uses the Sales and Use Tax Resale Certificate from the Rhode Island Division of Taxation. The form has no form number. You fill it out as the buyer and give it to the seller. Enter your Permit to Make Sales at Retail number and describe what you sell. Name the seller and describe what you will buy. Then add your name and address, sign and date it. You sign under penalty of perjury. If you are not required to hold a Rhode Island permit, for example because you make no sales here, write a note saying so in place of the number. If you sell only at wholesale, use the Wholesaler\'s-Resale Certificate instead. You can mark the certificate as a blanket certificate to cover ongoing purchases. It has no set expiration, though your permit must be renewed every year by June 30. Sellers may also accept the Streamlined Sales Tax certificate of exemption. A seller that accepts a properly completed certificate is protected unless there is fraud or collusion. If you use the goods yourself instead of holding them for sale, you owe tax on what you paid for them.',
    'sources' => [
        [
            'title' => 'Rhode Island Sales and Use Tax Resale Certificate',
            'url' => 'https://tax.ri.gov/sites/g/files/xkgbur541/files/forms/1997/excise/resale.pdf',
        ],
        [
            'title' => 'Sales and Use Tax Wholesaler\'s-Resale Certificate',
            'url' => 'https://tax.ri.gov/sites/g/files/xkgbur541/files/forms/2021/Excise/Sales-%26-Use/Resale-Cert-%28Wholesaler%29.pdf',
        ],
        [
            'title' => 'Sales & Excise Forms (Division of Taxation)',
            'url' => 'https://tax.ri.gov/forms/business-tax-forms/sales-excise-forms',
        ],
        [
            'title' => 'Division of Taxation General FAQs',
            'url' => 'https://tax.ri.gov/guidance/information-and-faqs/general-faqs',
        ],
        [
            'title' => '280-RICR-20-70-41 Resale, Certificates, Wholesalers, Distributors, and Replacement Parts',
            'url' => 'https://rules.sos.ri.gov/regulations/part/280-20-70-41',
        ],
        [
            'title' => 'R.I. Gen. Laws 44-18-25',
            'url' => 'https://webserver.rilegislature.gov/Statutes/TITLE44/44-18/44-18-25.htm',
        ],
        [
            'title' => 'R.I. Gen. Laws 44-18-26',
            'url' => 'https://webserver.rilegislature.gov/Statutes/TITLE44/44-18/44-18-26.htm',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10-14-2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
