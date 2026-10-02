<?php

/*
 * Oregon: no general sales or use tax. Oregon buyers give out-of-state
 * suppliers Form 150-800-002, the Oregon Business Registry Resale
 * Certificate, which the supplier may accept but does not have to.
 * Researched 2026-10-02 from oregon.gov, sos.oregon.gov, mtc.gov and
 * law.cornell.edu (WAC 458-20-102). Display copy only: Oregon has no
 * ResaleStateRule row and no certificate generator.
 */

return [
    'state' => 'OR',
    'researched_on' => '2026-10-02',
    'no_sales_tax' => true,
    'no_sales_tax_note' => 'Oregon has no general sales or use tax, so you do not need a resale certificate to buy inventory from Oregon suppliers. Suppliers in states with a sales tax may still ask for one before they sell to you tax-free.',
    'agency' => [
        'name' => 'Oregon Department of Revenue',
        'short' => 'the Department of Revenue',
        'url' => 'https://www.oregon.gov/dor/',
    ],
    'resale_page_url' => 'https://www.oregon.gov/dor/programs/businesses/pages/sales-tax.aspx',
    'form' => [
        'number' => '150-800-002',
        'title' => 'Oregon Business Registry Resale Certificate',
        'pdf_url' => 'https://www.oregon.gov/dor/forms/FormsPubs/or-business-registry-resale-cert_800-002.pdf',
        'prescribed' => false,
        'revision' => 'Rev. 03-17',
        'notes' => 'Given by an Oregon buyer to an out-of-state seller as evidence that the buyer is registered to do business in Oregon. The seller may accept it as a substitute resale certificate but is not required to. Not filed with the Department.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Oregon Business Registry (Oregon Secretary of State)',
        'number_name' => 'Oregon Business Registry number',
        'format' => null,
        'verify_url' => 'https://sos.oregon.gov/business/Pages/find.aspx',
        'notes' => 'Form 150-800-002 asks for it. A supplier can look it up with the Secretary of State\'s Find a Business search.',
    ],
    'accepts' => [
        'mtc' => [
            'value' => null,
            'cite' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022): Oregon is not on the state list. WAC 458-20-102(10) lets a Washington seller accept it from a nonresident buyer not required to register in Washington that buys for resale outside the state.',
        ],
        'sst' => [
            'value' => null,
            'cite' => 'Oregon has no general sales or use tax, so there is no Oregon tax to exempt.',
        ],
        'out_of_state_registration' => [
            'value' => null,
            'cite' => 'Not applicable: Oregon has no sales tax.',
        ],
        'blanket' => [
            'value' => null,
            'cite' => 'Not applicable: Oregon has no sales tax.',
        ],
    ],
    'expiration' => null,
    'good_faith' => null,
    'misuse_penalty' => null,
    'suppliers' => [
        'Form 150-800-002, the Oregon Business Registry Resale Certificate, filled in and signed. Give it to the supplier at the time of purchase. Do not file it with the Department of Revenue.',
        'Your Oregon Business Registry number, which goes on the form. The supplier can check it with the Secretary of State\'s Find a Business search.',
        'The supplier\'s own state form, if it asks for one. The supplier does not have to accept the Oregon certificate, and some states want their own form or more information.',
        'For a Washington supplier: the MTC uniform certificate or the Streamlined certificate. Washington lets its sellers take either from a nonresident buyer that buys for resale outside Washington and does not have to register there.',
    ],
    'local_taxes' => [
        [
            'name' => 'State lodging tax',
            'text' => '1.5 percent of the room charge for stays ending on or before December 31, 2026, and 2.75 percent for stays ending on or after January 1, 2027. Cities and counties can add their own lodging taxes.',
            'source_url' => 'https://www.oregon.gov/dor/programs/businesses/pages/lodging.aspx',
        ],
        [
            'name' => 'Vehicle use tax',
            'text' => 'Oregon charges a vehicle use tax on new vehicles bought outside the state.',
            'source_url' => 'https://www.oregon.gov/dor/programs/businesses/pages/sales-tax.aspx',
        ],
        [
            'name' => 'Corporate Activity Tax',
            'text' => 'A tax on the business, not a charge on the customer\'s bill. Businesses with $750,000 of Oregon commercial activity must register. Those with more than $1 million pay $250 plus 0.57 percent of taxable activity over $1 million.',
            'source_url' => 'https://www.oregon.gov/dor/programs/businesses/pages/corporate-activity-tax.aspx',
        ],
    ],
    'faq' => [
        'need_certificate' => 'Not to buy from Oregon suppliers. Oregon has no general sales or use tax, so there is no Oregon tax to exempt. You only need a resale certificate when a supplier in a state with a sales tax asks for one.',
        'what_to_give' => 'Give the supplier Form 150-800-002, the Oregon Business Registry Resale Certificate, with your Oregon Business Registry number. The supplier may accept it but does not have to. If its state wants a different form, use that one.',
        'mtc' => 'Only where the supplier\'s state allows it. The MTC certificate does not list Oregon, and it asks for a registration number for the supplier\'s state. Washington is one state that lets its sellers take the MTC certificate from nonresident buyers that buy for resale outside Washington.',
        'charge_customers' => 'Not Oregon sales tax, because there is none. If you sell to customers in states with a sales tax, those states may require you to register and collect their tax once you have nexus there.',
    ],
    'facts' => [
        [
            'text' => 'The Department of Revenue says Oregon does not have a sales tax exempt certificate. Form 150-800-002 is meant for suppliers in other states.',
            'source_url' => 'https://www.oregon.gov/dor/programs/businesses/pages/sales-tax.aspx',
        ],
        [
            'text' => 'Form 150-800-002 (Rev. 03-17) asks whether the business\'s only physical location is in Oregon and what kind of property it sells.',
            'source_url' => 'https://www.oregon.gov/dor/forms/FormsPubs/or-business-registry-resale-cert_800-002.pdf',
        ],
        [
            'text' => 'By signing the form, the buyer certifies it will not use the items other than for demonstration and display while holding them for sale, and that it may owe use tax if it does.',
            'source_url' => 'https://www.oregon.gov/dor/forms/FormsPubs/or-business-registry-resale-cert_800-002.pdf',
        ],
        [
            'text' => 'Washington rule WAC 458-20-102(10) lets a Washington seller accept the MTC uniform certificate or the Streamlined certificate, in place of a Washington reseller permit, from a nonresident buyer that buys for resale outside the state.',
            'source_url' => 'https://www.law.cornell.edu/regulations/washington/WAC-458-20-102',
        ],
    ],
    'state_notes' => 'Oregon has no general sales or use tax, so the Oregon Department of Revenue does not issue a sales tax permit or a sales tax exemption certificate. The question comes up when you buy inventory from a supplier in a state that does have a sales tax. That supplier has to charge its own state\'s tax unless it has a resale certificate it can accept.

