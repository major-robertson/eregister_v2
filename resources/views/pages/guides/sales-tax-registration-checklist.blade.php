@extends('layouts.landing')

@section('title', $guide['page_title'])
@section('description', $guide['description'])
@section('canonical', \App\Support\Seo\Guides::url($guide['slug']))
@section('og_type', 'article')

@php
    $faq = [
        ['q' => 'How long does it take to get a sales tax permit?', 'a' => 'It depends on the state. As of October 2026, California says it may be able to issue a seller\'s permit the same day. Florida says to allow three business days for an online application. Arkansas says to allow up to two weeks, and Texas says to allow two to three weeks to receive the permit. Apply before your first taxable sale, not after.'],
        ['q' => 'Do I need an EIN to register for a sales tax permit?', 'a' => 'Usually, if you have one. Florida\'s instructions note that the IRS requires an EIN for any business with employees and for partnerships, corporations, nonprofits, trusts and estates, and Florida asks for it to register. A sole proprietor without employees can often use a Social Security number. Texas lets you submit a permit application before you have a federal identification number. The IRS issues EINs online at no charge, usually immediately if the application is approved.'],
        ['q' => 'How much does it cost to register for a sales tax permit?', 'a' => 'Many states charge nothing. California and Texas charge no permit fee, though either can ask for a security deposit or bond. Some states charge a small fee: Arkansas collects $50 when you submit the application, as of October 2026. Check your state\'s registration page for its current fee and any per-location charges.'],
        ['q' => 'Do I have to file a sales tax return if I had no sales?', 'a' => 'Yes, in most states. Texas requires a return every period even when there are no taxable sales or purchases to report, and charges a $50 penalty for each late report even when no tax was due. Florida\'s minimum late penalty of $50 also applies when no tax is due. Mark every due date on your calendar from the day you register.'],
        ['q' => 'What is a NAICS code and where do I find mine?', 'a' => 'A NAICS code is a number from the North American Industry Classification System that describes your main business activity. Texas requires one on every sales tax permit application, and Florida asks for one for each business activity. You can search the U.S. Census Bureau\'s NAICS site by keyword to find the code that best fits what you sell.'],
    ];
    $sources = [
        ['name' => 'IRS, Get an employer identification number', 'url' => 'https://www.irs.gov/businesses/small-businesses-self-employed/get-an-employer-identification-number'],
        ['name' => 'U.S. Census Bureau, North American Industry Classification System', 'url' => 'https://www.census.gov/naics/'],
        ['name' => 'Texas Comptroller, Sales and Use Tax Permit (application requirements and timing)', 'url' => 'https://comptroller.texas.gov/taxes/permit/'],
        ['name' => 'Texas Comptroller, Sales Tax Permit FAQ', 'url' => 'https://comptroller.texas.gov/taxes/sales/faq/permit.php'],
        ['name' => 'Tex. Tax Code ch. 151 (151.201 permits; 151.251 security; 151.401 due dates; 151.703 penalties)', 'url' => 'https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm'],
        ['name' => 'CDTFA Publication 107, Applying for a Seller\'s Permit', 'url' => 'https://cdtfa.ca.gov/formspubs/pub107/applying-for-a-sellers-permit.htm'],
        ['name' => 'CDTFA Publication 73, Your California Seller\'s Permit', 'url' => 'https://cdtfa.ca.gov/formspubs/pub73.pdf'],
        ['name' => 'Florida DOR, Form DR-1N, Instructions for the Florida Business Tax Application (R. 01/26)', 'url' => 'https://floridarevenue.com/Forms_library/current/dr1n.pdf'],
        ['name' => 'Florida DOR, Online registration', 'url' => 'https://floridarevenue.com/taxes/eservices/Pages/registration.aspx'],
        ['name' => 'Florida DOR, GT-300015, New Dealer Guide to Working with the Florida Department of Revenue', 'url' => 'https://floridarevenue.com/Forms_library/current/guides/gt300015.pdf'],
        ['name' => 'Arkansas DFA, Register for a Tax Account', 'url' => 'https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/register-for-a-tax-account/'],
    ];

    // The per-state registration price from the catalog, the same source as
    // SalesTaxStatePage::price(). Left out of the sentence if the catalog is unseeded.
    try {
        $price = \App\Support\Seo\Prices::dollars(\App\Models\Price::resolve('tax', 'sales_tax_permit', 'per_state', 'one_time')->amount_cents);
    } catch (\Throwable) {
        $price = null;
    }
