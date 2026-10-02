<?php

/*
 * Montana: no general sales tax. Montana buyers give out-of-state suppliers
 * the Montana Business Registry Resale Certificate (no form number), which
 * the supplier may accept but does not have to. Researched 2026-10-02 from
 * revenue.mt.gov, revenuefiles.mt.gov, sosmt.gov and mtc.gov. Display copy
 * only: Montana has no ResaleStateRule row and no certificate generator.
 */

return [
    'state' => 'MT',
    'researched_on' => '2026-10-02',
    'no_sales_tax' => true,
    'no_sales_tax_note' => 'Montana has no general sales tax, so you do not need a resale certificate to buy inventory from Montana suppliers. Suppliers in states with a sales tax may still ask for one before they sell to you tax-free.',
    'agency' => [
        'name' => 'Montana Department of Revenue',
        'short' => 'the Department of Revenue',
        'url' => 'https://revenue.mt.gov/',
    ],
    'resale_page_url' => 'https://revenue.mt.gov/taxes/general-sales-tax',
    'form' => [
        'number' => null,
        'title' => 'Montana Business Registry Resale Certificate',
        'pdf_url' => 'https://revenuefiles.mt.gov/files/Forms/Montana_Business_Registry_Resale_Certificate_Form_Resale.pdf',
        'prescribed' => false,
        'revision' => null,
        'notes' => 'No form number. Given by a Montana buyer to an out-of-state seller as evidence that the buyer is registered to do business in Montana. The seller may accept it as a substitute resale certificate but is not required to, and is responsible for verifying the self-certification. Not filed with the Department.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Montana Secretary of State business registration',
        'number_name' => 'Montana business registration number',
        'format' => null,
        'verify_url' => 'https://biz.sosmt.gov/search/business',
        'notes' => 'The Montana certificate asks for it. A supplier can check it with the Secretary of State\'s Business Name Search.',
    ],
    'accepts' => [
        'mtc' => [
            'value' => null,
            'cite' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022): Montana is not on the state list.',
        ],
        'sst' => [
            'value' => null,
            'cite' => 'Montana has no general sales tax, so there is no Montana tax to exempt.',
        ],
        'out_of_state_registration' => [
            'value' => null,
            'cite' => 'Not applicable: Montana has no general sales tax.',
        ],
        'blanket' => [
            'value' => null,
            'cite' => 'Not applicable: Montana has no general sales tax.',
        ],
    ],
    'expiration' => null,
    'good_faith' => null,
    'misuse_penalty' => null,
    'suppliers' => [
        'The Montana Business Registry Resale Certificate, filled in and signed. It has no form number. Give it to the supplier when you buy. Do not file it with the Department of Revenue.',
        'Your Montana business registration number, which goes on the certificate. The supplier is responsible for verifying it and can use the Secretary of State\'s Business Name Search.',
        'The supplier\'s own state form, if it asks for one. The supplier does not have to accept the Montana certificate, and some states want their own form or more information.',
    ],
    'local_taxes' => [
        [
            'name' => 'Lodging facility sales and use tax',
            'text' => 'A 4 percent sales tax and a 4 percent use tax, 8 percent in all, on what guests pay for accommodations. The Department of Revenue administers it.',
            'source_url' => 'https://revenue.mt.gov/taxes/miscellaneous/lodging-facility',
        ],
        [
            'name' => 'Local resort tax',
            'text' => 'Up to 3 percent on lodging, restaurants, bars, ski resorts and luxuries in Big Sky, Cooke City, Craig, Gardiner, Red Lodge, St. Regis, Virginia City, West Yellowstone, Whitefish and Wolf Creek. Local voters approve it, and the Department of Revenue does not administer it.',
            'source_url' => 'https://revenue.mt.gov/taxes/miscellaneous/local-resort-tax',
        ],
    ],
    'faq' => [
        'need_certificate' => 'Not to buy from Montana suppliers. Montana has no general sales tax, so there is no Montana tax to exempt. You only need a resale certificate when a supplier in a state with a sales tax asks for one.',
        'what_to_give' => 'Give the supplier the Montana Business Registry Resale Certificate with your Montana business registration number. The supplier may accept it but does not have to. If its state wants a different form, use that one.',
        'mtc' => 'Only where the supplier\'s state allows it. The MTC certificate does not list Montana, and it asks for a registration number for the supplier\'s state. Ask the supplier whether its state will take the MTC certificate from a Montana business.',
        'charge_customers' => 'Not Montana sales tax, because there is none. The Department of Revenue says Montana businesses selling online to buyers in states that require online retailers to collect sales tax will need to collect and pay those taxes. Lodging and resort-area businesses have their own taxes.',
    ],
    'facts' => [
        [
            'text' => 'The Department of Revenue says the Wayfair decision does not affect Montanans buying online, because Montana has no general sales tax.',
            'source_url' => 'https://revenue.mt.gov/taxes/general-sales-tax',
        ],
        [
            'text' => 'You cannot claim another state\'s sales tax on your Montana income tax return. Montana residents shopping in another state should ask that state about its rules for nonresidents.',
            'source_url' => 'https://revenue.mt.gov/taxes/general-sales-tax',
        ],
        [
            'text' => 'The Montana certificate asks whether the business\'s only physical location is in Montana and what kind of tangible personal property it sells.',
            'source_url' => 'https://revenuefiles.mt.gov/files/Forms/Montana_Business_Registry_Resale_Certificate_Form_Resale.pdf',
        ],
        [
            'text' => 'The Montana Department of Commerce designates resort areas, and local voters approve each resort tax rate.',
            'source_url' => 'https://revenue.mt.gov/taxes/miscellaneous/local-resort-tax',
        ],
    ],
    'state_notes' => 'Montana does not have a general sales tax, and the Montana Department of Revenue says it has no sales tax exemption certificate. The question comes up when you buy inventory from a supplier in a state that does have a sales tax. That supplier has to charge its own state\'s tax unless it has a resale certificate it can accept.

The Department publishes the Montana Business Registry Resale Certificate for this case. It has no form number. You fill in your Montana business registration number, your business details and a description of what you will resell. Then you sign it and give it to the supplier when you buy. Do not file it with the Department. The supplier may accept the certificate as a substitute resale certificate, but it does not have to. Not all states accept it, and some ask for their own form or more information.

The certificate says sellers are responsible for verifying this self-certification, so expect the supplier to check your registration with the Secretary of State. If you use an item you bought for resale instead of reselling it, the form warns that you may owe use tax on it. Montana does tax some sales: lodging statewide, and lodging, meals, drinks and luxuries in resort communities.',
    'sources' => [
        [
            'title' => 'Montana DOR: Sales tax guidance for Montana businesses and residents',
            'url' => 'https://revenue.mt.gov/taxes/general-sales-tax',
        ],
        [
            'title' => 'Montana Business Registry Resale Certificate',
            'url' => 'https://revenuefiles.mt.gov/files/Forms/Montana_Business_Registry_Resale_Certificate_Form_Resale.pdf',
        ],
        [
            'title' => 'Montana Secretary of State: Business Name Search',
            'url' => 'https://biz.sosmt.gov/search/business',
        ],
        [
            'title' => 'Montana DOR: Lodging facility sales and use tax',
            'url' => 'https://revenue.mt.gov/taxes/miscellaneous/lodging-facility',
        ],
        [
            'title' => 'Montana DOR: Local resort tax',
            'url' => 'https://revenue.mt.gov/taxes/miscellaneous/local-resort-tax',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
