<?php

/*
 * Arkansas: Arkansas Department of Finance and Administration, Sales and Use
 * Tax Section, Form ST391. Researched 2026-10-01 from dfa.arkansas.gov,
 * codeofarrules.arkansas.gov, codes.findlaw.com, mtc.gov. Generated once from
 * the EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'AR',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Arkansas Department of Finance and Administration, Sales and Use Tax Section',
        'short' => 'DFA',
        'url' => 'https://www.dfa.arkansas.gov/',
    ],
    'resale_page_url' => 'https://www.dfa.arkansas.gov/excise-tax/sales-and-use-tax/sales-and-use-tax-forms/',
    'form' => [
        'number' => 'ST391',
        'title' => 'Exemption Certificate',
        'pdf_url' => 'https://www.dfa.arkansas.gov/wp-content/uploads/ExemptionCertificate_1.pdf',
        'prescribed' => true,
        'revision' => 'REV 09/10/2025',
        'notes' => 'Posted on DFA\'s forms page 09/15/2025. 26 CAR § 30-1134(b)(1) says the sale for resale exemption may be claimed with Form ST 391 or the Streamlined multistate certificate of exemption (SSTGB Form F0003). Rule (b)(1) also allows the purchaser to give its retail permit number or a written certification that the items are bought for resale.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Arkansas Sales/Use Tax Permit (retail permit)',
        'number_name' => 'Arkansas sales/use tax permit number',
        'format' => null,
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC Uniform Resale Certificate (rev. 10/14/2022) lists AR with no state note. Note: 26 CAR § 30-1134(b)(1) names only Form ST 391 and SSTGB Form F0003; DFA\'s forms page does not post the MTC form.',
        ],
        'sst' => [
            'value' => true,
            'cite' => '26 CAR § 30-1134(b)(1); DFA Sales and Use Tax Forms page lists \'SST Certificate of Exemption | F0003\'',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => '26 CAR § 30-1134(a)(2) (\'A seller may accept a valid retail permit or resale permit issued by another state\'); Form ST391 (nonresident purchaser holding a similar permit issued by another state)',
            'notes' => 'The ST391 has a line for a nonresident purchaser\'s home-state permit number and state.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Ark. Code Ann. § 26-52-517 (seller may accept a blanket exemption certificate from a purchaser with which it has a recurring business relationship)',
        ],
    ],
    'expiration' => [
        'label' => 'No fixed expiration',
        'summary' => 'No fixed expiration. A seller may accept a blanket certificate from a purchaser with a recurring business relationship, meaning no more than 12 months pass between sales, and does not need to renew it or update it while that relationship continues. If more than 12 months pass without a sale, a new certificate is needed.',
        'cite' => 'Ark. Code Ann. § 26-52-517',
    ],
    'good_faith' => [
        'summary' => 'A seller that follows DFA\'s exemption requirements is relieved of the tax even if the purchaser improperly claimed the exemption. Relief does not apply if the seller fraudulently fails to collect tax, solicits the purchaser to make an unlawful claim, or accepts an entity-based exemption that Arkansas does not allow. The seller has 90 days after the sale to obtain a completed certificate. If DFA later asks for proof, the seller has 120 days to prove the sale was not taxable or to get a good-faith certificate from the purchaser.',
        'cite' => 'Ark. Code Ann. § 26-52-517; 26 CAR § 30-1134(b)(5); Form ST391 \'Notice to sellers\'',
    ],
    'misuse_penalty' => [
        'summary' => 'If goods bought tax-free are not resold, the purchaser alone is liable for reporting and paying the tax. A retail permit holder that fails to tell the seller that a purchase is not for resale gives the Commissioner of Revenue grounds to cancel its retail permit.',
        'cite' => 'Ark. Code Ann. § 26-52-517; 26 CAR § 30-1134(b)(4)(B); Form ST391 certification',
    ],
    'facts' => [
        [
            'text' => 'Arkansas is a Streamlined Sales Tax member state, and DFA lists the SST Certificate of Exemption (F0003) on its sales and use tax forms page alongside Form ST391.',
            'source_url' => 'https://www.dfa.arkansas.gov/excise-tax/sales-and-use-tax/sales-and-use-tax-forms/',
        ],
        [
            'text' => 'Form ST391 was revised 09/10/2025. The header now cites only \'AR Code 26-52-517\'; the 2008 version cited \'GR-53 & AR Code 26-52-517(b)(1), (e), and (f)\', reflecting Arkansas\'s move of its tax rules into the Code of Arkansas Rules (26 CAR). The body text and seller notice are unchanged.',
            'source_url' => 'https://www.dfa.arkansas.gov/wp-content/uploads/ExemptionCertificate_1.pdf',
        ],
        [
            'text' => 'Instead of a certificate, a seller may accept the purchaser\'s retail permit number or a written certification that the items or services are bought for resale.',
            'source_url' => 'https://codeofarrules.arkansas.gov/Rules/Rule?levelType=section&titleID=26&chapterID=33&subChapterID=241&partID=971&subPartID=9369&sectionID=63043',
        ],
        [
            'text' => 'Commercial farmers use a separate Commercial Farm Exemption Certificate (ST-403) rather than the resale certificate.',
            'source_url' => 'https://www.dfa.arkansas.gov/excise-tax/sales-and-use-tax/sales-and-use-tax-forms/',
        ],
    ],
    'state_notes' => 'In Arkansas, use Form ST391, the Exemption Certificate from the Department of Finance and Administration (DFA). The current version is dated 09/10/2025. Arkansas also accepts the Streamlined Sales Tax Certificate of Exemption (Form F0003). On the ST391, enter your Arkansas sales/use tax permit number. If you are an out-of-state buyer, you can instead enter the state and number of your home-state permit. Name the seller, describe the merchandise you are buying, give the reason it is exempt (purchase for resale), and describe your business activity. Use your business name as it appears on your permit. Sign the form and add your title and the date. One certificate can cover all of your purchases from that seller. It stays valid as long as no more than 12 months pass between your purchases. The seller keeps it on file. A seller that follows DFA\'s rules is protected even if your claim turns out to be wrong. If you use items you bought tax-free instead of reselling them, you must report and pay the tax yourself. If you do not tell the seller a purchase is not for resale, DFA can cancel your permit.',
    'sources' => [
        [
            'title' => 'DFA Sales and Use Tax Forms',
            'url' => 'https://www.dfa.arkansas.gov/excise-tax/sales-and-use-tax/sales-and-use-tax-forms/',
        ],
        [
            'title' => 'Form ST391 Exemption Certificate, REV 09/10/2025',
            'url' => 'https://www.dfa.arkansas.gov/wp-content/uploads/ExemptionCertificate_1.pdf',
        ],
        [
            'title' => 'Form ST391, REV 01/01/2008 (superseded)',
            'url' => 'http://www.dfa.arkansas.gov/wp-content/uploads/ExemptionCertificate.pdf',
        ],
        [
            'title' => '26 CAR § 30-1134 Exemptions from tax - Sales for resale',
            'url' => 'https://codeofarrules.arkansas.gov/Rules/Rule?levelType=section&titleID=26&chapterID=33&subChapterID=241&partID=971&subPartID=9369&sectionID=63043',
        ],
        [
            'title' => 'Ark. Code Ann. § 26-52-517 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ar/title-26-taxation/ar-code-sect-26-52-517/',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