@endphp

@section('content')
<x-guides.article :guide="$guide" :faq="$faq" :sources="$sources">
    <p>
        To register for a sales tax permit, apply online with the tax agency in each state where you have nexus.
        Before you start, gather your federal EIN or Social Security number, your legal entity details, a NAICS code,
        your start date, an estimate of your sales, and the names and ID numbers of the owners or officers. Most
        states approve the application within a few days to a few weeks.
    </p>
    <p>
        A sales tax permit (also called a seller's permit or sales tax license) registers you to collect sales tax.
        Not sure where you need one? Read
        <a href="/guides/when-do-you-need-a-sales-tax-permit">when you need a sales tax permit</a> first.
    </p>

    <h2>Gather what every state asks for</h2>
    <p>Applications differ, but they ask for the same core items. Have them ready before you open the form.</p>
    <x-guides.table id="registration-checklist-table">
        <thead>
            <tr>
                <th scope="col">Item</th>
                <th scope="col">What to have ready</th>
                <th scope="col">Notes</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100">
            <tr>
                <th scope="row">Federal tax ID</th>
                <td>EIN, or SSN for a sole proprietor</td>
                <td>The IRS issues EINs online at no charge</td>
            </tr>
            <tr>
                <th scope="row">Legal entity details</th>
                <td>Legal name, entity type, state file number</td>
                <td>Texas asks corporations for their Secretary of State file number</td>
            </tr>
            <tr>
                <th scope="row">NAICS code</th>
                <td>The code for each business activity</td>
                <td>Search the Census Bureau's NAICS site</td>
            </tr>
            <tr>
                <th scope="row">Business locations</th>
                <td>Every address where you sell or store goods</td>
                <td>No P.O. box for a location in Arkansas</td>
            </tr>
            <tr>
                <th scope="row">Start date</th>
                <td>The date you began, or will begin, taxable sales</td>
                <td>Arkansas asks for the date operations will begin</td>
            </tr>
            <tr>
                <th scope="row">Estimated sales</th>
                <td>Expected monthly or yearly sales</td>
                <td>Used to set filing frequency and any deposit</td>
            </tr>
            <tr>
                <th scope="row">Owners and officers</th>
                <td>Name, home address, title, SSN or other ID</td>
                <td>Texas requires each officer's SSN</td>
            </tr>
            <tr>
                <th scope="row">Banking and suppliers</th>
                <td>Business bank account, main suppliers</td>
                <td>California asks for bank details</td>
            </tr>
        </tbody>
    </x-guides.table>
    <p>Three state examples show the pattern.</p>
    <p>
        <strong>Texas</strong> asks for the sole owner's Social Security number, each partner's SSN or federal
        employer identification number, a Texas corporation's Secretary of State file number, each officer's or
        director's SSN, and a NAICS code for every business
        (<a href="https://comptroller.texas.gov/taxes/permit/" rel="noopener" target="_blank">Texas Comptroller</a>).
        You can submit the application before you have a federal identification number
        (<a href="https://comptroller.texas.gov/taxes/sales/faq/permit.php" rel="noopener" target="_blank">Texas Comptroller permit FAQ</a>).
    </p>
    <p>
        <strong>California</strong> asks about your business, including bank account details and estimated income, and
        about you, including your driver license number and Social Security number
        (<a href="https://cdtfa.ca.gov/formspubs/pub107/applying-for-a-sellers-permit.htm" rel="noopener" target="_blank">CDTFA Publication 107</a>).
    </p>
    <p>
        <strong>Florida</strong> asks for a NAICS code for each business activity, and the name, home address and
        identification number of each owner, partner, officer or LLC member
        (<a href="https://floridarevenue.com/Forms_library/current/dr1n.pdf" rel="noopener" target="_blank">Form DR-1N</a>).
    </p>
    <p>
        If you have not formed your company or gotten an EIN yet, do that first. The IRS issues EINs online, and if
        the application is approved, the number comes immediately
        (<a href="https://www.irs.gov/businesses/small-businesses-self-employed/get-an-employer-identification-number" rel="noopener" target="_blank">IRS</a>).
        NAICS codes are listed on the
        <a href="https://www.census.gov/naics/" rel="noopener" target="_blank">Census Bureau's NAICS site</a>.
        eRegister can help with the <a href="/ein-tax-id">EIN</a> and an <a href="/llc">LLC formation</a> if you need
        them.
    </p>

    <h2>How long does approval take?</h2>
    <p>Timelines, as of October 2026:</p>
    <ul>
        <li>
            <strong>California</strong>: CDTFA says it may be able to issue your permit the same day
            (<a href="https://cdtfa.ca.gov/formspubs/pub107/applying-for-a-sellers-permit.htm" rel="noopener" target="_blank">Publication 107</a>).
        </li>
        <li>
            <strong>Florida</strong>: allow three business days for a new online application before checking its status
            (<a href="https://floridarevenue.com/taxes/eservices/Pages/registration.aspx" rel="noopener" target="_blank">Florida DOR</a>).
        </li>
        <li>
            <strong>Arkansas</strong>: allow up to two weeks
            (<a href="https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/register-for-a-tax-account/" rel="noopener" target="_blank">Arkansas DFA</a>).
        </li>
        <li>
            <strong>Texas</strong>: allow two to three weeks to receive your permit
            (<a href="https://comptroller.texas.gov/taxes/permit/" rel="noopener" target="_blank">Texas Comptroller</a>).
        </li>
    </ul>
    <p>Apply before your first taxable sale, so the permit is in hand when you start collecting tax.</p>

    <h2>When does a bond or deposit apply?</h2>
    <p>Most states charge little or nothing to register, but some can ask for security first.</p>
    <ul>
        <li>
            <strong>Texas</strong> law calls for adequate security from permit applicants, with some exemptions, and lets
            the Comptroller require it later from a permit holder who falls behind
            (<a href="https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm" rel="noopener" target="_blank">Tex. Tax Code 151.251</a>).
            The Comptroller's FAQ says there is no fee, but you may be required to post a security bond
            (<a href="https://comptroller.texas.gov/taxes/sales/faq/permit.php" rel="noopener" target="_blank">Texas Comptroller permit FAQ</a>).
        </li>
        <li>
            <strong>California</strong> charges no fee but may require a security deposit, for example when the law
            requires it, after a revocation, or after a history of nonpayment
            (<a href="https://cdtfa.ca.gov/formspubs/pub73.pdf" rel="noopener" target="_blank">Publication 73</a>).
        </li>
        <li>
            <strong>Arkansas</strong> charges a $50 permit fee as of October 2026, and other tax liabilities must be
            cleared before it issues a new permit
            (<a href="https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/register-for-a-tax-account/" rel="noopener" target="_blank">Arkansas DFA</a>).
        </li>
    </ul>
    <p>
        California says whether it asks for a deposit depends on your type of business and expected taxable sales
        (<a href="https://cdtfa.ca.gov/formspubs/pub107/applying-for-a-sellers-permit.htm" rel="noopener" target="_blank">Publication 107</a>).
        Give an honest estimate.
    </p>

    <h2>How is your filing frequency set?</h2>
    <p>The state assigns how often you file, usually based on how much tax you expect to collect.</p>
    <ul>
        <li>
            <strong>Texas</strong> makes returns due monthly, on the 20th of the following month. If you owe less than
            $500 a month or $1,500 a quarter, you file quarterly
            (<a href="https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm" rel="noopener" target="_blank">Tex. Tax Code 151.401</a>).
        </li>
        <li>
            <strong>Florida</strong> sets frequency by annual sales tax collections: monthly over $1,000, quarterly from
            $501 to $1,000, semiannually from $101 to $500, and annually at $100 or less. It reviews accounts each year
            (<a href="https://floridarevenue.com/Forms_library/current/guides/gt300015.pdf" rel="noopener" target="_blank">GT-300015</a>).
        </li>
        <li>
            <strong>California</strong> tells you your reporting basis when it issues the permit. Returns are due the last
            day of the month after the period ends
            (<a href="https://cdtfa.ca.gov/formspubs/pub73.pdf" rel="noopener" target="_blank">Publication 73</a>).
        </li>
    </ul>

    <h2>What arrives after approval?</h2>
    <p>
        You get a permit or certificate with your account number. Florida, for example, mails a Certificate of
        Registration, an Annual Resale Certificate, an initial supply of returns unless you file electronically, and a
        new dealer guide
        (<a href="https://floridarevenue.com/Forms_library/current/guides/gt300015.pdf" rel="noopener" target="_blank">GT-300015</a>).
    </p>
    <p>
        Display the permit. Texas requires you to display it at the place of business it covers, and issues a separate
        permit for each place of business
        (<a href="https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm" rel="noopener" target="_blank">Tex. Tax Code 151.201</a>).
        Florida says the same about its certificate.
    </p>
    <p>
        If you buy inventory to resell, your new permit number also goes on the resale certificates you give
        suppliers. See
        <a href="/guides/sellers-permit-vs-resale-certificate">seller's permit vs resale certificate</a>.
    </p>

    <h2>File your first return on time</h2>
    <p>Your first due date can come quickly, and the penalties start even when you owe nothing.</p>
    <ol>
        <li>
            <strong>File every period, even with no sales.</strong> Texas requires a return even if there are no taxable
            sales or purchases to report
            (<a href="https://comptroller.texas.gov/taxes/sales/faq/permit.php" rel="noopener" target="_blank">Texas Comptroller permit FAQ</a>).
            Florida says you must file and pay even if you do not receive returns in the mail
            (<a href="https://floridarevenue.com/Forms_library/current/guides/gt300015.pdf" rel="noopener" target="_blank">GT-300015</a>).
        </li>
        <li>
            <strong>Know the late penalties.</strong> Texas charges 5 percent of the tax, another 5 percent after 30 days,
            and a separate $50 penalty for each late report, even when no tax was due
            (<a href="https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm" rel="noopener" target="_blank">Tex. Tax Code 151.703</a>).
            Florida's late penalty is 10 percent of the tax, with a $50 minimum that applies even when no tax is due
            (<a href="https://floridarevenue.com/Forms_library/current/guides/gt300015.pdf" rel="noopener" target="_blank">GT-300015</a>).
        </li>
        <li>
            <strong>Keep the tax separate.</strong> Florida says sales tax you collect becomes state funds when you
            collect it, and you hold it as a custodian
            (<a href="https://floridarevenue.com/Forms_library/current/guides/gt300015.pdf" rel="noopener" target="_blank">GT-300015</a>).
        </li>
    </ol>

    <h2>What to do next</h2>
    <ul>
        <li>
            Confirm each state where you have nexus, using the
            <a href="/guides/economic-nexus-thresholds-by-state">economic nexus thresholds guide</a>.
        </li>
        <li>
            Open your state's page, such as <a href="/sales-tax-registration/texas">Texas</a>,
            <a href="/sales-tax-registration/california">California</a> or
            <a href="/sales-tax-registration/florida">Florida</a>, for its agency, fee and timeline.
        </li>
        <li>Put every return due date on your calendar the day the permit arrives.</li>
    </ul>
    <p>
        eRegister prepares and files sales tax permit applications{{ $price ? ' for '.$price.' per state' : '' }};
        start at <a href="/sales-tax-registration">sales tax registration</a>.
    </p>
    <p>
        This guide is general information, not tax advice. Requirements change, so check with the state before you
        file.
    </p>
</x-guides.article>
@endsection
