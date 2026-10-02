<?php

/*
 * Minnesota: Minnesota Department of Revenue, Form ST3. Researched 2026-10-01
 * from revenue.state.mn.us, revisor.mn.gov, dev.revenue.state.mn.us, mtc.gov.
 * Generated once from the EREG-8 resale research; edit this file directly from
 * now on.
 */

return [
    'state' => 'MN',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Minnesota Department of Revenue',
        'short' => 'the Department of Revenue',
        'url' => 'https://www.revenue.state.mn.us/',
    ],
    'resale_page_url' => 'https://www.revenue.state.mn.us/guide/sales-tax-exemptions',
    'form' => [
        'number' => 'ST3',
        'title' => 'Certificate of Exemption',
        'pdf_url' => 'https://www.revenue.state.mn.us/sites/default/files/2026-06/st3-26.pdf',
        'prescribed' => true,
        'revision' => 'Rev. 6/26',
        'notes' => 'Multi-purpose exemption certificate; the buyer selects reason H, Resale. Minn. Stat. 297A.72 requires a certificate substantially in the commissioner\'s form. The SST Certificate of Exemption (F0003) and the MTC certificate (resale only) are also accepted.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Minnesota sales and use tax registration',
        'number_name' => 'Minnesota Tax ID Number',
        'format' => '7 digits',
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC certificate (rev. 10/14/2022) note 18: accepted for resale only, with a Minnesota business-type code; other exemptions require ST3 or the SST form.',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'Minnesota is an SST member; MTC note 18 directs non-resale exemptions to Form ST3 or SST Form F0003; Minn. Stat. 297A.665 and 297A.72 follow the SST rules.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Minn. Stat. 297A.72, subd. 2(3): https://www.revisor.mn.gov/statutes/cite/297A.72',
            'notes' => 'If the purchaser has no Minnesota tax ID, it gives another state\'s tax ID and that state\'s name; failing that, its FEIN, then a driver\'s license or state ID number.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Minn. Stat. 297A.72, subd. 3; Form ST3 (blanket unless the single-purchase box is checked)',
        ],
    ],
    'expiration' => [
        'label' => 'No expiration',
        'summary' => 'No expiration. ST3 is a blanket certificate unless marked single purchase, and remains in force as long as the purchaser keeps buying or until it cancels it. The purchaser must update a blanket certificate as needed to keep the required information accurate.',
        'cite' => 'Form ST3 (Rev. 6/26); Minn. Stat. 297A.72, subd. 3',
    ],
    'good_faith' => [
        'summary' => 'The seller is relieved of liability if it obtains a fully completed certificate (or the required data) at the time of sale or within 90 days after. Otherwise, within 120 days of a Department request it must obtain a certificate taken in good faith (an exemption that was available, could apply to the item, and is reasonable for the buyer\'s business) or prove the sale was exempt by other means. Relief does not apply to sellers who fraudulently fail to collect tax, solicit unlawful claims, or knew the information was materially false. A drop shipper may accept its customer\'s resale certificate even if the customer is not registered in Minnesota.',
        'cite' => 'Minn. Stat. 297A.665(b)-(d)',
    ],
    'misuse_penalty' => [
        'summary' => 'A person who uses an exemption certificate to buy items or services for purposes other than the exemption claimed, intending to evade sales tax, is subject to a $100 penalty for each transaction. The purchaser is also liable for the tax, interest and penalties on ineligible purchases.',
        'cite' => 'Minn. Stat. 289A.60, subd. 14; Form ST3 (Rev. 6/26) penalty statement and instructions',
    ],
    'facts' => [
        [
            'text' => 'Drop shipments: a seller drop-shipping into Minnesota may accept a resale certificate (or other evidence) from its customer regardless of whether the customer is registered in Minnesota.',
            'source_url' => 'https://www.revisor.mn.gov/statutes/cite/297A.665',
        ],
        [
            'text' => 'Liquor retailers cannot buy alcoholic beverages exempt for resale (Minn. Stat. 340A.505).',
            'source_url' => 'https://www.revenue.state.mn.us/sites/default/files/2026-06/st3-26.pdf',
        ],
        [
            'text' => 'Form ST3 was revised in June 2026 (Rev. 6/26), replacing Rev. 7/19; the changes are mainly wording in the instructions.',
            'source_url' => 'https://www.revenue.state.mn.us/sites/default/files/2026-06/st3-26.pdf',
        ],
        [
            'text' => 'Do not send ST3 to the Department; the seller keeps it and may have to produce it to verify the exemption. If incomplete, the seller must charge tax.',
            'source_url' => 'https://www.revenue.state.mn.us/sites/default/files/2026-06/st3-26.pdf',
        ],
        [
            'text' => 'The resale exemption covers items and taxable services bought for resale in the normal course of business, including off-road vehicles sold to another dealer for resale.',
            'source_url' => 'https://www.revenue.state.mn.us/guide/sales-tax-exemptions',
        ],
    ],
    'state_notes' => 'In Minnesota, buyers use Form ST3, Certificate of Exemption, from the Minnesota Department of Revenue. The current version is Rev. 6/26. Minnesota also accepts the Streamlined Sales Tax certificate and, for resale only, the MTC uniform certificate. On ST3, enter your business name and address. Give your 7-digit Minnesota Tax ID Number. If you do not have one, give your tax ID from another state and name that state. If you have neither, give your FEIN. Enter the seller\'s name and address. Choose your type of business and select reason H, Resale. An authorized person signs, prints a name and title, and dates the form. ST3 is a blanket certificate unless you check the single-purchase box. It stays in force as long as you keep buying, until you cancel it. Update it if your information changes. The seller keeps it; do not send it to the Department. The seller is protected if it has the completed certificate within 90 days of the sale. If you use the certificate to avoid tax on items you do not resell, you can be fined $100 for each transaction. You will also owe the tax and interest.',
    'sources' => [
        [
            'title' => 'Form ST3 Certificate of Exemption (Rev. 6/26)',
            'url' => 'https://www.revenue.state.mn.us/sites/default/files/2026-06/st3-26.pdf',
        ],
        [
            'title' => 'Sales Tax Exemptions guide',
            'url' => 'https://www.revenue.state.mn.us/guide/sales-tax-exemptions',
        ],
        [
            'title' => 'Minn. Stat. 297A.72',
            'url' => 'https://www.revisor.mn.gov/statutes/cite/297A.72',
        ],
        [
            'title' => 'Minn. Stat. 297A.665',
            'url' => 'https://www.revisor.mn.gov/statutes/cite/297A.665',
        ],
        [
            'title' => 'Minn. Stat. 289A.60',
            'url' => 'https://www.revisor.mn.gov/statutes/cite/289A.60',
        ],
        [
            'title' => 'Minnesota Tax ID Number (seven digits), DOR instructions',
            'url' => 'https://dev.revenue.state.mn.us/sites/default/files/2014-12/m8_inst_14.pdf',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
