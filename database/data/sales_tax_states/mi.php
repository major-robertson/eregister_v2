<?php

/*
 * Michigan: Michigan Department of Treasury, Sales Tax License. Researched
 * 2026-10-02 from michigan.gov, legislature.mi.gov,
 * legislature.michigan.gov. Generated once from the EREG-13 sales tax
 * research; edit this file directly from now on.
 */

return [
    'state' => 'MI',
    'name' => 'Michigan',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Michigan Department of Treasury',
        'short' => 'Treasury',
        'url' => 'https://www.michigan.gov/treasury',
    ],
    'registration' => [
        'term' => 'Sales Tax License',
        'portal' => [
            'name' => 'Michigan Treasury Online (MTO), Start a New Business (eRegistration)',
            'url' => 'https://mto.treasury.michigan.gov/',
        ],
        'form' => [
            'number' => '518',
            'title' => 'Registration for Michigan Taxes (Michigan Business Taxes Registration Booklet)',
            'pdf_url' => 'https://www.michigan.gov/taxes/-/media/Project/Websites/taxes/Forms/MBT/518_1017.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No fee for a sales tax license. Treasury may require a surety bond or cash deposit of $1,000 to $25,000 from applicants with past tax delinquencies.',
            'cite' => 'https://www.michigan.gov/taxes/business-taxes/sales-use-tax/information/sales-tax-license-faq; MCL 205.53(1)',
        ],
        'renewal' => [
            'required' => true,
            'summary' => 'Licenses are issued yearly and valid January through December. Treasury renews them automatically each year unless the business cancels, as long as the tax due is paid.',
            'cite' => 'https://www.michigan.gov/taxes/business-taxes/sales-use-tax/information/remote-seller-faq; MCL 205.53(1)',
        ],
        'timing' => [
            'online' => 'authenticated within 10 to 15 minutes; up to 48 hours to process',
            'paper' => '4 to 6 weeks',
            'temporary_number' => false,
            'summary' => 'Treasury says eRegistration is authenticated within 10 to 15 minutes of submission and can take up to 48 hours to process; once processed, the license is available in MTO immediately. Mailed Form 518 takes 4 to 6 weeks. A business may start selling before the license arrives but must remit tax from its first sale.',
            'cite' => 'https://www.michigan.gov/taxes/business-taxes/sales-use-tax/information/sales-tax-license-faq; https://www.michigan.gov/taxes/business-taxes/new-biz',
        ],
        'number' => [
            'name' => 'Treasury business account number (the business\'s FEIN; Treasury Registration (TR) number if no FEIN)',
            'format' => '9-digit FEIN, or a Treasury-assigned TR number for businesses without one',
            'cite' => 'https://www.michigan.gov/taxes/business-taxes/new-biz; https://www.michigan.gov/taxes/questions/tax-faqs/michigan-treasury-online/mto-registration/',
        ],
    ],
    'nexus' => [
        'physical' => 'Anyone who sells tangible personal property to final consumers in Michigan, including service businesses that also sell parts, needs a license; wholesalers and contractors do not.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => 'previous calendar year (gross sales or separate transactions with Michigan customers); collection starts Jan. 1 of the following year',
            'effective' => '2018-10-01',
            'cite' => 'RAB 2021-21; https://www.michigan.gov/taxes/business-taxes/sales-use-tax/information/remote-seller-faq',
        ],
        'marketplace' => 'Since Jan. 1, 2020, a marketplace facilitator with over $100,000 in Michigan sales or 200+ transactions in the previous calendar year must collect on sales it facilitates (RAB 2021-22; Treasury Marketplace Facilitator FAQs).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annual, plus an annual return for all',
        'rule' => 'Treasury assigns a frequency from estimated activity, then each year from the prior year\'s liability. Every filer also files an annual return by February 28. Taxpayers averaging $720,000 or more in sales or use tax a year pay on an accelerated schedule. Streamlined (SST) registrants file monthly with no annual return.',
        'due_day' => '20th of the month after the period; annual return due February 28',
        'zero_return_required' => true,
        'prepayments' => 'Accelerated filers ($720,000+ annual sales or use tax) prepay by the 20th of the current month and reconcile by the 20th of the next month.',
        'cite' => 'https://www.michigan.gov/taxes/business-taxes/sales-use-tax; https://www.michigan.gov/taxes/business-taxes/sales-use-tax/information/filing-requirements-faq',
    ],
    'rates' => [
        'state_rate_pct' => 6.0,
        'local' => 'None. Michigan does not allow city or local sales or use taxes. Residential utilities and home heating fuel are taxed at 4%.',
        'sourcing' => 'destination (Streamlined Sales Tax member)',
        'cite' => 'https://www.michigan.gov/taxes/business-taxes/sales-use-tax',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'No local sales tax and no local registration.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Doing taxable business without a license, or after it expires or is suspended, is a misdemeanor punishable by a fine of up to $1,000, up to 1 year in jail, or both.',
            'cite' => 'MCL 205.53(3)',
        ],
        'late_filing' => [
            'summary' => '5% of the tax if not more than 2 months late, plus 5% for each further month, up to 25%, plus interest.',
            'cite' => 'MCL 205.24',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'use tax',
            'income tax withholding',
            'corporate income tax',
            'unemployment insurance (UIA)',
        ],
        'prerequisites' => 'FEIN required for online eRegistration (businesses without one mail Form 518 and get a TR number).',
        'cite' => 'https://www.michigan.gov/taxes/questions/tax-faqs/michigan-treasury-online/mto-registration/; Form 518',
    ],
    'facts' => [
        [
            'text' => 'Since April 26, 2023 (PA 20 and PA 21 of 2023), most separately stated delivery and installation charges are no longer taxable in Michigan.',
            'source_url' => 'https://www.michigan.gov/taxes/business-taxes/sales-use-tax/information/remote-seller-faq',
        ],
        [
            'text' => 'Michigan does not license wholesalers; they claim exemption on Form 3372 marked \'Resale at Wholesale\'.',
            'source_url' => 'https://www.michigan.gov/taxes/business-taxes/sales-use-tax/information/sales-tax-license-faq',
        ],
        [
            'text' => 'A seller at only one or two events a year may file Form 5089, Concessionaire\'s Sales Tax Return, instead of getting a license; more than two events requires a license.',
            'source_url' => 'https://www.michigan.gov/taxes/business-taxes/sales-use-tax/information/sales-tax-license-faq',
        ],
        [
            'text' => 'Every Michigan sales tax filer must file an annual reconciliation return by February 28, whatever its regular frequency.',
            'source_url' => 'https://www.michigan.gov/taxes/business-taxes/sales-use-tax',
        ],
        [
            'text' => 'Michigan still uses a 200-transaction test alongside $100,000 for remote sellers, measured on the previous calendar year.',
            'source_url' => 'https://www.michigan.gov/taxes/business-taxes/sales-use-tax/information/remote-seller-faq',
        ],
    ],
    'state_notes' => 'In Michigan, the permit is a Sales Tax License from the Michigan Department of Treasury. You apply online through Michigan Treasury Online (MTO) using Start a New Business, or by mailing Form 518, Registration for Michigan Taxes. Online registration needs an FEIN. There is no fee, though Treasury can ask for a bond from applicants with past unpaid taxes. Treasury says online applications are recognized within 10 to 15 minutes and can take up to 48 hours to process. Mailed forms take 4 to 6 weeks. The license runs January to December and renews automatically each year if your taxes are paid. Treasury assigns monthly, quarterly or annual filing based on your expected and then actual tax. Returns are due on the 20th of the month after the period. Every filer must also send an annual return by February 28. The rate is a flat 6%, and Michigan has no local sales tax. Remote sellers must register if they had over $100,000 in Michigan sales or 200 transactions in the prior year. The mistake to avoid: skipping the annual return. Monthly and quarterly filers still owe it, and it does not replace any missed period returns.',
    'sources' => [
        [
            'title' => 'Treasury: Sales and Use Taxes',
            'url' => 'https://www.michigan.gov/taxes/business-taxes/sales-use-tax',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Treasury: Sales Tax License FAQ',
            'url' => 'https://www.michigan.gov/taxes/business-taxes/sales-use-tax/information/sales-tax-license-faq',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Treasury: New Business Registration',
            'url' => 'https://www.michigan.gov/taxes/business-taxes/new-biz',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Treasury: MTO Registration FAQ',
            'url' => 'https://www.michigan.gov/taxes/questions/tax-faqs/michigan-treasury-online/mto-registration/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Treasury: Remote Seller FAQ',
            'url' => 'https://www.michigan.gov/taxes/business-taxes/sales-use-tax/information/remote-seller-faq',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Treasury: Filing Requirements FAQ',
            'url' => 'https://www.michigan.gov/taxes/business-taxes/sales-use-tax/information/filing-requirements-faq',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Form 518 Registration for Michigan Taxes',
            'url' => 'https://www.michigan.gov/taxes/-/media/Project/Websites/taxes/Forms/MBT/518_1017.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'MCL 205.53',
            'url' => 'https://www.legislature.mi.gov/Laws/MCL?objectName=mcl-205-53',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'MCL 205.24',
            'url' => 'https://legislature.michigan.gov/(S(wp00slpfd4zaspfplcvsokqa))/mileg.aspx?page=getObject&objectname=mcl-205-24',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'RAB 2021-21 Nexus Standards for Remote Sellers',
            'url' => 'https://www.michigan.gov/taxes/rep-legal/rab/2021-revenue-administrative-bulletins/revenue-administrative-bulletin-2021-21',
            'accessed' => '2026-10-02',
        ],
    ],
];