The Department publishes Form 150-800-002, the Oregon Business Registry Resale Certificate, for this case. You fill in your Oregon Business Registry number, your business details and a description of what you will resell. Then you sign it and give it to the supplier when you buy. Do not file it with the Department. The supplier may accept the form, but it does not have to. Not all states accept it, and some ask for their own form or more information. The Department names Washington as one of them.

Washington\'s rule lets a seller take the Multistate Tax Commission\'s uniform certificate or the Streamlined Sales Tax certificate from a nonresident buyer that buys for resale outside Washington. If you use an item you bought for resale instead of reselling it, the Oregon form warns that you may owe use tax on it. Ask each supplier which form its state accepts before you place the order.',
    'sources' => [
        [
            'title' => 'Oregon DOR: Sales tax in Oregon',
            'url' => 'https://www.oregon.gov/dor/programs/businesses/pages/sales-tax.aspx',
        ],
        [
            'title' => 'Form 150-800-002, Oregon Business Registry Resale Certificate (Rev. 03-17)',
            'url' => 'https://www.oregon.gov/dor/forms/FormsPubs/or-business-registry-resale-cert_800-002.pdf',
        ],
        [
            'title' => 'Oregon Secretary of State: Find a Business',
            'url' => 'https://sos.oregon.gov/business/Pages/find.aspx',
        ],
        [
            'title' => 'Oregon DOR: Transient lodging tax',
            'url' => 'https://www.oregon.gov/dor/programs/businesses/pages/lodging.aspx',
        ],
        [
            'title' => 'Oregon DOR: Corporate Activity Tax',
            'url' => 'https://www.oregon.gov/dor/programs/businesses/pages/corporate-activity-tax.aspx',
        ],
        [
            'title' => 'WAC 458-20-102, Reseller permits (Washington)',
            'url' => 'https://www.law.cornell.edu/regulations/washington/WAC-458-20-102',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
