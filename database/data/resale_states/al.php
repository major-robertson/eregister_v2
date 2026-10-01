<?php

/*
 * Alabama: Alabama Department of Revenue, No separate form: your Alabama sales
 * tax license is the resale certificate. Researched 2026-10-01 from
 * revenue.alabama.gov, law.onecle.com, law.cornell.edu, mtc.gov. Generated
 * once from the EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'AL',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Alabama Department of Revenue',
        'short' => 'ALDOR',
        'url' => 'https://www.revenue.alabama.gov/',
    ],
    'resale_page_url' => 'https://www.revenue.alabama.gov/faqs/where-do-i-get-a-copy-of-my-resale-certificate/',
    'form' => [
        'number' => null,
        'title' => 'Alabama Sales Tax License (ALDOR treats the license as the resale certificate); ALDOR also posts the MTC Uniform Sales & Use Tax Certificate - Multijurisdiction on its forms page',
        'pdf_url' => null,
        'prescribed' => false,
        'revision' => null,
        'notes' => 'ALDOR says: "In Alabama, a resale certificate is officially called a \'Sales Tax License\'." A licensed buyer prints the license from My Alabama Taxes (MAT) and gives it, or its number, to the vendor. ALDOR lists the MTC uniform certificate on its forms page (https://www.revenue.alabama.gov/forms/uniform-sales-use-tax-certificate-multijurisdiction-form/). Form STE-1 is not a resale certificate: it is a certificate of exemption for product-based exempt buyers who do not hold a sales tax license, and ALDOR will not issue an STE-1 to a sales tax license holder (Ala. Admin. Code r. 810-6-5-.02).',
        'label' => 'No separate form: your Alabama sales tax license is the resale certificate',
    ],
    'issuer_model' => 'state_issued',
    'registration' => [
        'name' => 'Alabama Sales Tax License (Tax Account License)',
        'number_name' => 'Alabama sales tax account number',
        'format' => null,
        'verify_url' => 'https://myalabamataxes.alabama.gov/_/',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'https://www.revenue.alabama.gov/forms/uniform-sales-use-tax-certificate-multijurisdiction-form/ ; MTC Uniform Resale Certificate (rev. 10/14/2022) lists AL with note 2: each retailer is responsible for determining the validity of a purchaser\'s claim for exemption.',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'Alabama is not a Streamlined Sales Tax member state and ALDOR posts no SST certificate; no ALDOR page found accepting the SST certificate.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Ala. Admin. Code r. 810-6-1-.144.03; https://www.revenue.alabama.gov/faqs/can-i-use-an-out-of-state-sales-tax-license-to-purchase-tax-free-in-alabama/ ; https://www.revenue.alabama.gov/faqs/what-guidance-is-there-for-vendors-drop-shipping-into-alabama-and-remote-sellers/',
            'notes' => 'Rule 810-6-1-.144.03 extends the tax-free wholesale purchase to retailers outside Alabama that hold the sales tax license required by their home state. ALDOR\'s drop-ship FAQ says the vendor should accept the home-state resale certificate when a remote reseller buys for resale to an Alabama customer; ALDOR does not issue sales tax licenses to remote businesses with no Alabama location.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'The resale document is the buyer\'s annual sales tax license, which covers all purchases for resale while it is valid (r. 810-6-1-.144.03); the MTC certificate ALDOR posts is itself a blanket certificate.',
        ],
    ],
    'expiration' => [
        'label' => 'License renewed every year',
        'summary' => 'The certificate is the sales tax license. Alabama sales tax and related tax account licenses must be renewed every year online in MAT between November 1 and December 31 for the next tax year, with no fee. A license that is not renewed may be cancelled and can no longer be used to buy tax-free for resale. ALDOR prescribes no separate expiry for a vendor\'s copy.',
        'cite' => 'https://www.revenue.alabama.gov/faqs/do-i-have-to-renew-my-alabama-tax-account-license/ ; https://www.revenue.alabama.gov/aldor-sales-and-other-tax-license-renewals-go-annual-online/',
    ],
    'good_faith' => [
        'summary' => 'A retailer who relies in good faith on a state sales tax exemption number authorized by ALDOR, with the claim made on a form provided or approved by ALDOR, is not liable for the tax if the number holder misuses it. ALDOR\'s note on the MTC certificate adds that each retailer is responsible for determining the validity of the purchaser\'s claim, so vendors should check the number in MAT (\'Verify account numbers\').',
        'cite' => 'Ala. Code § 40-23-120; MTC Uniform Resale Certificate note 2 (Alabama); https://www.revenue.alabama.gov/sales-use/e-filing-payments-assistance/',
    ],
    'misuse_penalty' => [
        'summary' => 'ALDOR may collect the sales tax on purchases made illegally with a state tax-exempt number from the person who used the number and anyone who benefited from the illegal use. A specific statutory penalty for misusing a sales tax license as a resale certificate was not confirmed; the $2,000-or-double-tax civil penalty in Ala. Code § 40-9-60(c) applies to certificates of exemption, not sales tax licenses.',
        'cite' => 'Ala. Code § 40-23-121; Ala. Admin. Code r. 810-6-5-.02.01 (certificate-of-exemption penalty, for comparison)',
    ],
    'facts' => [
        [
            'text' => 'Alabama treats a drop shipment as two separate sales. The vendor should accept the remote reseller\'s home-state resale certificate when goods are drop-shipped to an Alabama customer, and the remote reseller is responsible for any Alabama tax on its own sale.',
            'source_url' => 'https://www.revenue.alabama.gov/faqs/what-guidance-is-there-for-vendors-drop-shipping-into-alabama-and-remote-sellers/',
        ],
        [
            'text' => 'Vendors can check whether an Alabama-issued resale or exemption number is valid in My Alabama Taxes under Other Actions, Search, \'Verify account numbers\' (the Tax Account Status Check).',
            'source_url' => 'https://www.revenue.alabama.gov/sales-use/e-filing-payments-assistance/',
        ],
        [
            'text' => 'Form STE-1 (certificate of exemption) is only for buyers entitled to certain tax-free purchases who are not required to hold a sales tax license. ALDOR will not issue an STE-1 to a business that has a sales tax license.',
            'source_url' => 'https://www.law.cornell.edu/regulations/alabama/Ala-Admin-Code-r-810-6-5-.02',
        ],
        [
            'text' => 'A licensed buyer prints its license (the \'resale certificate\') from My Alabama Taxes: open the tax account and click \'Print tax account license\'.',
            'source_url' => 'https://www.revenue.alabama.gov/faqs/where-do-i-get-a-copy-of-my-resale-certificate/',
        ],
        [
            'text' => 'Alabama sales tax, sellers use tax and Simplified Sellers Use Tax licenses are renewed annually online, with no renewal fee; an unrenewed license may be cancelled and cannot be used for tax-free resale purchases.',
            'source_url' => 'https://www.revenue.alabama.gov/aldor-sales-and-other-tax-license-renewals-go-annual-online/',
        ],
    ],
    'state_notes' => 'Alabama has no state resale certificate form. The Alabama Department of Revenue (ALDOR) says the resale certificate is the buyer\'s Sales Tax License. To buy for resale tax-free, give your supplier a copy of your license, printed from My Alabama Taxes, or a certificate that shows your Alabama sales tax account number. ALDOR also posts the Multistate Tax Commission\'s Uniform Sales & Use Tax Certificate, which many suppliers accept. If you use it, enter your Alabama number next to AL, describe what you sell and what you are buying, and have an owner, partner, officer or other authorized person sign and date it. If your business is outside Alabama and has no Alabama location, ALDOR says a supplier should accept your home-state resale certificate for goods drop-shipped to your Alabama customer. Your license must be renewed online every year between November 1 and December 31. If it is not renewed, it can be cancelled and can no longer be used for tax-free purchases. Suppliers can check your number in My Alabama Taxes. If you use your number to buy items you do not resell, ALDOR can collect the tax from you and from anyone who benefited.',
    'sources' => [
        [
            'title' => 'ALDOR FAQ: Where do I get a copy of my resale certificate',
            'url' => 'https://www.revenue.alabama.gov/faqs/where-do-i-get-a-copy-of-my-resale-certificate/',
        ],
        [
            'title' => 'ALDOR FAQ: Can I use an out-of-state sales tax license to purchase tax-free in Alabama?',
            'url' => 'https://www.revenue.alabama.gov/faqs/can-i-use-an-out-of-state-sales-tax-license-to-purchase-tax-free-in-alabama/',
        ],
        [
            'title' => 'ALDOR FAQ: Drop-shipping into Alabama and remote sellers',
            'url' => 'https://www.revenue.alabama.gov/faqs/what-guidance-is-there-for-vendors-drop-shipping-into-alabama-and-remote-sellers/',
        ],
        [
            'title' => 'ALDOR forms: Uniform Sales & Use Tax Certificate - Multijurisdiction Form',
            'url' => 'https://www.revenue.alabama.gov/forms/uniform-sales-use-tax-certificate-multijurisdiction-form/',
        ],
        [
            'title' => 'ALDOR FAQ: Do I have to renew my Alabama Tax Account License?',
            'url' => 'https://www.revenue.alabama.gov/faqs/do-i-have-to-renew-my-alabama-tax-account-license/',
        ],
        [
            'title' => 'ALDOR: Sales and other tax license renewals go annual, online',
            'url' => 'https://www.revenue.alabama.gov/aldor-sales-and-other-tax-license-renewals-go-annual-online/',
        ],
        [
            'title' => 'ALDOR E-Filing, Payments and Assistance (Tax Account Status Check)',
            'url' => 'https://www.revenue.alabama.gov/sales-use/e-filing-payments-assistance/',
        ],
        [
            'title' => 'Ala. Code § 40-23-120 (onecle)',
            'url' => 'https://law.onecle.com/alabama/title-40/40-23-120.html',
        ],
        [
            'title' => 'Ala. Code § 40-23-121 (onecle)',
            'url' => 'https://law.onecle.com/alabama/title-40/40-23-121.html',
        ],
        [
            'title' => 'Ala. Admin. Code r. 810-6-5-.02 (Form STE-1)',
            'url' => 'https://www.law.cornell.edu/regulations/alabama/Ala-Admin-Code-r-810-6-5-.02',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
