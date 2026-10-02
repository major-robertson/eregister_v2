<?php

/*
 * New Hampshire: no general sales and use tax. The Department of Revenue
 * Administration issues no resale certificates, exemption certificates or
 * tax exempt numbers, so a New Hampshire buyer follows the rules of the
 * supplier's state. Researched 2026-10-02 from revenue.nh.gov and mtc.gov.
 * Display copy only: New Hampshire has no ResaleStateRule row and no
 * certificate generator.
 */

return [
    'state' => 'NH',
    'researched_on' => '2026-10-02',
    'no_sales_tax' => true,
    'no_sales_tax_note' => 'New Hampshire has no general sales and use tax, so you do not need a resale certificate to buy inventory from New Hampshire suppliers. Suppliers in states with a sales tax may still ask for one before they sell to you tax-free.',
    'agency' => [
        'name' => 'New Hampshire Department of Revenue Administration',
        'short' => 'the Department of Revenue Administration',
        'url' => 'https://www.revenue.nh.gov/',
    ],
    'resale_page_url' => 'https://www.revenue.nh.gov/licenses-certifications/resale-exempt-certificates',
    'form' => [
        'number' => null,
        'title' => null,
        'pdf_url' => null,
        'prescribed' => false,
        'revision' => null,
        'notes' => 'None. The Department does not issue Certificates for Resale or Tax Exemptions, or tax exempt numbers. Form DP-143 is only for resale under the Communications Services Tax.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => null,
        'number_name' => null,
        'format' => null,
        'verify_url' => null,
        'notes' => 'New Hampshire issues no sales tax number and no tax exempt number. If the supplier\'s state wants a registration number, ask whether it accepts one from another state or wants you to register there.',
    ],
    'accepts' => [
        'mtc' => [
            'value' => null,
            'cite' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022): New Hampshire is not on the state list.',
        ],
        'sst' => [
            'value' => null,
            'cite' => 'New Hampshire has no general sales and use tax, so there is no New Hampshire tax to exempt.',
        ],
        'out_of_state_registration' => [
            'value' => null,
            'cite' => 'Not applicable: New Hampshire has no general sales tax.',
        ],
        'blanket' => [
            'value' => null,
            'cite' => 'Not applicable: New Hampshire has no general sales tax.',
        ],
    ],
    'expiration' => null,
    'good_faith' => null,
    'misuse_penalty' => null,
    'suppliers' => [
        'The resale certificate the supplier\'s state accepts. That is usually the state\'s own form, or the MTC uniform certificate where that state allows it. There is no New Hampshire form.',
        'A registration number, if the supplier\'s state requires one. Some states accept a number from another state. Others want you to register with them first.',
        'If the supplier asks for a New Hampshire tax exempt number, refer it to the Department of Revenue Administration at (603) 230-5920, or print the Department\'s resale and exempt certificates page and show it to the supplier.',
    ],
    'local_taxes' => [
        [
            'name' => 'Meals and Rooms (Rentals) Tax',
            'text' => '8.5 percent on restaurant meals, hotel and other sleeping accommodations, and motor vehicle rentals, for taxable periods beginning October 1, 2021. The customer pays it and the operator sends it to the state each month.',
            'source_url' => 'https://www.revenue.nh.gov/taxes-glance/meals-rooms-rentals-tax',
        ],
        [
            'name' => 'Communications Services Tax',
            'text' => 'A separate tax under RSA 82-A. A business that already files it can apply for resale treatment with Form DP-143.',
            'source_url' => 'https://www.revenue.nh.gov/licenses-certifications/resale-exempt-certificates',
        ],
    ],
    'faq' => [
        'need_certificate' => 'Not to buy from New Hampshire suppliers. New Hampshire has no general sales and use tax, so there is no New Hampshire tax to exempt. You only need a resale certificate when a supplier in a state with a sales tax asks for one.',
        'what_to_give' => 'Give the supplier the resale certificate its own state accepts, with whatever registration number that state requires. New Hampshire has no form or number of its own. If the supplier insists on a New Hampshire tax exempt number, refer it to the Department of Revenue Administration.',
        'mtc' => 'Only where the supplier\'s state allows it. The MTC certificate does not list New Hampshire, and it asks for a registration number for the supplier\'s state. Some states accept a number from another state on it, and others do not, so ask the supplier first.',
        'charge_customers' => 'Not sales tax, because New Hampshire has none. Restaurants, hotels and vehicle rental businesses charge the 8.5 percent Meals and Rooms (Rentals) Tax. If you sell into states with a sales tax, those states may require you to register and collect their tax.',
    ],
    'facts' => [
        [
            'text' => 'The Department of Revenue Administration does not issue Certificates for Resale or Tax Exemptions, and it does not issue tax exempt numbers.',
            'source_url' => 'https://www.revenue.nh.gov/licenses-certifications/resale-exempt-certificates',
        ],
        [
            'text' => 'Form DP-143, the Application for Resale for the Communications Services Tax, is mailed to the Department\'s Taxpayer Services Division, PO Box 637, Concord, NH 03302-0637.',
            'source_url' => 'https://www.revenue.nh.gov/licenses-certifications/resale-exempt-certificates',
        ],
        [
            'text' => 'Meals and Rooms (Rentals) Tax is due on the 15th of each month from the operators who collect it.',
            'source_url' => 'https://www.revenue.nh.gov/taxes-glance/meals-rooms-rentals-tax',
        ],
        [
            'text' => 'Under RSA 78-A:4, a business must get a Meals and Rooms operator\'s license before it opens a hotel, offers sleeping accommodations, sells taxable meals or rents motor vehicles.',
            'source_url' => 'https://www.revenue.nh.gov/taxes-glance/meals-rooms-rentals-tax',
        ],
    ],
    'state_notes' => 'New Hampshire does not have a general sales and use tax. The New Hampshire Department of Revenue Administration does not issue resale certificates, tax exemption certificates or tax exempt numbers, so there is no New Hampshire form to give a supplier.

The question comes up when you buy inventory from a supplier in a state that does have a sales tax. That supplier has to charge its own state\'s tax unless it has a resale certificate it can accept, so you follow the rules of the supplier\'s state. Some states accept the Multistate Tax Commission\'s uniform certificate, or their own form with a registration number from another state. Others want you to register with them first. Ask each supplier which certificate its state accepts.

If a supplier refuses to sell to you because you have no New Hampshire tax exempt number, the Department says you can refer the supplier to it at (603) 230-5920. You can also print the Department\'s resale and exempt certificates page and show it as proof that New Hampshire does not issue these certificates. New Hampshire does tax some sales directly. The 8.5 percent Meals and Rooms (Rentals) Tax applies to restaurant meals, hotel rooms and motor vehicle rentals.',
    'sources' => [
        [
            'title' => 'NH DRA: Resale & Exempt Certificates',
            'url' => 'https://www.revenue.nh.gov/licenses-certifications/resale-exempt-certificates',
        ],
        [
            'title' => 'NH DRA: Meals & Rooms (Rentals) Tax',
            'url' => 'https://www.revenue.nh.gov/taxes-glance/meals-rooms-rentals-tax',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
