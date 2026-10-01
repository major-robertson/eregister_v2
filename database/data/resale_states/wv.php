<?php

/*
 * West Virginia: West Virginia Tax Division (West Virginia Department of
 * Revenue), Form F0003. Researched 2026-10-01 from tax.wv.gov,
 * law.cornell.edu, streamlinedsalestax.org, mtc.gov. Generated once from the
 * EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'WV',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'West Virginia Tax Division (West Virginia Department of Revenue)',
        'short' => 'the Tax Division',
        'url' => 'https://tax.wv.gov/',
    ],
    'resale_page_url' => 'https://tax.wv.gov/business/salesandusetax/streamlinedsalesandusetax/pages/streamlinedsalesandusetax.aspx',
    'form' => [
        'number' => 'F0003',
        'title' => 'Streamlined Sales and Use Tax Agreement Certificate of Exemption',
        'pdf_url' => 'https://tax.wv.gov/Documents/sst/f0003.pdf',
        'prescribed' => true,
        'revision' => 'Revised 12/21/2021',
        'notes' => 'West Virginia uses the SST certificate (SSTGB Form F0003) as its exemption certificate, with West Virginia-specific instructions at https://tax.wv.gov/Documents/sst/f0003.instructions.pdf. The buyer checks reason G, Resale. 110 CSR 15-6.3.1 also allows an out-of-state exemption certificate that gives equivalent information.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'West Virginia Business Registration Certificate',
        'number_name' => 'Business Registration Certificate number',
        'format' => null,
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => false,
            'cite' => 'West Virginia is not among the states listed on the MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/22), and Tax Division guidance names only the SST certificate. 110 CSR 15-6.3.1 accepts an out-of-state exemption certificate with equivalent information, which might cover an MTC form in some cases (unconfirmed).',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'https://tax.wv.gov/business/salesandusetax/streamlinedsalesandusetax/pages/streamlinedsalesandusetax.aspx; West Virginia is a full SST member.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'W. Va. Code R. §110-15-6.3.1 (out-of-state exemption certificate with equivalent information); SSTGB Form F0003 instructions (purchaser not registered in the state may give an ID issued by any state).',
            'notes' => 'The Tax Division\'s F0003 instructions say the purchaser must hold a valid West Virginia Business Registration Certificate. Buyers not doing business in West Virginia rely on the SST rule allowing a home-state ID, or on an out-of-state certificate under 110 CSR 15-6.3.1.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'West Virginia F0003 instructions, General Instructions and purchaser item 2 (https://tax.wv.gov/Documents/sst/f0003.instructions.pdf).',
        ],
    ],
    'expiration' => [
        'label' => 'No fixed expiration',
        'summary' => 'A blanket certificate stays in force while the purchaser keeps making recurring purchases (at least one in any 12 consecutive months) or until the purchaser cancels it. Sellers keep certificates at least three years after the due date of the last return to which they relate, or while the period stays open.',
        'cite' => 'West Virginia F0003 instructions; W. Va. Code R. §110-15-6.4',
    ],
    'good_faith' => [
        'summary' => 'A properly executed certificate relieves the vendor of the burden of proof only if accepted in good faith. The seller must get the completed certificate at the time of sale or the sale is treated as taxable. A timely certificate with a material deficiency is acceptable if the deficiency is later corrected. Under the SST rules, a seller is not liable if it gets a fully completed certificate within 90 days of the sale and did not fraudulently fail to collect tax or solicit an unlawful claim.',
        'cite' => 'W. Va. Code R. §110-15-6.3; West Virginia F0003 instructions; SSTGB Form F0003 seller\'s instructions',
    ],
    'misuse_penalty' => [
        'summary' => 'Willfully issuing a false or fraudulent exemption certificate to evade tax is a misdemeanor. Misuse with intent to evade adds a penalty of 50% of the tax that would have been due, on top of other penalties, and the tax can be assessed at any time. The Business Registration Certificate may be suspended or revoked.',
        'cite' => 'West Virginia F0003 instructions (Instructions for Purchaser); W. Va. Code R. §110-15-6.3 (criminal sanctions under W. Va. Code §11-9-1 et seq.)',
    ],
    'facts' => [
        [
            'text' => 'Each sales slip or invoice under a blanket certificate must show the purchaser\'s name, address and Business Registration Certificate number.',
            'source_url' => 'https://tax.wv.gov/Documents/sst/f0003.instructions.pdf',
        ],
        [
            'text' => 'If items bought tax exempt are later used in a non-exempt way, the purchaser must pay sales or use tax on the purchase price.',
            'source_url' => 'https://tax.wv.gov/Documents/sst/f0003.instructions.pdf',
        ],
        [
            'text' => 'Doing business in West Virginia without a valid Business Registration Certificate can bring a penalty of up to $100 per day.',
            'source_url' => 'https://tax.wv.gov/Documents/sst/f0003.instructions.pdf',
        ],
        [
            'text' => 'A veterinarian\'s purchases of goods for resale are exempt and claimed on the F0003 certificate, as an example of the general resale rule.',
            'source_url' => 'https://tax.wv.gov/Documents/TSD/tsd368.pdf',
        ],
    ],
    'state_notes' => 'West Virginia buyers use the Streamlined Sales and Use Tax Certificate of Exemption, Form F0003, which the West Virginia Tax Division posts on its website. Check reason G, Resale. Enter your West Virginia Business Registration Certificate number and the state that issued it. If you are not registered in West Virginia, enter your sales tax number from your home state. Fill in every field. A paper certificate must be signed. Give it to your supplier, not to the Tax Division. If you do not check the single purchase box, it works as a blanket certificate. It stays in force while you buy from that supplier at least once every 12 months, or until you cancel it. Each invoice under a blanket certificate should show your name, address and registration number. The supplier keeps the certificate for at least three years. If you later use an item yourself, you owe sales or use tax on it. Willfully giving a false certificate is a misdemeanor. Misuse to evade tax also brings a penalty of 50% of the tax, and your Business Registration Certificate can be suspended or revoked.',
    'sources' => [
        [
            'title' => 'WV Tax Division: Streamlined Sales and Use Tax',
            'url' => 'https://tax.wv.gov/business/salesandusetax/streamlinedsalesandusetax/pages/streamlinedsalesandusetax.aspx',
        ],
        [
            'title' => 'WV F0003 Certificate of Exemption',
            'url' => 'https://tax.wv.gov/Documents/sst/f0003.pdf',
        ],
        [
            'title' => 'WV F0003 Instructions',
            'url' => 'https://tax.wv.gov/Documents/sst/f0003.instructions.pdf',
        ],
        [
            'title' => 'W. Va. Code R. §110-15-6',
            'url' => 'https://www.law.cornell.edu/regulations/west-virginia/W-Va-C-S-R-SS-110-15-6',
        ],
        [
            'title' => 'WV TSD-368 Sales and Use Tax for Veterinarians',
            'url' => 'https://tax.wv.gov/Documents/TSD/tsd368.pdf',
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
