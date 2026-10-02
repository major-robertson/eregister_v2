<?php

/*
 * Delaware: no state or local sales tax; a gross receipts tax falls on the
 * seller. Delaware has no resale certificate for a Delaware buyer to give a
 * supplier, so the buyer follows the rules of the supplier's state. Form 373
 * covers the opposite case (out-of-state buyers picking up in Delaware).
 * Researched 2026-10-02 from revenue.delaware.gov, revenuefiles.delaware.gov
 * and mtc.gov. Display copy only: Delaware has no ResaleStateRule row and
 * no certificate generator.
 */

return [
    'state' => 'DE',
    'researched_on' => '2026-10-02',
    'no_sales_tax' => true,
    'no_sales_tax_note' => 'Delaware has no state or local sales tax, so you do not need a resale certificate to buy inventory from Delaware suppliers. Suppliers in states with a sales tax may still ask for one before they sell to you tax-free.',
    'agency' => [
        'name' => 'Delaware Division of Revenue',
        'short' => 'the Division of Revenue',
        'url' => 'https://revenue.delaware.gov/',
    ],
    'resale_page_url' => 'https://revenue.delaware.gov/business-tax-forms/doing-business-in-delaware/step-4-gross-receipts-taxes/',
    'form' => [
        'number' => null,
        'title' => null,
        'pdf_url' => null,
        'prescribed' => false,
        'revision' => null,
        'notes' => 'None for a Delaware buyer. Form 373, the Wholesale Exemption Certificate, documents gross receipts tax exempt sales to out-of-state purchasers who pick up goods in Delaware; it is not a resale certificate for Delaware buyers.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Delaware business license (Division of Revenue)',
        'number_name' => 'Delaware business license number',
        'format' => null,
        'verify_url' => null,
        'notes' => 'Delaware issues a business license, not a sales tax number. The Division of Revenue does not say whether other states accept the license number on a resale certificate, so ask the supplier\'s state.',
    ],
    'accepts' => [
        'mtc' => [
            'value' => null,
            'cite' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022): Delaware is not on the state list.',
        ],
        'sst' => [
            'value' => null,
            'cite' => 'Delaware has no state or local sales tax, so there is no Delaware sales tax to exempt.',
        ],
        'out_of_state_registration' => [
            'value' => null,
            'cite' => 'Not applicable: Delaware has no sales tax.',
        ],
        'blanket' => [
            'value' => null,
            'cite' => 'Not applicable: Delaware has no sales tax.',
        ],
    ],
    'expiration' => null,
    'good_faith' => null,
    'misuse_penalty' => null,
    'suppliers' => [
        'The resale certificate the supplier\'s state accepts. That is usually the state\'s own form, or the MTC uniform certificate where that state allows it. Delaware has no resale form for its own buyers.',
        'A registration number, if the supplier\'s state requires one. Delaware issues a business license, not a sales tax number. Ask the supplier\'s state whether it takes the license number or wants you to register there.',
        'Your federal Employer Identification Number, if the form asks for it. Minnesota\'s note on the MTC certificate, for example, accepts a federal EIN from a buyer with no Minnesota number.',
    ],
    'local_taxes' => [
        [
            'name' => 'Gross receipts tax',
            'text' => 'A tax on the seller of goods or services in Delaware, not a sales tax on the buyer. Rates run from 0.0945 percent to 1.9914 percent by business activity, and up to 2.4218 percent on petroleum products.',
            'source_url' => 'https://revenue.delaware.gov/business-tax-forms/doing-business-in-delaware/step-4-gross-receipts-taxes/',
        ],
        [
            'name' => 'Lodging tax',
            'text' => 'An 8 percent tax on room rents at hotels, motels and tourist homes. The guest pays it and the operator sends it to the state each month.',
            'source_url' => 'https://revenue.delaware.gov/services/current_bt/taxtips/hotmot.pdf',
        ],
        [
            'name' => 'Short-term rental lodging tax',
            'text' => 'A 4.5 percent tax on every occupancy of a short-term rental in Delaware.',
            'source_url' => 'https://revenue.delaware.gov/short-term-rental-faqs/',
        ],
    ],
    'faq' => [
        'need_certificate' => 'Not to buy from Delaware suppliers. Delaware has no state or local sales tax, so there is no Delaware sales tax to exempt. Delaware sellers pay gross receipts tax themselves. You only need a resale certificate when a supplier in a state with a sales tax asks for one.',
        'what_to_give' => 'Give the supplier the resale certificate its own state accepts, with whatever registration number that state requires. Delaware has no resale form for its own buyers. Ask the supplier\'s state whether your Delaware business license number is enough or whether you need to register there.',
        'mtc' => 'Only where the supplier\'s state allows it. The MTC certificate does not list Delaware, and it asks for a registration number for the supplier\'s state. A few states accept other numbers on it, such as Minnesota with a federal EIN, so ask the supplier first.',
        'charge_customers' => 'No. Delaware has no sales tax to add to your customers\' bills. Your business pays gross receipts tax on what it sells in Delaware instead. Lodging businesses charge lodging tax. If you sell into states with a sales tax, those states may require you to register and collect their tax.',
    ],
    'facts' => [
        [
            'text' => 'Any person or entity doing business in Delaware needs a Delaware business license from the Division of Revenue. That includes Delaware businesses that operate outside the state.',
            'source_url' => 'https://revenue.delaware.gov/frequently-asked-questions/business-licenses-faqs/',
        ],
        [
            'text' => 'Most Delaware business licenses run one year and expire December 31. After the first year, a business can choose a three-year license at three times the yearly fee.',
            'source_url' => 'https://revenue.delaware.gov/frequently-asked-questions/business-licenses-faqs/',
        ],
        [
            'text' => 'Gross receipts tax allows no deduction for the cost of goods sold, labor, interest, delivery costs or other expenses.',
            'source_url' => 'https://revenue.delaware.gov/business-tax-forms/doing-business-in-delaware/step-4-gross-receipts-taxes/',
        ],
        [
            'text' => 'Form 373, the Wholesale Exemption Certificate, documents sales to out-of-state buyers who pick up goods in Delaware for delivery and use outside the state. Those sales are exempt from the wholesale gross receipts tax.',
            'source_url' => 'https://revenuefiles.delaware.gov/docs/373-1.pdf',
        ],
    ],
    'state_notes' => 'Delaware does not impose a state or local sales tax. Instead, the Delaware Division of Revenue collects a gross receipts tax from the seller of goods or services in the state. Delaware has no resale certificate of its own for a Delaware buyer to give a supplier.

