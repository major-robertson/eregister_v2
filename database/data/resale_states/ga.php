<?php

/*
 * Georgia: Georgia Department of Revenue, Form ST-5. Researched 2026-10-01
 * from dor.georgia.gov, law.cornell.edu, law.justia.com,
 * streamlinedsalestax.org. Generated once from the EREG-8 resale research;
 * edit this file directly from now on.
 */

return [
    'state' => 'GA',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Georgia Department of Revenue',
        'short' => 'DOR',
        'url' => 'https://dor.georgia.gov/',
    ],
    'resale_page_url' => 'https://dor.georgia.gov/taxes/business-taxes/sales-use-tax/nontaxable-sales',
    'form' => [
        'number' => 'ST-5',
        'title' => 'Sales Tax Certificate of Exemption (Georgia Purchaser)',
        'pdf_url' => 'https://dor.georgia.gov/document/form/st-5-sales-tax-certificate-exemption/download',
        'prescribed' => true,
        'revision' => 'ST-5 (Rev. 07/11/22)',
        'notes' => 'Box 1 of ST-5 covers purchases of tangible personal property or services for resale only (O.C.G.A. § 48-8-30). Out-of-state dealers that only buy in Georgia and immediately remove the goods use Form ST-4, Sales Tax Certificate of Exemption - Out of State Purchaser (Rev. 08/2017), https://dor.georgia.gov/document/form/formst-4pdf/download. Ga. Comp. R. & Regs. r. 560-12-1-.08 lists the certificates.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Georgia Sales and Use Tax Registration',
        'number_name' => 'Georgia sales tax number',
        'format' => null,
        'verify_url' => 'https://gtc.dor.ga.gov/',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC Uniform Resale Certificate (rev. 10/14/2022) lists GA with note 9 (purchaser\'s home-state number accepted for drop shipments; seller relieved on a properly completed certificate taken in good faith). DOR\'s own pages name only its ST forms.',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'Georgia is a Streamlined Sales Tax member state; the SST Certificate of Exemption (F0003) is accepted by all member states, with Georgia requiring the seller to verify the purchaser\'s ID number (streamlinedsalestax.org).',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Form ST-4 (Rev. 08/2017); Ga. Comp. R. & Regs. r. 560-12-1-.08; MTC note 9 (Georgia)',
            'notes' => 'Two limited cases. (1) Form ST-4: a purchaser licensed and registered outside Georgia, with no sales, solicitation or services in Georgia, that removes the goods from Georgia immediately after purchase, gives its home-state/country tax ID. (2) Drop shipments: the purchaser\'s home-state number is accepted instead of a Georgia number when the purchaser is outside Georgia, has no Georgia nexus, and the goods are drop-shipped to its Georgia customer. A purchaser with other Georgia activity must register in Georgia.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'ST-5: certifies that \'all purchases made after this date\' qualify; DOR: most certificates of exemption do not expire.',
        ],
    ],
    'expiration' => [
        'label' => 'No expiration',
        'summary' => 'No expiration. DOR says most certificates of exemption do not expire (the GATE agricultural certificate is the exception). An ST-5 covers all purchases made after its date. The seller needs one properly completed certificate from each purchaser buying tax-free.',
        'cite' => 'https://dor.georgia.gov/taxes/business-taxes/sales-use-tax/nontaxable-sales ; Form ST-5 (Rev. 07/11/22)',
    ],
    'good_faith' => [
        'summary' => 'The seller bears the burden of proving a sale is not at retail unless it takes in good faith a properly completed certificate: fully completed (name, address, sales tax number, signature), in the right form, claiming an exemption available and reasonable for the item and the buyer\'s business. For resale, the seller is relieved if the buyer is engaged in selling tangible personal property, lists a sales tax number valid at the time of purchase, and the seller has no reason to believe the buyer will not resell. Sellers can check numbers with the Sales Tax ID Verification Tool.',
        'cite' => 'O.C.G.A. § 48-8-38; Ga. Comp. R. & Regs. r. 560-12-1-.08; https://dor.georgia.gov/taxes/business-taxes/sales-use-tax/nontaxable-sales',
    ],
    'misuse_penalty' => [
        'summary' => 'Property obtained under the certificate is subject to sales and use tax if the purchaser uses or consumes it other than as certified. The purchaser signs under penalties of perjury. Misuse of a certificate of exemption is sufficient cause for the Commissioner to revoke the certificate of the seller, the purchaser, or both. A specific monetary penalty for misuse was not confirmed from DOR sources.',
        'cite' => 'Form ST-5 (Rev. 07/11/22) certification; Ga. Comp. R. & Regs. r. 560-12-1-.08',
    ],
    'facts' => [
        [
            'text' => 'Resale purchases on Form ST-5 require a Georgia sales and use tax number. Tax-free treatment does not extend to anything the purchaser uses, including items the purchaser will donate.',
            'source_url' => 'https://dor.georgia.gov/document/form/st-5-sales-tax-certificate-exemption/download',
        ],
        [
            'text' => 'The resale box on Form ST-5 covers both tangible personal property and services purchased for resale, and \'purchase\' includes leases and rentals.',
            'source_url' => 'https://dor.georgia.gov/document/form/st-5-sales-tax-certificate-exemption/download',
        ],
        [
            'text' => 'The Sales Tax ID Verification Tool on the Georgia Tax Center verifies only Georgia sales tax numbers, not out-of-state sales tax numbers, FEINs or Social Security numbers.',
            'source_url' => 'https://dor.georgia.gov/taxes/sales-use-tax/sales-tax-id-verification-tool',
        ],
        [
            'text' => 'An out-of-state dealer buying in Georgia for resale in another state uses Form ST-4, certifying that it has no sales, solicitation or services in Georgia and will remove the goods from Georgia immediately after purchase.',
            'source_url' => 'https://dor.georgia.gov/st-4-certificate-exemption-out-state-purchaser',
        ],
        [
            'text' => 'The supplier must secure and keep one properly completed certificate of exemption from each purchaser making tax-free purchases.',
            'source_url' => 'https://dor.georgia.gov/document/form/st-5-sales-tax-certificate-exemption/download',
        ],
    ],
    'state_notes' => 'In Georgia, use Form ST-5, the Sales Tax Certificate of Exemption, from the Georgia Department of Revenue (DOR). Check box 1, purchases for resale only. Enter your business name, address and type of business, and your Georgia sales tax number. A sales tax number is required for resale purchases. Print your name and title and sign; you sign under penalties of perjury. The certificate covers all purchases from that supplier made after its date, and DOR says it does not expire. Georgia also accepts the Streamlined Sales Tax Certificate of Exemption. If your business is outside Georgia, has no activity in Georgia, and removes the goods from Georgia right after buying them, use Form ST-4 with your home-state tax ID instead. If you sell drop-shipped goods to Georgia customers and have no Georgia nexus, suppliers may accept your home-state number. Your supplier can check a Georgia number with DOR\'s Sales Tax ID Verification Tool. If you use or consume items you bought tax-free, you owe the tax. Misuse can lead DOR to revoke your certificate.',
    'sources' => [
        [
            'title' => 'Georgia DOR: Nontaxable Sales',
            'url' => 'https://dor.georgia.gov/taxes/business-taxes/sales-use-tax/nontaxable-sales',
        ],
        [
            'title' => 'Form ST-5 Sales Tax Certificate of Exemption (Rev. 07/11/22)',
            'url' => 'https://dor.georgia.gov/document/form/st-5-sales-tax-certificate-exemption/download',
        ],
        [
            'title' => 'Form ST-4 Certificate of Exemption - Out of State Purchaser (Rev. 08/2017)',
            'url' => 'https://dor.georgia.gov/document/form/formst-4pdf/download',
        ],
        [
            'title' => 'Georgia DOR: Sales Tax ID Verification Tool',
            'url' => 'https://dor.georgia.gov/taxes/sales-use-tax/sales-tax-id-verification-tool',
        ],
        [
            'title' => 'Ga. Comp. R. & Regs. r. 560-12-1-.08 Certificate of Exemption',
            'url' => 'https://www.law.cornell.edu/regulations/georgia/Ga-Comp-R-Regs-R-560-12-1-.08',
        ],
        [
            'title' => 'O.C.G.A. § 48-8-38 (Justia)',
            'url' => 'https://law.justia.com/codes/georgia/title-48/chapter-8/article-1/part-2/section-48-8-38/',
        ],
        [
            'title' => 'Streamlined Sales Tax Certificate of Exemption (F0003)',
            'url' => 'https://www.streamlinedsalestax.org/docs/default-source/forms/exemption-certificateb926a7ab4a0d43e1ad4fe8eb19e79cbb.pdf?sfvrsn=857843d_5',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
