<?php

/*
 * Vermont: Vermont Department of Taxes, Form S-3. Researched 2026-10-01 from
 * tax.vermont.gov, legislature.vermont.gov, streamlinedsalestax.org, mtc.gov.
 * Generated once from the EREG-8 resale research; edit this file directly from
 * now on.
 */

return [
    'state' => 'VT',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Vermont Department of Taxes',
        'short' => 'the Department of Taxes',
        'url' => 'https://tax.vermont.gov/',
    ],
    'resale_page_url' => 'https://tax.vermont.gov/content/form-s-3',
    'form' => [
        'number' => 'S-3',
        'title' => 'Vermont Sales Tax Exemption Certificate for Purchases for Resale, by Exempt Organizations, and by Direct Pay Permit',
        'pdf_url' => 'https://tax.vermont.gov/sites/tax/files/documents/S-3.pdf',
        'prescribed' => true,
        'revision' => 'Rev. 07/21',
        'notes' => 'Buyer checks \'For resale/wholesale\' and enters a Vermont Sales & Use Tax Account Number. Not for contractors. Vermont is a full SST member, so the SST certificate (F0003) is also accepted, and Vermont is listed on the MTC certificate with restrictions.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Vermont sales and use tax account (registration)',
        'number_name' => 'Vermont Sales & Use Tax Account Number',
        'format' => null,
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC Uniform Sales & Use Tax Resale Certificate rev. 10/14/22, note 30 (reseller must be registered to collect Vermont sales tax; goods only, not component parts of a service; not for contractors).',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'Vt. Reg. §1.9745-1(A)(3) (sellers use the SST governing board standard form for electronic exemptions); Vermont is a full SST member (https://www.streamlinedsalestax.org/Shared-Pages/exemptions-).',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Vt. Reg. §1.9701(5)-3 and §1.9745-1(A)(6) (drop shipper may accept a resale certificate regardless of whether the reseller is registered in Vermont); SSTGB Form F0003 instructions.',
            'notes' => 'Limited. Form S-3 asks for a Vermont account number, Vermont regulations require persons buying for resale in Vermont to register (Reg. §1.9707-1), and the MTC note says the reseller must be registered in Vermont. A non-Vermont number is clearly accepted for drop shipments and on the SST certificate when the buyer is not required to register in Vermont.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Form S-3 \'Multiple Purchase\' box and instructions; Vt. Reg. §1.9745-1(D)(3).',
        ],
    ],
    'expiration' => [
        'label' => 'No fixed expiration',
        'summary' => 'No fixed expiration. A blanket (multiple purchase) certificate stays valid while there is a recurring business relationship, meaning no more than 12 months between sales. The buyer must update the certificate when its information changes. The MTC note says blanket certificates should be updated at least every three years. Sellers keep certificates at least three years from the last sale covered.',
        'cite' => 'Vt. Reg. §1.9745-1(D)(2)-(3); Form S-3 instructions (Rev. 07/21); MTC certificate note 30',
    ],
    'good_faith' => [
        'summary' => 'A seller that accepts a certificate in good faith is not liable for the tax. Good faith at the time of sale requires a certificate with no entry the seller knows or should know is false, on a Department form or substantially identical language, signed, dated and complete, for property of a type ordinarily used for the stated purpose. The seller may obtain the certificate up to 90 days after the sale, or within 120 days after an audit request if taken in good faith. No relief for fraud or for soliciting false claims.',
        'cite' => '32 V.S.A. §9745(a); Vt. Reg. §1.9745-1(B)-(D); Form S-3 instructions',
    ],
    'misuse_penalty' => [
        'summary' => 'If the buyer\'s exemption claim was false, the Department collects the tax from the buyer, plus penalty and interest. Willfully filing a false certificate or statement is a misdemeanor punishable by a fine of up to $1,000, up to one year in prison, or both.',
        'cite' => '32 V.S.A. §9814a(c); Form S-3 instructions (Burden of Proof); 32 V.S.A. §9777',
    ],
    'facts' => [
        [
            'text' => 'Form S-3 does not apply to contractors. Vermont publishes separate certificates, such as S-3M for manufacturing and S-3F for agriculture.',
            'source_url' => 'https://tax.vermont.gov/sites/tax/files/documents/S-3.pdf',
        ],
        [
            'text' => 'Vermont allows the MTC certificate for resale of goods only, not for component parts of a service.',
            'source_url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
        [
            'text' => 'For purchases on a multiple purchase certificate, each sales slip or invoice must show the buyer\'s name and address so it can be linked to the certificate on file.',
            'source_url' => 'https://tax.vermont.gov/sites/tax/files/documents/S-3.pdf',
        ],
        [
            'text' => 'A drop shipper may claim the resale exemption based on its customer\'s certificate regardless of whether the customer is registered in Vermont.',
            'source_url' => 'https://tax.vermont.gov/sites/tax/files/documents/SU%20Regs%20Effective%201%201%2019.pdf',
        ],
        [
            'text' => 'Every person purchasing tangible personal property for resale is listed among those who must register for Vermont sales and use tax.',
            'source_url' => 'https://tax.vermont.gov/sites/tax/files/documents/SU%20Regs%20Effective%201%201%2019.pdf',
        ],
    ],
    'state_notes' => 'Vermont buyers use Form S-3, the Vermont Sales Tax Exemption Certificate, from the Vermont Department of Taxes. Check the box for resale/wholesale and enter your Vermont sales and use tax account number. Vermont expects resellers to be registered. Out-of-state buyers who are not required to register can use the Streamlined Sales Tax certificate with their home-state number, and drop shipments are allowed without Vermont registration. Describe the items you are buying. The buyer or an authorized agent signs and dates the form. Choose single purchase or multiple purchase. A multiple purchase certificate covers later orders while you buy at least once every 12 months. Update it when your information changes. Give the certificate to the seller, not to the Department. The seller keeps it for at least three years after the last sale it covers. The seller must take the certificate in good faith. Form S-3 cannot be used by contractors. If your claim is false, the Department collects the tax from you, with penalty and interest. Willfully filing a false certificate is a misdemeanor under 32 V.S.A. section 9814a, with a fine of up to $1,000, up to one year in prison, or both.',
    'sources' => [
        [
            'title' => 'Vermont Form S-3 (Rev. 07/21)',
            'url' => 'https://tax.vermont.gov/sites/tax/files/documents/S-3.pdf',
        ],
        [
            'title' => 'Vermont Department of Taxes: Form S-3 page',
            'url' => 'https://tax.vermont.gov/content/form-s-3',
        ],
        [
            'title' => 'Vermont Sales and Use Tax Regulations (effective 1/1/2019)',
            'url' => 'https://tax.vermont.gov/sites/tax/files/documents/SU%20Regs%20Effective%201%201%2019.pdf',
        ],
        [
            'title' => '32 V.S.A. §9745',
            'url' => 'https://legislature.vermont.gov/statutes/section/32/233/09745',
        ],
        [
            'title' => '32 V.S.A. §9814a',
            'url' => 'https://legislature.vermont.gov/statutes/section/32/233/09814a',
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
