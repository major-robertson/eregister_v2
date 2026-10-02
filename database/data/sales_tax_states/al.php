<?php

/*
 * Alabama: Alabama Department of Revenue, Sales Tax License. Researched
 * 2026-10-02 from revenue.alabama.gov, alabamaretail.org, commenda.io.
 * Generated once from the EREG-13 sales tax research; edit this file
 * directly from now on.
 */

return [
    'state' => 'AL',
    'name' => 'Alabama',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Alabama Department of Revenue',
        'short' => 'ALDOR',
        'url' => 'https://www.revenue.alabama.gov/',
    ],
    'registration' => [
        'term' => 'Sales Tax License',
        'portal' => [
            'name' => 'My Alabama Taxes (MAT)',
            'url' => 'https://myalabamataxes.alabama.gov/',
        ],
        'form' => [
            'number' => null,
            'title' => 'Online business tax registration in My Alabama Taxes (no paper application found)',
            'pdf_url' => null,
            'online_only' => true,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No fee to register or to renew. Retailers of beer, wine and tobacco applying for a license must buy a one-time surety bond of at least $25,000 for two years; any licensed retailer who falls out of compliance can be required to do the same.',
            'cite' => 'https://www.revenue.alabama.gov/sales-use/business-tax-online-registration-system/; Ala. Code 40-23-6; ALDOR Notice, Oct. 1, 2019, https://www.revenue.alabama.gov/wp-content/uploads/2020/02/Notice-Surety-Bond_20191001.pdf',
        ],
        'renewal' => [
            'required' => true,
            'summary' => 'Every ALDOR sales and use tax license (sales, rental, sellers use, lodgings, utility gross receipts, simplified sellers use) must be renewed online in MAT each year by December 31. There is no renewal charge.',
            'cite' => 'https://www.revenue.alabama.gov/aldor-sales-and-other-tax-license-renewals-go-annual-online/',
        ],
        'timing' => [
            'online' => '3 to 5 days for the account number',
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'ALDOR says it takes 3 to 5 days to receive an account number after registering online. The applicant gets a confirmation number at submission and the license is mailed later.',
            'cite' => 'https://www.revenue.alabama.gov/sales-use/business-tax-online-registration-system/; https://www.revenue.alabama.gov/faqs/how-do-i-apply-or-register-for-a-sales-tax-number/',
        ],
        'number' => [
            'name' => 'Alabama sales tax account number',
            'format' => null,
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'A business that sells tangible personal property at retail from a location, inventory or staff in Alabama must hold a state sales tax license before selling (Ala. Code 40-23-6).',
        'economic' => [
            'revenue_usd' => 250000,
            'transactions' => null,
            'period' => 'previous calendar year (retail sales of tangible personal property sold into Alabama)',
            'effective' => '2018-10-01',
            'cite' => 'Ala. Admin. Code r. 810-6-2-.90.03; https://www.revenue.alabama.gov/news/ador-announces-sales-and-use-tax-guidance-for-online-sellers/',
        ],
        'marketplace' => 'Under Act 2018-539, a marketplace facilitator with more than $250,000 in Alabama marketplace sales must collect tax on third-party sellers\' sales (through the Simplified Sellers Use Tax program) or meet reporting and customer-notice requirements; a remote seller is relieved where a facilitator collects for it (https://www.revenue.alabama.gov/news/ador-announces-sales-and-use-tax-guidance-for-online-sellers/).',
    ],
    'filing' => [
        'frequencies' => 'monthly by default; quarterly, bi-annual or annual on request for small accounts',
        'rule' => 'Monthly is the default. A taxpayer may request quarterly filing if prior-year state sales tax was under $2,400, bi-annual if under $1,200 (or sales in no more than two 30-day periods), and annual if under $600 (or sales in one 30-day period). Changes must be requested before February 20 for that year.',
        'due_day' => '20th of the month after the period',
        'zero_return_required' => null,
        'prepayments' => null,
        'cite' => 'https://www.revenue.alabama.gov/faqs/when-is-the-sales-tax-due/',
    ],
    'rates' => [
        'state_rate_pct' => 4.0,
        'local' => 'City and county sales taxes apply on top of the 4% state rate; ALDOR administers over 200 of them but not all. The state rate on SNAP-eligible food is 2% from September 1, 2025.',
        'sourcing' => 'destination (rate where the buyer receives the goods); from secondary sources, not confirmed on an ALDOR page',
        'cite' => 'https://www.revenue.alabama.gov/faqs/local-sales-tax/; https://www.revenue.alabama.gov/notice-state-sales-and-use-tax-rate-reduced-on-food-beginning-september-1-2025/',
    ],
    'local_registration' => [
        'required' => true,
        'summary' => 'Yes, for self-administered localities. ALDOR collects over 200 city and county sales taxes but tells sellers to contact every county and city where they do business to see if they must register with it directly. Large cities such as Birmingham, Montgomery, Mobile and Huntsville administer their own sales tax, and some localities use private administrators. Many local returns, including some non-state-administered ones, can be filed through ONE SPOT in My Alabama Taxes. Remote sellers in the Simplified Sellers Use Tax program collect a flat 8% and do not register locally.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => null,
            'cite' => null,
        ],
        'late_filing' => [
            'summary' => 'Failure to file on time: the greater of 10% of the tax due or $50. Failure to pay on time: 10% of the tax due, plus interest.',
            'cite' => 'Ala. Code 40-2A-11(a); https://www.revenue.alabama.gov/faqs/is-there-a-penalty-imposed-for-not-timely-filing-and-paying-the-sales-tax-due/',
        ],
    ],
    'connected' => [
        'covers' => [
            'state sales tax',
            'state-administered local sales and use taxes',
            'rental tax',
            'lodgings tax',
            'consumer use tax',
            'sellers use tax',
            'withholding',
        ],
        'prerequisites' => null,
        'cite' => 'https://www.revenue.alabama.gov/sales-use/business-tax-online-registration-system/; https://www.revenue.alabama.gov/faqs/how-do-i-apply-or-register-for-a-sales-tax-number/',
    ],
    'facts' => [
        [
            'text' => 'Since November 2020, every ALDOR sales and use tax license expires each December 31 and must be renewed online in My Alabama Taxes at no charge.',
            'source_url' => 'https://www.revenue.alabama.gov/aldor-sales-and-other-tax-license-renewals-go-annual-online/',
        ],
        [
            'text' => 'Remote sellers above $250,000 can join the Simplified Sellers Use Tax (SSUT) program and collect a flat 8% on all Alabama sales instead of tracking state and local rates. A 2024 bill to raise the SSUT rate to 9.3% did not pass.',
            'source_url' => 'https://www.revenue.alabama.gov/news/ador-announces-sales-and-use-tax-guidance-for-online-sellers/',
        ],
        [
            'text' => 'Act 2025-305 cut the state sales and use tax on food (SNAP-eligible) from 3% to 2% effective September 1, 2025. Local taxes on food are unchanged.',
            'source_url' => 'https://www.revenue.alabama.gov/notice-state-sales-and-use-tax-rate-reduced-on-food-beginning-september-1-2025/',
        ],
        [
            'text' => 'A licensed buyer uses its Alabama Sales Tax License as its resale certificate; ALDOR says a resale certificate is officially called a Sales Tax License.',
            'source_url' => 'https://www.revenue.alabama.gov/faqs/where-do-i-get-a-copy-of-my-resale-certificate/',
        ],
        [
            'text' => 'A timely-payment discount applies: 5% of the first $100 of tax and 2% of tax over $100, capped at $400 a month.',
            'source_url' => 'https://www.revenue.alabama.gov/faqs/when-is-the-sales-tax-due/',
        ],
    ],
    'state_notes' => 'In Alabama, the permit is called a Sales Tax License. The Alabama Department of Revenue (ALDOR) issues it. You apply online through My Alabama Taxes (MAT); ALDOR\'s pages describe no paper application. There is no fee. ALDOR says it takes 3 to 5 days to get your account number, and the license is mailed to you later. The same registration can add rental, lodgings, consumer use and withholding accounts. Retailers of beer, wine or tobacco must also post a surety bond of at least $25,000. Every license expires on December 31 and must be renewed online each year, at no charge. After you register, you file monthly by the 20th. Small accounts can ask for quarterly, bi-annual or annual filing before February 20 each year. The one mistake to avoid: assuming the state license covers every local tax. ALDOR collects many city and county taxes, but some cities, including Birmingham, Montgomery, Mobile and Huntsville, run their own. Check with each city and county where you sell to see if you must register with it too. Remote sellers over $250,000 a year can instead use the Simplified Sellers Use Tax program at a flat 8%.',
    'sources' => [
        [
            'title' => 'ALDOR: Business Tax Online Registration System',
            'url' => 'https://www.revenue.alabama.gov/sales-use/business-tax-online-registration-system/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ALDOR FAQ: How do I obtain or register for a sales tax license?',
            'url' => 'https://www.revenue.alabama.gov/faqs/how-do-i-apply-or-register-for-a-sales-tax-number/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ALDOR: Sales and other tax license renewals go annual, online',
            'url' => 'https://www.revenue.alabama.gov/aldor-sales-and-other-tax-license-renewals-go-annual-online/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ALDOR Notice: Surety bond requirement (Oct. 1, 2019)',
            'url' => 'https://www.revenue.alabama.gov/wp-content/uploads/2020/02/Notice-Surety-Bond_20191001.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ALDOR: Sales and use tax guidance for online sellers',
            'url' => 'https://www.revenue.alabama.gov/news/ador-announces-sales-and-use-tax-guidance-for-online-sellers/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ALDOR FAQ: When is the sales tax due?',
            'url' => 'https://www.revenue.alabama.gov/faqs/when-is-the-sales-tax-due/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ALDOR FAQ: Local sales tax',
            'url' => 'https://www.revenue.alabama.gov/faqs/local-sales-tax/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ALDOR FAQ: Penalty for not timely filing and paying',
            'url' => 'https://www.revenue.alabama.gov/faqs/is-there-a-penalty-imposed-for-not-timely-filing-and-paying-the-sales-tax-due/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ALDOR Notice: State rate on food reduced Sept. 1, 2025',
            'url' => 'https://www.revenue.alabama.gov/notice-state-sales-and-use-tax-rate-reduced-on-food-beginning-september-1-2025/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Alabama Retail Association: SSUT revision 2024',
            'url' => 'https://alabamaretail.org/news/simplified-sellers-revision-2024/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Commenda: Birmingham sales tax (secondary, self-administered cities)',
            'url' => 'https://www.commenda.io/usa/alabama/birmingham-sales-tax',
            'accessed' => '2026-10-02',
        ],
    ],
];
