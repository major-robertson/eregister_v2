<?php

/*
 * Minnesota: Minnesota Department of Revenue, Sales and Use Tax account
 * (sales tax permit) under a Minnesota Tax ID Number. Researched
 * 2026-10-02 from revenue.state.mn.us, taxcloud.com. Generated once from
 * the EREG-13 sales tax research; edit this file directly from now on.
 */

return [
    'state' => 'MN',
    'name' => 'Minnesota',
    'term_label' => 'Sales and Use Tax Account',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Minnesota Department of Revenue',
        'short' => 'the Department of Revenue',
        'url' => 'https://www.revenue.state.mn.us/',
    ],
    'registration' => [
        'term' => 'Sales and Use Tax account (sales tax permit) under a Minnesota Tax ID Number',
        'portal' => [
            'name' => 'Business Tax Registration (online), then e-Services',
            'url' => 'https://www.revenue.state.mn.us/guide/registering-your-business',
        ],
        'form' => [
            'number' => 'ABR',
            'title' => 'Minnesota Application for Business Registration (instruction booklet abr-inst-24)',
            'pdf_url' => 'https://www.revenue.state.mn.us/sites/default/files/2024-02/abr-inst-24.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No registration fee is listed for a sales and use tax account in the Department\'s registration guide or the ABR instructions.',
            'cite' => 'https://www.revenue.state.mn.us/guide/registering-your-business; ABR instructions (2024); cross-check TaxCloud',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'No renewal is described; the account stays open unless the business closes it or the Department cancels or revokes it for noncompliance.',
            'cite' => 'https://www.revenue.state.mn.us/guide/registering-your-business',
        ],
        'timing' => [
            'online' => null,
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'After registering, the business receives a confirmation letter with its Minnesota Tax ID Number, the tax types opened and a temporary e-Services password. The Department does not publish a processing time; it will not issue a Tax ID more than one year before operations begin.',
            'cite' => 'ABR instructions (2024); https://www.revenue.state.mn.us/e-services-information',
        ],
        'number' => [
            'name' => 'Minnesota Tax ID Number',
            'format' => '7 digits',
            'cite' => 'resale research 2026-10-01',
        ],
    ],
    'nexus' => [
        'physical' => 'A business with a physical location, inventory, or an employee, representative, agent or contractor working in Minnesota must register before making any taxable sales.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => 'prior 12 consecutive months (retail sales shipped to Minnesota)',
            'effective' => '2018-10-01',
            'cite' => 'Minn. Stat. 297A.66; Minnesota Sales and Use Tax Business Guide (Small Seller Exception)',
        ],
        'marketplace' => 'A marketplace provider must register and collect on retail sales it facilitates for its marketplace sellers, reporting them on its own return (Minn. Stat. 297A.66; Minnesota Sales and Use Tax Business Guide).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annual by average monthly tax',
        'rule' => 'Less than $100 a month: annual (due February 5). $100 to $500 a month: quarterly (due April 20, July 20, October 20, January 20). Over $500 a month: monthly. Returns are filed online through e-Services. Businesses paying over $10,000 in a tax in the prior fiscal year must pay electronically.',
        'due_day' => '20th of the month after the period (annual returns due February 5)',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'Minnesota Sales and Use Tax Business Guide (Filing Frequency)',
    ],
    'rates' => [
        'state_rate_pct' => 6.875,
        'local' => 'Many cities, counties and special areas add local sales taxes, most administered by the Department of Revenue; combined rates vary by location (use the Department\'s rate calculator). A 50-cent Retail Delivery Fee applies to taxable deliveries of $100 or more by retailers with $1 million+ in Minnesota sales.',
        'sourcing' => 'destination (Streamlined Sales Tax member)',
        'cite' => 'Minnesota Sales and Use Tax Business Guide; https://www.revenue.state.mn.us/retail-delivery-fee',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'Local and special local sales taxes administered by the Department are added to the same state registration (check the boxes for each local tax). Some cities and areas administer their own lodging and other special taxes, which require contacting that city directly.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Making retail sales after the Department cancels or revokes a sales tax account can bring a felony charge and a $100-per-day civil fine.',
            'cite' => 'https://www.revenue.state.mn.us/guide/registering-your-business',
        ],
        'late_filing' => [
            'summary' => 'Late filing: 5% of tax not paid by the due date. Late payment: 5%, plus 5% for each additional 30 days, up to 15%, plus interest.',
            'cite' => 'https://www.revenue.state.mn.us/penalties-and-interest-businesses',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales and use tax',
            'local sales taxes administered by Revenue',
            'withholding tax',
            'other business taxes under one Minnesota Tax ID',
        ],
        'prerequisites' => 'FEIN (LLCs need both federal and state IDs even without employees); Minnesota Secretary of State registration for entities. Past-due sales tax must be paid before a new account is opened.',
        'cite' => 'ABR instructions (2024); https://www.revenue.state.mn.us/guide/registering-your-business',
    ],
    'facts' => [
        [
            'text' => 'Since July 1, 2024, a 50-cent Retail Delivery Fee applies to deliveries in Minnesota of taxable goods or clothing totaling $100 or more, for retailers with $1 million or more in prior-year Minnesota sales; it is reported on the sales tax return.',
            'source_url' => 'https://www.revenue.state.mn.us/retail-delivery-fee',
        ],
        [
            'text' => 'Minnesota\'s remote seller test still counts transactions: 200 or more retail sales, or more than $100,000, shipped to Minnesota over 12 consecutive months.',
            'source_url' => 'https://www.revenue.state.mn.us/book/export/html/10021',
        ],
        [
            'text' => 'A business with more than one location may file one consolidated return; craft-show sales and service routes are not separate locations.',
            'source_url' => 'https://www.revenue.state.mn.us/guide/registering-your-business',
        ],
        [
            'text' => 'Misusing an exemption certificate to evade tax can bring a $100 penalty per transaction.',
            'source_url' => 'https://www.revenue.state.mn.us/book/export/html/10021',
        ],
        [
            'text' => 'A business that owes past-due sales tax cannot open a new sales tax account until that tax is paid.',
            'source_url' => 'https://www.revenue.state.mn.us/guide/registering-your-business',
        ],
    ],
    'state_notes' => 'In Minnesota, you register with the Minnesota Department of Revenue for a Minnesota Tax ID Number and a Sales and Use Tax account. You must do this before your first taxable sale. You can apply online through Business Tax Registration or by phone. The paper form is the Application for Business Registration (ABR). No registration fee is listed. You will get a confirmation letter with your 7-digit Tax ID and a temporary password for e-Services, where you file. When you register, also add any local sales taxes that apply where you sell. The account does not need renewal. Your filing schedule depends on your average tax. Under $100 a month files annually, $100 to $500 quarterly, and more than that monthly. Monthly and quarterly returns are due on the 20th, and annual returns on February 5. All returns are filed online. The state rate is 6.875%, plus local taxes in many areas. Remote sellers must register after 200 sales or more than $100,000 shipped to Minnesota in 12 months. Larger retailers also owe a 50-cent fee on many deliveries. The mistake to avoid: skipping zero returns. You must file even when you had no taxable sales.',
    'sources' => [
        [
            'title' => 'Registering Your Business (Sales and Use Tax Business Guide)',
            'url' => 'https://www.revenue.state.mn.us/guide/registering-your-business',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Minnesota Sales and Use Tax Business Guide (full export)',
            'url' => 'https://www.revenue.state.mn.us/book/export/html/10021',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Minnesota Application for Business Registration Instruction Booklet (2024)',
            'url' => 'https://www.revenue.state.mn.us/sites/default/files/2024-02/abr-inst-24.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Penalties and Interest for Businesses',
            'url' => 'https://www.revenue.state.mn.us/penalties-and-interest-businesses',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Retail Delivery Fee',
            'url' => 'https://www.revenue.state.mn.us/retail-delivery-fee',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Special Local Taxes',
            'url' => 'https://www.revenue.state.mn.us/guide/special-local-taxes',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TaxCloud Minnesota guide (cross-check)',
            'url' => 'https://taxcloud.com/sales-tax/minnesota/',
            'accessed' => '2026-10-02',
        ],
    ],
];
