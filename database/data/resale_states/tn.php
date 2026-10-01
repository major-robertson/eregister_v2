<?php

/*
 * Tennessee: Tennessee Department of Revenue, Tennessee Sales and Use Tax
 * Blanket Certificate of Resale (issued by the Department through TNTAP).
 * Researched 2026-10-01 from revenue.support.tn.gov, tn.gov, law.cornell.edu,
 * law.justia.com. Generated once from the EREG-8 resale research; edit this
 * file directly from now on.
 */

return [
    'state' => 'TN',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Tennessee Department of Revenue',
        'short' => 'the Department of Revenue',
        'url' => 'https://www.tn.gov/revenue.html',
    ],
    'resale_page_url' => 'https://revenue.support.tn.gov/hc/en-us/articles/360058174992-SUT-31-Sale-for-Resale-Resale-Certificate-Overview',
    'form' => [
        'number' => null,
        'title' => 'Tennessee Sales and Use Tax Blanket Certificate of Resale (issued by the Department through TNTAP)',
        'pdf_url' => null,
        'prescribed' => true,
        'revision' => null,
        'notes' => 'Tennessee-registered dealers do not fill in a resale form. The Department automatically issues a Blanket Certificate of Resale for each registered location, printed from TNTAP (More... > View Letters). Accepted alternatives: the Streamlined Sales Tax Certificate of Exemption (posted by the Department at https://www.tn.gov/content/dam/tn/revenue/documents/forms/sales/sst_exemptioncertificate.pdf), the MTC Uniform Sales and Use Tax Certificate - Multijurisdiction, and, for out-of-state dealers only, another state\'s resale certificate. The old purchaser-completed form RV-F1300701 is no longer posted (tn.gov/.../forms/sales/f1300701.pdf returns 404).',
    ],
    'issuer_model' => 'state_issued',
    'registration' => [
        'name' => 'Tennessee sales and use tax certificate of registration',
        'number_name' => 'Tennessee sales and use tax account number / location ID',
        'format' => 'Each location has a 10-digit location ID, shown on the resale certificate; sellers verify by location ID, not the SLC account number.',
        'verify_url' => 'https://tntap.tn.gov/eservices/_/',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'Tennessee Sales and Use Tax Manual, August 2026, ch. \'Resale Exemption Certificates\' (pp. 230-231): \'The SST Certificate of Exemption and the MTC Uniform Sales and Use Tax Certificate - Multijurisdiction are the only two multijurisdictional certificates accepted by Tennessee.\' https://www.tn.gov/content/dam/tn/revenue/documents/tax_manuals/august-2026/sales-use-tax-manual.pdf',
        ],
        'sst' => [
            'value' => true,
            'cite' => 'Tennessee Sales and Use Tax Manual, August 2026, pp. 230-231; SUT-31 (https://revenue.support.tn.gov/hc/en-us/articles/360058174992). Tennessee is an SST associate member.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'SUT-33 Out-of-State Resale Certificates (https://revenue.support.tn.gov/hc/en-us/articles/360058623991); Tenn. Comp. R. & Regs. 1320-05-01-.29(2) and 1320-05-01-.68; Important Notice 22-01.',
            'notes' => 'Since the repeal of Rule 96 effective January 10, 2022, an out-of-state dealer may give another state\'s resale certificate, or a fully completed SST certificate with the other state\'s sales tax ID, including for drop shipments into Tennessee. Dealers whose home state has no sales tax may use an SST certificate with another state tax ID or FEIN. Foreign dealers use the SST certificate with a home-country tax ID. A driver\'s license number is not accepted.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Tenn. Comp. R. & Regs. 1320-05-01-.68(2) (registered dealer need not execute certificates for individual purchases); Department-issued certificate is a Blanket Certificate of Resale.',
        ],
    ],
    'expiration' => [
        'label' => 'No expiration',
        'summary' => 'Department-issued resale certificates do not expire, but a certificate is no longer valid once the location closes. Sellers keep certificates for three years plus the current year.',
        'cite' => 'TDOR, Certificate of Resale (updated March 2021), https://www.tn.gov/content/dam/tn/revenue/documents/taxpayer_education/sales/s&u_resale_certificates.pdf',
    ],
    'good_faith' => [
        'summary' => 'Absent fraud or illegal solicitation, a seller that obtains a fully completed certificate at the time of sale or within 90 days after is not liable if the purchaser improperly claimed the exemption. After 90 days, the seller is protected only if the certificate was taken in good faith: the exemption was available on the sale date, could apply to the item, and is reasonable for the purchaser\'s business. Sales for resale without a fully completed certificate are treated as retail sales. The older \'ordinary care\' standard was repealed effective January 1, 2008.',
        'cite' => 'Tenn. Code Ann. §67-6-409; Tenn. Comp. R. & Regs. 1320-05-01-.78; Tennessee Sales and Use Tax Manual, August 2026, \'Relief of Liability\'',
    ],
    'misuse_penalty' => [
        'summary' => 'Using a resale certificate to buy items for the business\'s own use is grounds for the Commissioner to revoke the dealer\'s registration, and misuse to avoid tax is a misdemeanor (Class C under Tenn. Code Ann. §67-6-607). Items withdrawn from inventory must be reported and tax paid. Letting a new owner buy tax free on your certificate is also a misdemeanor.',
        'cite' => 'Tenn. Comp. R. & Regs. 1320-05-01-.68(3); Tenn. Code Ann. §67-6-607; Tennessee Sales and Use Tax Manual, August 2026, n. 751',
    ],
    'facts' => [
        [
            'text' => 'Resale certificates are issued by location. Each business location needs its own certificate.',
            'source_url' => 'https://www.tn.gov/content/dam/tn/revenue/documents/taxpayer_education/sales/s&u_resale_certificates.pdf',
        ],
        [
            'text' => 'Effective January 10, 2022, Rule 96 was repealed. A Tennessee supplier may now drop ship to an out-of-state dealer\'s Tennessee customer without tax if it gets the dealer\'s other-state resale certificate or a completed SST certificate (Important Notice 22-01).',
            'source_url' => 'https://revenue.support.tn.gov/hc/en-us/articles/360058175672-SUT-35-Sale-for-Resale-Drop-Shipments-to-Tennessee-Customers',
        ],
        [
            'text' => 'The MTC and SST multistate certificates may be used in Tennessee for sales for resale only, not for other exemptions.',
            'source_url' => 'https://www.tn.gov/content/dam/tn/revenue/documents/tax_manuals/august-2026/sales-use-tax-manual.pdf',
        ],
        [
            'text' => 'Merchandise taken from inventory for promotions, gifts or personal use must be reported on the sales tax return and the tax paid.',
            'source_url' => 'https://revenue.support.tn.gov/hc/en-us/articles/360058175152-SUT-32-Sale-for-Resale-Appropriate-Use-of-a-Resale-Certificate',
        ],
        [
            'text' => 'Sellers verify a resale certificate in TNTAP under Sales & Use Tax Certificate Lookup, choosing Blanket Sales and Use Tax Certificate of Resale and entering the location ID. Verification does not replace keeping a copy.',
            'source_url' => 'https://revenue.support.tn.gov/hc/en-us/articles/360058251112-SUT-76-Sales-Tax-Filing-Verifying-Exemption-Certificates',
        ],
    ],
    'state_notes' => 'If you are registered for Tennessee sales and use tax, the Tennessee Department of Revenue issues your resale certificate. It is called the Blanket Certificate of Resale. You print it from TNTAP and give a copy to each supplier. Each business location has its own certificate and 10-digit location ID. Instead, you may give the Streamlined Sales Tax Certificate of Exemption, marked Resale, with your Tennessee account number. Tennessee also accepts the Multistate Tax Commission uniform certificate for resale only. Out-of-state dealers may give their home state\'s resale certificate or a completed Streamlined certificate with their home-state sales tax number. Suppliers can check your certificate in TNTAP by location ID. The certificate does not expire, but it ends when the location closes. Your supplier keeps it for three years plus the current year. Use the certificate only for items you will resell. If you take items from inventory for your own use, report them and pay the tax. Misusing a resale certificate is a misdemeanor, and the Commissioner can revoke your registration under Rule 1320-05-01-.68.',
    'sources' => [
        [
            'title' => 'TDOR SUT-31 Resale Certificate Overview (updated 2025-03-27)',
            'url' => 'https://revenue.support.tn.gov/hc/en-us/articles/360058174992-SUT-31-Sale-for-Resale-Resale-Certificate-Overview',
        ],
        [
            'title' => 'TDOR SUT-33 Out-of-State Resale Certificates',
            'url' => 'https://revenue.support.tn.gov/hc/en-us/articles/360058623991-SUT-33-Sale-for-Resale-Out-of-State-Resale-Certificates',
        ],
        [
            'title' => 'TDOR SUT-32 Appropriate Use of a Resale Certificate',
            'url' => 'https://revenue.support.tn.gov/hc/en-us/articles/360058175152-SUT-32-Sale-for-Resale-Appropriate-Use-of-a-Resale-Certificate',
        ],
        [
            'title' => 'TDOR SUT-35 Drop Shipments to Tennessee Customers',
            'url' => 'https://revenue.support.tn.gov/hc/en-us/articles/360058175672-SUT-35-Sale-for-Resale-Drop-Shipments-to-Tennessee-Customers',
        ],
        [
            'title' => 'TDOR SUT-76 Verifying Exemption Certificates',
            'url' => 'https://revenue.support.tn.gov/hc/en-us/articles/360058251112-SUT-76-Sales-Tax-Filing-Verifying-Exemption-Certificates',
        ],
        [
            'title' => 'TDOR MS-11 Marketplace Sellers Purchasing for Resale',
            'url' => 'https://revenue.support.tn.gov/hc/en-us/articles/4408841122836',
        ],
        [
            'title' => 'Tennessee Sales and Use Tax Manual, August 2026',
            'url' => 'https://www.tn.gov/content/dam/tn/revenue/documents/tax_manuals/august-2026/sales-use-tax-manual.pdf',
        ],
        [
            'title' => 'TDOR Certificate of Resale (updated March 2021)',
            'url' => 'https://www.tn.gov/content/dam/tn/revenue/documents/taxpayer_education/sales/s&u_resale_certificates.pdf',
        ],
        [
            'title' => 'TDOR Streamlined Sales Tax Certificate of Exemption',
            'url' => 'https://www.tn.gov/content/dam/tn/revenue/documents/forms/sales/sst_exemptioncertificate.pdf',
        ],
        [
            'title' => 'Tenn. Comp. R. & Regs. 1320-05-01-.68 Resale Certificate',
            'url' => 'https://www.law.cornell.edu/regulations/tennessee/Tenn-Comp-R-Regs-1320-05-01-.68',
        ],
        [
            'title' => 'Tenn. Code Ann. §67-6-607 (2010, Justia)',
            'url' => 'https://law.justia.com/codes/tennessee/2010/title-67/chapter-6/part-6/67-6-607',
        ],
    ],
];
