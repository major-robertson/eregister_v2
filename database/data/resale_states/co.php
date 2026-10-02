<?php

/*
 * Colorado: Colorado Department of Revenue, Taxation Division, Form DR 5002.
 * Researched 2026-10-01 from tax.colorado.gov, colorado.gov, mtc.gov.
 * Generated once from the EREG-8 resale research; edit this file directly from
 * now on.
 */

return [
    'state' => 'CO',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Colorado Department of Revenue, Taxation Division',
        'short' => 'the Department of Revenue',
        'url' => 'https://tax.colorado.gov/',
    ],
    'resale_page_url' => 'https://tax.colorado.gov/DR5002',
    'form' => [
        'number' => 'DR 5002',
        'title' => 'Declaration of Wholesale or Entity Sales Tax Exemption',
        'pdf_url' => 'https://tax.colorado.gov/sites/tax/files/documents/DR5002_2023.pdf',
        'prescribed' => false,
        'revision' => 'DR 5002 (08/04/23)',
        'notes' => 'The Department\'s own form, but optional: its instructions say a seller may accept DR 5002 or keep the information in another format, including the MTC Uniform Sales & Use Tax Resale Certificate. One form may cover multiple purchases by the same purchaser claiming the same exemption. Not sent to the Department.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Colorado Sales Tax License (retail) or Colorado Wholesale License',
        'number_name' => 'Colorado account number',
        'format' => '8 digits (enter the 8-digit account number, not the 12-digit location ID)',
        'verify_url' => 'https://www.colorado.gov/revenueonline/',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'DR 5002 (08/04/23) instructions; Colorado Sales Tax Guide (https://tax.colorado.gov/sales-tax-guide); MTC Uniform Resale Certificate notes 5-6 (not for services bought for resale; may not be accepted by self-collecting home-rule cities); GIL 26-001 (a wholesaler with no state-issued sales tax license or exemption certificate may not use the MTC certificate).',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'Colorado is not a Streamlined Sales Tax member; DR 5002 and the Sales Tax Guide name only DR 5002, the MTC certificate, or the seller\'s own records. No Department page found accepting the SST certificate.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Colorado Sales Tax Guide: a retailer may inspect a license \'issued by the Department or the comparable tax administration agency of another state\' and may accept a completed DR 5002 or MTC certificate from an out-of-state purchaser; DR 5002 line 1 asks for the license number, state and expiration date.',
            'notes' => 'The seller must still verify that the license is current and valid at the time of sale and keep a copy.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'DR 5002 instructions: \'It may be applied to multiple purchases by the same purchaser claiming the same exemption.\'',
        ],
    ],
    'expiration' => [
        'label' => 'No fixed expiration',
        'summary' => 'DR 5002 has no fixed expiry and can cover repeated purchases, but the seller must confirm that the buyer\'s license is current and valid at the time of each sale and record its expiration date. Colorado sales tax and wholesale licenses expire on December 31 of odd-numbered years and must be renewed.',
        'cite' => 'DR 5002 (08/04/23) instructions; https://tax.colorado.gov/sales-tax-guide',
    ],
    'good_faith' => [
        'summary' => 'A seller generally has the burden of proving a sale was properly exempt. To document a wholesale sale, the seller must get and keep enough information to verify eligibility (DR 5002, the MTC certificate, or another format), verify the buyer\'s license is current (online in Revenue Online via \'Verify a License or Certificate\', or by inspecting and copying the license), and consider whether the goods would reasonably be used for the exempt purpose. If a doubt about eligibility cannot be resolved, the seller should collect the tax.',
        'cite' => '1 Code Colo. Regs. 201-1, Rule 39-26-105-3 (Documenting Exempt Sales), as referenced in MTC note 6; DR 5002 seller instructions; https://tax.colorado.gov/sales-tax-guide',
    ],
    'misuse_penalty' => [
        'summary' => 'The purchaser remains directly liable for reporting and paying the sales or use tax, plus interest and any applicable penalties, on any purchase that does not qualify for the exemption or is used in a way that does not qualify. The purchaser certifies that its purchases qualify. A specific statutory penalty amount for misuse was not confirmed.',
        'cite' => 'DR 5002 (08/04/23), Line 5 instructions',
    ],
    'facts' => [
        [
            'text' => 'GIL 26-001 (July 2026): a wholesaler with no state-issued sales tax license or exemption certificate may not use the MTC Uniform Resale Certificate to claim the wholesale exemption. It may apply for a Colorado wholesale license, or complete DR 5002 instead.',
            'source_url' => 'https://tax.colorado.gov/july-2026-tax-policy-updates',
        ],
        [
            'text' => 'GIL 26-002 (July 2026): wholesale sales are exempt from Colorado sales tax regardless of the purchaser\'s location, the shipping terms, and whether the goods are exported.',
            'source_url' => 'https://tax.colorado.gov/july-2026-tax-policy-updates',
        ],
        [
            'text' => 'The purchase-for-resale exemption covers items resold in an unaltered or unused state in the ordinary course of business; a buyer that uses or consumes the item cannot claim it. Ingredients and component parts sold to manufacturers are also treated as wholesale sales.',
            'source_url' => 'https://tax.colorado.gov/sites/tax/files/documents/DR5002_2023.pdf',
        ],
        [
            'text' => 'Colorado collects the state tax and the taxes of certain cities, counties and special districts. Self-collecting home-rule cities may not accept the MTC form, and sellers should contact those cities directly. Colorado does not allow the MTC certificate to claim resale of a taxable service.',
            'source_url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
        [
            'text' => 'Colorado sales tax licenses and wholesale licenses expire on December 31 of odd-numbered years. A business operating exclusively as a wholesaler may apply for a wholesale license.',
            'source_url' => 'https://tax.colorado.gov/sales-tax-guide',
        ],
    ],
    'state_notes' => 'In Colorado, the Colorado Department of Revenue\'s form is DR 5002, the Declaration of Wholesale or Entity Sales Tax Exemption. Suppliers may also accept the Multistate Tax Commission\'s Uniform Sales & Use Tax Resale Certificate. On DR 5002, enter your business\'s legal name and address. Then enter your sales tax license number, the issuing state and the expiration date. For a Colorado license, use the 8-digit account number, not the 12-digit location ID. A license from another state is acceptable. Mark \'Purchase for Resale\' and describe your ordinary course of business and what you sell. You, or a person authorized to act for your business, must sign. One form can cover repeated purchases from the same supplier. Your supplier must check that your license is current at each sale, and Colorado licenses expire on December 31 of odd-numbered years. If you have no state-issued license, you cannot use the MTC certificate in Colorado; use DR 5002 or get a Colorado wholesale license. Some home-rule cities collect their own tax and may not accept these forms. If a purchase does not qualify, you owe the tax plus interest and penalties.',
    'sources' => [
        [
            'title' => 'DR 5002 Declaration of Wholesale or Entity Sales Tax Exemption (08/04/23)',
            'url' => 'https://tax.colorado.gov/sites/tax/files/documents/DR5002_2023.pdf',
        ],
        [
            'title' => 'Colorado DOR: DR 5002 page',
            'url' => 'https://tax.colorado.gov/DR5002',
        ],
        [
            'title' => 'Colorado Sales Tax Guide',
            'url' => 'https://tax.colorado.gov/sales-tax-guide',
        ],
        [
            'title' => 'Colorado DOR: July 2026 Tax Policy Updates (GIL 26-001, GIL 26-002)',
            'url' => 'https://tax.colorado.gov/july-2026-tax-policy-updates',
        ],
        [
            'title' => 'Colorado Revenue Online',
            'url' => 'https://www.colorado.gov/revenueonline/',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