The question comes up when you buy inventory from a supplier in a state that does have a sales tax. That supplier has to charge its own state\'s tax unless it has a resale certificate it can accept, so you follow the rules of the supplier\'s state. Some states accept the Multistate Tax Commission\'s uniform certificate, or their own form with a number from another state. Others want you to register with them first. Ask each supplier which certificate its state accepts.

Every business that operates in Delaware needs a Delaware business license, which most businesses renew by December 31 each year. The Division of Revenue does not say whether other states accept that license number on a resale certificate, so check with the supplier\'s state. Delaware\'s Form 373, the Wholesale Exemption Certificate, is for the opposite case: buyers from other states who pick up goods from a Delaware wholesaler.',
    'sources' => [
        [
            'title' => 'Delaware Division of Revenue: Gross receipts taxes',
            'url' => 'https://revenue.delaware.gov/business-tax-forms/doing-business-in-delaware/step-4-gross-receipts-taxes/',
        ],
        [
            'title' => 'Delaware Division of Revenue: Business licenses FAQs',
            'url' => 'https://revenue.delaware.gov/frequently-asked-questions/business-licenses-faqs/',
        ],
        [
            'title' => 'Form 373, Wholesale Exemption Certificate',
            'url' => 'https://revenuefiles.delaware.gov/docs/373-1.pdf',
        ],
        [
            'title' => 'Delaware Division of Revenue: Tax tips for hotels, motels and tourist homes',
            'url' => 'https://revenue.delaware.gov/services/current_bt/taxtips/hotmot.pdf',
        ],
        [
            'title' => 'Delaware Division of Revenue: Short-term rental FAQs',
            'url' => 'https://revenue.delaware.gov/short-term-rental-faqs/',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
