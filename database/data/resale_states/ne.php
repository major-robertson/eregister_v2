<?php

/*
 * Nebraska: Nebraska Department of Revenue, Form 13. Researched 2026-10-01
 * from revenue.nebraska.gov, nebraskalegislature.gov. Generated once from the
 * EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'NE',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Nebraska Department of Revenue',
        'short' => 'DOR',
        'url' => 'https://revenue.nebraska.gov/',
    ],
    'resale_page_url' => 'https://revenue.nebraska.gov/about/information-guides/nebraska-sales-tax-exemptions',
    'form' => [
        'number' => '13',
        'title' => 'Nebraska Resale or Exempt Sale Certificate for Sales Tax Exemption',
        'pdf_url' => 'https://revenue.nebraska.gov/files/doc/tax-forms/f_13.pdf',
        'prescribed' => true,
        'revision' => '6-134-1970 Rev. 7-2022',
        'notes' => 'Section A is the resale certificate; Section B covers exempt purchases; Section C is for contractors. DOR also accepts the MTC uniform certificate and the SST certificate (Reg-1-013.11).',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Nebraska Sales Tax Permit',
        'number_name' => 'Nebraska Sales Tax ID Number',
        'format' => 'Begins with the prefix 01- (Form 13 prints "01-" before the blank); the FEIN must not be used',
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'Reg-1-013.11 (https://revenue.nebraska.gov/legal/regs/salestax/1-013.html); MTC certificate lists NE',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'Reg-1-013.11 recognizes certificates authorized by the Streamlined Sales and Use Tax Agreement; Nebraska is an SST full member state.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Form 13 instructions and Section A field "Foreign State Sales Tax Number": https://revenue.nebraska.gov/files/doc/tax-forms/f_13.pdf; Reg-1-013.03B(2)',
            'notes' => 'Out-of-state purchasers may give their home state sales tax number. Wholesalers and manufacturers need not give an ID number. A buyer with no permit must state why.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Reg-1-013.06; Form 13 checkbox "Blanket"',
        ],
    ],
    'expiration' => [
        'label' => 'Valid until revoked',
        'summary' => 'A blanket Form 13 is valid until the purchaser revokes it in writing. A blanket certificate is tied to a recurring business relationship, which exists when sales occur at least once every 12 months.',
        'cite' => 'Form 13 (Rev. 7-2022) Section A; Reg-1-013.06',
    ],
    'good_faith' => [
        'summary' => 'A retailer holding a properly completed resale certificate is relieved of tax, penalty and interest. The certificate must be fully completed and received before, with, or within 90 days after the sale. If obtained within 120 days after a DOR request for substantiation, it must also be accepted in good faith. Retailers who fraudulently fail to collect or solicit false claims stay liable. Sellers cannot accept incomplete certificates.',
        'cite' => 'Reg-1-013.03, 013.05, 013.08 (https://revenue.nebraska.gov/legal/regs/salestax/1-013.html); Form 13',
    ],
    'misuse_penalty' => [
        'summary' => 'A purchaser who gives a resale certificate for a purchase not for resale, lease or rental owes a penalty of $100 or ten times the tax, whichever is larger, for each instance. With a blanket certificate the penalty applies to each purchase. Fraudulently signing a certificate can also be a Class IV misdemeanor.',
        'cite' => 'Neb. Rev. Stat. 77-2705; Reg-1-013.09 and 013.10; Form 13 penalty statement',
    ],
    'facts' => [
        [
            'text' => 'A sale for resale includes buying property only to lease or rent it to others at fair market value, but not leases incidental to renting real estate.',
            'source_url' => 'https://revenue.nebraska.gov/legal/regs/salestax/1-013.html',
        ],
        [
            'text' => 'Sales the retailer must deliver outside Nebraska need no resale certificate if supported by bills of lading or out-of-state delivery records; if an out-of-state buyer takes delivery in Nebraska, tax applies unless a certificate is given.',
            'source_url' => 'https://revenue.nebraska.gov/legal/regs/salestax/1-013.html',
        ],
        [
            'text' => 'Contractors use Section C of Form 13: Option 1 and Option 3 contractors give their Nebraska sales or use tax ID; Option 2 contractors buying for exempt organizations attach a Purchasing Agent Appointment (Form 17).',
            'source_url' => 'https://revenue.nebraska.gov/files/doc/tax-forms/f_13.pdf',
        ],
        [
            'text' => 'Property bought for resale and then used for anything other than retention, demonstration or display becomes taxable to the purchaser when first used.',
            'source_url' => 'https://revenue.nebraska.gov/legal/regs/salestax/1-013.html',
        ],
        [
            'text' => 'DOR does not release sales tax permit numbers for private use and warns that websites selling resale certificates are likely scams.',
            'source_url' => 'https://revenue.nebraska.gov/businesses/sales-and-use-tax/beware-websites-selling-sales-tax-certificates',
        ],
    ],
    'state_notes' => 'Nebraska uses Form 13, the Nebraska Resale or Exempt Sale Certificate, from the Nebraska Department of Revenue. To buy for resale, check "Purchase for Resale" and complete Section A. Enter your Nebraska Sales Tax ID Number, which starts with 01-. If you are based in another state, you can enter your home state sales tax number instead. Do not use your federal EIN. Describe what you are buying and your type of business, then sign and date the form. Give it to the seller and keep a copy. Do not send it to the Department. You can mark it for a single purchase or as a blanket certificate. A blanket certificate stays valid until you revoke it in writing. Sellers cannot accept an incomplete certificate. The seller should have it within 90 days of the sale. The Department also accepts the Multistate Tax Commission uniform certificate and the Streamlined Sales Tax certificate. Misuse is costly. If you use Form 13 for something you do not resell, lease or rent, the penalty is $100 or ten times the tax, whichever is larger, for each purchase. Signing one fraudulently can also be a Class IV misdemeanor.',
    'sources' => [
        [
            'title' => 'Form 13 Nebraska Resale or Exempt Sale Certificate (Rev. 7-2022)',
            'url' => 'https://revenue.nebraska.gov/files/doc/tax-forms/f_13.pdf',
        ],
        [
            'title' => 'Reg-1-013 Sale for Resale - Resale Certificate',
            'url' => 'https://revenue.nebraska.gov/legal/regs/salestax/1-013.html',
        ],
        [
            'title' => 'Title 316 Chapter 1 Sales and Use Tax regulations',
            'url' => 'https://revenue.nebraska.gov/about/chapter-1-sales-and-use-tax',
        ],
        [
            'title' => 'Neb. Rev. Stat. 77-2705',
            'url' => 'https://nebraskalegislature.gov/laws/statutes.php?statute=77-2705',
        ],
        [
            'title' => 'Beware of Websites Selling Sales Tax Certificates',
            'url' => 'https://revenue.nebraska.gov/businesses/sales-and-use-tax/beware-websites-selling-sales-tax-certificates',
        ],
        [
            'title' => 'Nebraska Sales Tax Exemptions information guide',
            'url' => 'https://revenue.nebraska.gov/about/information-guides/nebraska-sales-tax-exemptions',
        ],
    ],
];
