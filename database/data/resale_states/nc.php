<?php

/*
 * North Carolina: North Carolina Department of Revenue, Form E-595E.
 * Researched 2026-10-01 from ncdor.gov, ncleg.gov, streamlinedsalestax.org.
 * Generated once from the EREG-8 resale research; edit this file directly from
 * now on.
 */

return [
    'state' => 'NC',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'North Carolina Department of Revenue',
        'short' => 'NCDOR',
        'url' => 'https://www.ncdor.gov/',
    ],
    'resale_page_url' => 'https://www.ncdor.gov/taxes-forms/sales-and-use-tax/sales-and-use-tax-forms-and-certificates/exemption-certificates',
    'form' => [
        'number' => 'E-595E',
        'title' => 'Streamlined Sales and Use Tax Agreement Certificate of Exemption',
        'pdf_url' => 'https://www.ncdor.gov/e595e-4-2022-webfillpdf/open',
        'prescribed' => true,
        'revision' => 'Web-Fill 4-2022',
        'notes' => 'NCDOR\'s version of the multistate SST certificate (SSTGB Form F0003), used for resale and other exemptions. NCDOR\'s exemption certificate page also lists the MTC Uniform Sales & Use Tax Certificate.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'North Carolina Certificate of Registration (sales and use tax)',
        'number_name' => 'Certificate of registration number (sales and use tax account ID)',
        'format' => null,
        'verify_url' => 'https://eservices.dor.nc.gov/salesdatabase/',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'NCDOR Exemption Certificates page lists the Uniform Sales & Use Tax Certificate - Multijurisdiction; MTC note 22: not valid if signed by a person such as a contractor who intends to use the property; subject to G.S. 105-164.28.',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'Form E-595E is the North Carolina SST certificate; North Carolina is an SST full member state.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Form E-595E (4-2022) instructions, ID Numbers for Resale Purchases (Including Drop Shipments): https://www.ncdor.gov/e595e-4-2022-webfillpdf/open',
            'notes' => 'A buyer not registered in North Carolina gives a sales tax ID issued by any state; a buyer not required to register anywhere may give its FEIN, another state business ID or a driver\'s license number. A buyer required to register in North Carolina must give its North Carolina number. Drop shippers may accept another state\'s number.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'G.S. 105-164.28; Form E-595E Section 1: unchecked single-purchase box makes it a blanket certificate.',
        ],
    ],
    'expiration' => [
        'label' => 'No fixed expiration',
        'summary' => 'A blanket certificate stays effective until the purchaser cancels it, as long as no more than 12 months pass between sales (a recurring business relationship). NCDOR may not require renewal while that relationship continues.',
        'cite' => 'G.S. 105-164.28; Form E-595E (4-2022) instructions, Section 1',
    ],
    'good_faith' => [
        'summary' => 'The seller is not liable for tax, interest or penalty if the buyer claims an exemption improperly, provided the fully completed certificate or data elements are obtained at the sale or within 90 days after it, the seller did not fraudulently fail to collect tax, and did not solicit improper claims. If NCDOR requests substantiation, the seller has 120 days to produce a certificate or other proof. The seller need not verify the buyer\'s ID number.',
        'cite' => 'G.S. 105-164.28 (https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_105/GS_105-164.28.html); Form E-595E seller\'s instructions',
    ],
    'misuse_penalty' => [
        'summary' => 'A purchaser who does not resell an item bought under a certificate owes the tax. NCDOR assesses a $250 penalty for misuse of an exemption certificate by a purchaser. The certificate warns of possible civil and criminal penalties.',
        'cite' => 'G.S. 105-236(a)(5a) (https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_105/GS_105-236.html); G.S. 105-164.28',
    ],
    'facts' => [
        [
            'text' => 'NCDOR publishes a public Registry of Sales and Use Tax Numbers listing names, account IDs and SST IDs of active accounts, plus a registry of direct pay permits and exemption certificate numbers.',
            'source_url' => 'https://www.ncdor.gov/taxes-forms/sales-and-use-tax/other-sales-and-use-tax-resources/sales-and-use-tax-number-registries',
        ],
        [
            'text' => 'A drop shipper may accept a resale ID number issued by another state, so one number may be used for several states.',
            'source_url' => 'https://www.ncdor.gov/e595e-4-2022-webfillpdf/open',
        ],
        [
            'text' => 'Qualifying farmers, conditional farmers and commercial loggers enter their NCDOR exemption numbers on the same Form E-595E.',
            'source_url' => 'https://www.ncdor.gov/taxes-forms/sales-and-use-tax/sale-and-purchase-exemptions/qualifying-farmer-or-conditional-farmer-exemption-certificate-number',
        ],
        [
            'text' => 'A signature is not required on E-595E when it is provided in electronic form.',
            'source_url' => 'https://www.ncdor.gov/e595e-4-2022-webfillpdf/open',
        ],
        [
            'text' => 'The $250 misuse penalty in G.S. 105-236(a)(5a) is written for misuse of an exemption certificate by a purchaser, not the seller that accepts it.',
            'source_url' => 'https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_105/GS_105-236.html',
        ],
    ],
    'state_notes' => 'North Carolina uses Form E-595E, the Streamlined Sales and Use Tax Agreement Certificate of Exemption, from the North Carolina Department of Revenue. It is the state\'s version of the multistate Streamlined form. Fill it out as the buyer, choose "Resale" as the reason, and give it to the seller. Do not send it to the Department. If you are registered in North Carolina, enter your certificate of registration number. If you are not registered here, you can enter a sales tax number from any state. Leave the single-purchase box unchecked to make it a blanket certificate. A blanket certificate stays valid until you cancel it, as long as you buy from that seller at least once every 12 months. Sign it, or send it electronically without a signature. The seller is protected if it gets the completed form within 90 days of the sale. The seller does not have to check your number, but the Department keeps a public registry of active sales tax accounts. The Multistate Tax Commission certificate is also accepted. If you do not resell what you bought tax-free, you owe the tax. The Department can also charge a $250 penalty for misusing a certificate.',
    'sources' => [
        [
            'title' => 'NCDOR Exemption Certificates',
            'url' => 'https://www.ncdor.gov/taxes-forms/sales-and-use-tax/sales-and-use-tax-forms-and-certificates/exemption-certificates',
        ],
        [
            'title' => 'Form E-595E Streamlined Sales and Use Tax Certificate of Exemption (Web-Fill 4-2022)',
            'url' => 'https://www.ncdor.gov/e595e-4-2022-webfillpdf/open',
        ],
        [
            'title' => 'Form E-595E page',
            'url' => 'https://www.ncdor.gov/taxes-forms/sales-and-use-tax/sales-and-use-tax-forms-and-certificates/exemption-certificates/form-e-595e-streamlined-sales-and-use-tax-certificate-exemption',
        ],
        [
            'title' => 'G.S. 105-164.28 Certificates of exemption',
            'url' => 'https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_105/GS_105-164.28.html',
        ],
        [
            'title' => 'G.S. 105-236 Penalties',
            'url' => 'https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_105/GS_105-236.html',
        ],
        [
            'title' => 'NCDOR Sales and Use Tax Number Registries',
            'url' => 'https://www.ncdor.gov/taxes-forms/sales-and-use-tax/other-sales-and-use-tax-resources/sales-and-use-tax-number-registries',
        ],
        [
            'title' => 'SSTGB Form F0003 Certificate of Exemption (Revised 12/21/2021)',
            'url' => 'https://www.streamlinedsalestax.org/docs/default-source/forms/exemption-certificateb926a7ab4a0d43e1ad4fe8eb19e79cbb.pdf?sfvrsn=857843d_5',
        ],
    ],
];
