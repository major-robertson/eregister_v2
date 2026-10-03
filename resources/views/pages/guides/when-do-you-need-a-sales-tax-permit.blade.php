@extends('layouts.landing')

@section('title', $guide['page_title'])
@section('description', $guide['description'])
@section('canonical', \App\Support\Seo\Guides::url($guide['slug']))
@section('og_type', 'article')

@php
    $faq = [
        ['q' => 'Do I need a sales tax permit to sell online?', 'a' => 'You need one in your home state if you sell taxable goods there, and in any other state where you have nexus. Out-of-state online sales create nexus once you pass that state\'s economic threshold, such as $500,000 a year in Texas or California or $100,000 in Florida, as of October 2026. Sales made only through a marketplace that collects the tax for you may not count.'],
        ['q' => 'Do I need a sales tax permit for a one-day event or craft fair?', 'a' => 'Usually yes, if you sell taxable items in a state with sales tax. Texas counts a temporary location in the state as doing business there. California offers a temporary seller\'s permit, valid for up to 90 days, for short-lived sales such as Christmas trees or fireworks. Ask the organizer what vendors must show, and apply early enough for the permit to arrive before the show.'],
        ['q' => 'Do I need a sales tax permit if I only sell on Amazon or Etsy?', 'a' => 'Often not in the states where the marketplace collects for you. Texas says a remote seller that sells only through a marketplace provider that certifies it will collect the tax does not need a Texas permit, though it must keep records for four years. If you also sell through your own website, or hold inventory or staff in a state, you may still need a permit there.'],
        ['q' => 'What happens if I sell without a sales tax permit?', 'a' => 'You can owe the tax you should have collected, plus penalties and interest, even though you never charged your customers. In Texas, doing business as a retailer without a required permit is a Class C misdemeanor for a first offense, with each day a separate offense. Florida can add a $100 registration fee. Register as soon as you find the gap.'],
        ['q' => 'Which states do not have a sales tax?', 'a' => 'Five states have no general statewide sales tax: Alaska, Delaware, Montana, New Hampshire and Oregon. That does not mean no tax at all. Many Alaska cities and boroughs levy local sales tax, and remote sellers register through the Alaska Remote Seller Sales Tax Commission. Delaware charges sellers a gross receipts tax instead. A business based in any of the five still needs a permit in other states where it has nexus.'],
    ];
    $sources = [
        ['name' => 'South Dakota v. Wayfair, Inc., 585 U.S. 162 (2018), slip opinion', 'url' => 'https://www.supremecourt.gov/opinions/17pdf/17-494_j4el.pdf'],
        ['name' => 'Texas Comptroller, Sales Tax Permit FAQ', 'url' => 'https://comptroller.texas.gov/taxes/sales/faq/permit.php'],
        ['name' => 'Texas Comptroller, Remote Sellers', 'url' => 'https://comptroller.texas.gov/taxes/sales/remote-sellers.php'],
        ['name' => 'Texas Comptroller, Marketplace Providers and Marketplace Sellers', 'url' => 'https://comptroller.texas.gov/taxes/sales/marketplace-providers-sellers.php'],
        ['name' => 'Tex. Tax Code ch. 151 (151.0242 marketplace providers; 151.201 permits; 151.708 selling without permit)', 'url' => 'https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm'],
        ['name' => 'Tex. Tax Code 111.016 (tax collected held in trust for the state)', 'url' => 'https://tcss.legis.texas.gov/resources/TX/htm/TX.111.htm'],
        ['name' => 'CDTFA, Wayfair Decision tax guide (California $500,000 threshold)', 'url' => 'https://cdtfa.ca.gov/industry/wayfair/'],
        ['name' => 'CDTFA Regulation 1684.5 (marketplace facilitators)', 'url' => 'https://cdtfa.ca.gov/lawguides/vol1/sutr/1684-5.html'],
        ['name' => 'CDTFA Publication 107, Applying for a Seller\'s Permit (temporary permits)', 'url' => 'https://cdtfa.ca.gov/formspubs/pub107/applying-for-a-sellers-permit.htm'],
        ['name' => 'Cal. Rev. & Tax. Code 6051 (sales tax imposed on retailers)', 'url' => 'https://cdtfa.ca.gov/lawguides/vol1/sutl/6051.html'],
        ['name' => 'Fla. Stat. 212.0596 (remote sales; $100,000 threshold)', 'url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0200-0299/0212/Sections/0212.0596.html'],
        ['name' => 'Fla. Stat. 212.18 (registration; $100 fee for failing to register)', 'url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0200-0299/0212/Sections/0212.18.html'],
        ['name' => 'Alaska Remote Seller Sales Tax Commission, FAQs for Sellers', 'url' => 'https://arsstc.org/faqs-for-sellers/'],
        ['name' => 'Delaware Division of Revenue, Gross Receipts Taxes', 'url' => 'https://revenue.delaware.gov/business-tax-forms/doing-business-in-delaware/step-4-gross-receipts-taxes/'],
        ['name' => 'Montana Department of Revenue, General Sales Tax', 'url' => 'https://revenue.mt.gov/taxes/general-sales-tax'],
        ['name' => 'New Hampshire DRA, Does New Hampshire have a sales tax?', 'url' => 'https://www.revenue.nh.gov/faq/does-new-hampshire-have-sales-tax'],
        ['name' => 'Oregon Department of Revenue, Sales Tax in Oregon', 'url' => 'https://www.oregon.gov/dor/programs/businesses/pages/sales-tax.aspx'],
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
        You need a sales tax permit in any state where you sell taxable goods or services and have nexus. Nexus is a
        connection to the state strong enough that it can make you collect its tax. You get it through a physical
        presence, such as an office, inventory, staff or a booth at an event, or by passing the state's economic
        threshold for sales into the state.
    </p>
    <p>
        A sales tax permit (also called a seller's permit or sales tax license) is your registration with the state
        tax agency. Once you hold one, you must collect tax on taxable sales and file returns.
    </p>

    <h2>What creates nexus?</h2>
    <x-guides.table id="nexus-types-table">
        <thead>
            <tr>
                <th scope="col">Type of nexus</th>
                <th scope="col">What triggers it</th>
                <th scope="col">Example</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100">
            <tr>
                <th scope="row">Physical presence</th>
                <td>An office, store, warehouse, inventory, employees or representatives in the state</td>
                <td>A Texas office or a sales rep who takes orders there</td>
            </tr>
            <tr>
                <th scope="row">Economic nexus</th>
                <td>Sales into the state above its threshold, with no physical presence</td>
                <td>More than $500,000 a year into California</td>
            </tr>
            <tr>
                <th scope="row">Temporary presence</th>
                <td>Selling at a trade show, fair or pop-up</td>
                <td>A booth at a one-weekend craft fair</td>
            </tr>
            <tr>
                <th scope="row">Marketplace sales</th>
                <td>Sales through a platform; usually the platform collects</td>
                <td>Selling only through a marketplace that collects Texas tax</td>
            </tr>
        </tbody>
    </x-guides.table>
    <p>Thresholds and rules change. Check each state's own page before you rely on a number.</p>

    <h2>Do you have a physical presence?</h2>
    <p>
        Physical presence is the oldest test. Texas says you are engaged in business there if you have a temporary or
        permanent location in the state, an employee or representative who sells, delivers or takes orders there, or
        people performing services there for you
        (<a href="https://comptroller.texas.gov/taxes/sales/faq/permit.php" rel="noopener" target="_blank">Texas Comptroller permit FAQ</a>).
        Texas also requires a separate permit for each place of business
        (<a href="https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm" rel="noopener" target="_blank">Tex. Tax Code 151.201</a>).
    </p>
    <p>
        Most states use a similar list. If you have a location or people in a state that has a sales tax, assume you
        need a permit there and check that state's rules. Our
        <a href="/sales-tax-registration">sales tax registration</a> pages cover each state's agency and process.
    </p>
    <h3>Selling at events and trade shows</h3>
    <p>
        A booth at a fair, festival or trade show is a physical presence for the days you are there. In Texas, a
        temporary location counts
        (<a href="https://comptroller.texas.gov/taxes/sales/faq/permit.php" rel="noopener" target="_blank">Texas Comptroller permit FAQ</a>).
        California offers a temporary seller's permit for sales of a temporary nature, valid for up to 90 days
        (<a href="https://cdtfa.ca.gov/formspubs/pub107/applying-for-a-sellers-permit.htm" rel="noopener" target="_blank">CDTFA Publication 107</a>).
    </p>
    <p>
        Before the event, ask the organizer whether vendors must show a permit, and apply early enough for it to
        arrive.
    </p>

    <h2>Have you passed a state's economic threshold?</h2>
    <p>
        Since June 21, 2018, a state can require a seller with no physical presence to collect its tax. In <em>South
        Dakota v. Wayfair, Inc.</em>, the Supreme Court overruled the physical presence rule and upheld a South Dakota
        law covering sellers with more than $100,000 of sales or 200 transactions into the state each year
        (<a href="https://www.supremecourt.gov/opinions/17pdf/17-494_j4el.pdf" rel="noopener" target="_blank">South Dakota v. Wayfair</a>).
    </p>
    <p>Each state sets its own threshold, and the numbers differ. Three examples, as of October 2026:</p>
    <ul>
        <li>
            <strong>Texas</strong>: total Texas revenue of $500,000 or more in the preceding twelve calendar months. You
            must get a permit and start collecting no later than the first day of the fourth month after you pass it
            (<a href="https://comptroller.texas.gov/taxes/sales/remote-sellers.php" rel="noopener" target="_blank">Texas Comptroller, Remote Sellers</a>).
        </li>
        <li>
            <strong>California</strong>: combined sales of tangible goods for delivery into California, by you and related
            persons, over $500,000 in the preceding or current calendar year
            (<a href="https://cdtfa.ca.gov/industry/wayfair/" rel="noopener" target="_blank">CDTFA</a>).
        </li>
        <li>
            <strong>Florida</strong>: taxable remote sales into Florida over $100,000 in the previous calendar year
            (<a href="https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&amp;URL=0200-0299/0212/Sections/0212.0596.html" rel="noopener" target="_blank">Fla. Stat. 212.0596</a>).
        </li>
    </ul>
    <p>
        Notice the differences: Texas counts all Texas revenue, taxable or not, while Florida counts only taxable
        remote sales. See the full list in our
        <a href="/guides/economic-nexus-thresholds-by-state">economic nexus thresholds guide</a>.
    </p>

    <h2>What if you sell through a marketplace?</h2>
    <p>
        Where a state has a marketplace rule, the marketplace, not you, collects the tax on the sales it processes. A
        marketplace facilitator (or marketplace provider) is a platform that lists other sellers' goods and processes
        the payment.
    </p>
    <p>
        Texas makes a marketplace provider collect and remit tax on sales it processes for marketplace sellers
        (<a href="https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm" rel="noopener" target="_blank">Tex. Tax Code 151.0242</a>).
        A remote seller who sells only through a provider that has certified it will collect is not required to hold a
        Texas permit, but must keep records of those sales for at least four years
        (<a href="https://comptroller.texas.gov/taxes/sales/marketplace-providers-sellers.php" rel="noopener" target="_blank">Texas Comptroller</a>).
        California has a similar rule for facilitators
        (<a href="https://cdtfa.ca.gov/lawguides/vol1/sutr/1684-5.html" rel="noopener" target="_blank">Regulation 1684.5</a>).
    </p>
    <p>
        If you also sell through your own website, or you store inventory in a state, the marketplace rule may not
        cover you.
    </p>

    <h2>Which states have no general sales tax?</h2>
    <p>Five states have no general statewide sales tax. Each still has rules to know.</p>
    <ul>
        <li>
            <strong>Alaska</strong>: no state sales tax, but many cities and boroughs have one. Remote sellers register
            with the Alaska Remote Seller Sales Tax Commission
            (<a href="https://arsstc.org/faqs-for-sellers/" rel="noopener" target="_blank">ARSSTC</a>).
        </li>
        <li>
            <strong>Delaware</strong>: no state or local sales tax, but a gross receipts tax on sellers of goods and
            services
            (<a href="https://revenue.delaware.gov/business-tax-forms/doing-business-in-delaware/step-4-gross-receipts-taxes/" rel="noopener" target="_blank">Delaware Division of Revenue</a>).
        </li>
        <li>
            <strong>Montana</strong>: no general sales tax
            (<a href="https://revenue.mt.gov/taxes/general-sales-tax" rel="noopener" target="_blank">Montana Department of Revenue</a>).
        </li>
        <li>
            <strong>New Hampshire</strong>: no general sales tax on goods
            (<a href="https://www.revenue.nh.gov/faq/does-new-hampshire-have-sales-tax" rel="noopener" target="_blank">New Hampshire DRA</a>).
        </li>
        <li>
            <strong>Oregon</strong>: no general sales or use tax
            (<a href="https://www.oregon.gov/dor/programs/businesses/pages/sales-tax.aspx" rel="noopener" target="_blank">Oregon Department of Revenue</a>).
        </li>
    </ul>
    <p>A business based in one of these states still needs a permit in any other state where it has nexus.</p>

    <h2>What happens if you get it wrong?</h2>
    <p>
        <strong>Selling without collecting.</strong> The tax is often owed by the seller whether or not you charged
        it. California imposes its sales tax on retailers
        (<a href="https://cdtfa.ca.gov/lawguides/vol1/sutl/6051.html" rel="noopener" target="_blank">Rev. &amp; Tax. Code 6051</a>).
        If you skipped it, the tax can come out of your own pocket.
    </p>
    <p>
        <strong>Selling without a permit.</strong> In Texas, doing business as a retailer without a required permit is
        a Class C misdemeanor for a first offense, rising for repeat offenses
        (<a href="https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm" rel="noopener" target="_blank">Tex. Tax Code 151.708</a>).
        Florida can charge a $100 registration fee to a business that should have registered and did not
        (<a href="https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&amp;URL=0200-0299/0212/Sections/0212.18.html" rel="noopener" target="_blank">Fla. Stat. 212.18</a>).
    </p>
    <p>
        <strong>Collecting without a permit.</strong> Money you collect as tax is not yours. In Texas, anyone who
        collects a tax, or money represented as tax, holds it in trust for the state and is liable for the full amount
        (<a href="https://tcss.legis.texas.gov/resources/TX/htm/TX.111.htm" rel="noopener" target="_blank">Tex. Tax Code 111.016</a>).
        Register, then remit it.
    </p>

    <h2>What to do next</h2>
    <ol>
        <li>List every state where you have a location, staff, inventory or event sales.</li>
        <li>
            Total your sales into each other state and compare them with the
            <a href="/guides/economic-nexus-thresholds-by-state">economic nexus thresholds</a>.
        </li>
        <li>
            Register in each state where you have nexus, using the
            <a href="/guides/sales-tax-registration-checklist">registration checklist</a>. Start with your home state,
            such as <a href="/sales-tax-registration/texas">Texas</a> or
            <a href="/sales-tax-registration/california">California</a>.
        </li>
        <li>
            If you buy inventory to resell, get resale certificates too. See
            <a href="/guides/sellers-permit-vs-resale-certificate">seller's permit vs resale certificate</a>.
        </li>
    </ol>
    <p>
        eRegister can prepare and file your state sales tax
        registration{{ $price ? ' for '.$price.' per state' : '' }}; see
        <a href="/sales-tax-registration">sales tax registration</a>.
    </p>
    <p>
        This guide is general information, not tax advice. If you are unsure whether you have nexus in a state, check
        with the state.
    </p>
</x-guides.article>
@endsection
