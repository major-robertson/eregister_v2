@extends('layouts.landing')

@section('title', $guide['page_title'])
@section('description', $guide['description'])
@section('canonical', \App\Support\Seo\Guides::url($guide['slug']))
@section('og_type', 'article')

@php
    $faq = [
        ['q' => 'Do I need a seller\'s permit to get a resale certificate?', 'a' => 'In most states, yes. A resale certificate usually asks for your sales tax permit number, and Texas requires an 11-digit permit number unless your application is pending or you are an out-of-state retailer. In Florida, the state issues its Annual Resale Certificate only to businesses registered to collect sales tax. Out-of-state buyers can often use their home-state registration number instead.'],
        ['q' => 'Is a seller\'s permit the same as a resale certificate?', 'a' => 'No. The seller\'s permit is your registration with the state tax agency, and it obliges you to collect and remit sales tax on taxable sales. The resale certificate is a document you hand a supplier so you can buy items for resale without paying tax on them. Texas says a copy of your permit is not a substitute for a resale certificate.'],
        ['q' => 'How much does a resale certificate cost?', 'a' => 'Purchaser-completed forms, such as Texas Form 01-339 or California\'s CDTFA-230, are filled out and given to the supplier; you do not file them with the state. Washington\'s Department of Revenue charges nothing for a reseller permit. The cost usually sits with the seller\'s permit instead: as of October 2026, Connecticut charges $100 to register and Arkansas $50, while California and Texas charge no permit fee.'],
        ['q' => 'Can I use a resale certificate from another state?', 'a' => 'Often, but not everywhere. Many states let a supplier accept the Multistate Tax Commission uniform certificate or the Streamlined Sales Tax certificate with your home-state number. Texas accepts the MTC certificate but not the Streamlined certificate for resale, and California needs either its seller\'s permit number or an explanation of why you do not need one. Check the state\'s rules first.'],
        ['q' => 'What happens if I misuse a resale certificate?', 'a' => 'You owe the tax you avoided, and usually a penalty on top. California adds 10 percent of the tax or $500, whichever is greater, for each purchase. Florida adds a 200 percent penalty and treats fraud as a third-degree felony. Texas grades knowing misuse from a Class C misdemeanor to a second-degree felony, depending on the tax evaded.'],
    ];
    $sources = [
        ['name' => 'CDTFA Publication 73, Your California Seller\'s Permit', 'url' => 'https://cdtfa.ca.gov/formspubs/pub73.pdf'],
        ['name' => 'CDTFA Publication 103, Valid Resale Certificates', 'url' => 'https://cdtfa.ca.gov/formspubs/pub103/valid-resale-certificates.htm'],
        ['name' => 'Cal. Code Regs., tit. 18, 1668 (Regulation 1668, Sales for Resale)', 'url' => 'https://cdtfa.ca.gov/lawguides/vol1/sutr/1668.html'],
        ['name' => 'Cal. Rev. & Tax. Code 6094.5 (misuse of resale certificate)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=RTC&sectionNum=6094.5'],
        ['name' => 'Texas Comptroller, Sales Tax Permit FAQ', 'url' => 'https://comptroller.texas.gov/taxes/sales/faq/permit.php'],
        ['name' => 'Texas Comptroller, Resale Certificate FAQ', 'url' => 'https://comptroller.texas.gov/taxes/sales/faq/resale.php'],
        ['name' => 'Texas Form 01-339, Sales and Use Tax Resale Certificate (Rev.4-13/8)', 'url' => 'https://comptroller.texas.gov/forms/01-339.pdf'],
        ['name' => '34 Tex. Admin. Code 3.285 (resale certificate; sales for resale)', 'url' => 'https://www.law.cornell.edu/regulations/texas/34-Tex-Admin-Code-SS-3-285'],
        ['name' => 'Tex. Tax Code 151.707 (resale or exemption certificate; criminal penalty)', 'url' => 'https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm#151.707'],
        ['name' => 'Fla. Stat. 212.085 (fraudulent claim of exemption; penalties)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2025/212.085'],
        ['name' => 'Florida Department of Revenue, Annual Resale Certificate for Sales Tax', 'url' => 'https://floridarevenue.com/taxes/taxesfees/Pages/annual_resale_certificate_sut.aspx'],
        ['name' => 'Washington DOR, Reseller permit: your questions answered', 'url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits/reseller-permit-your-questions-answered'],
        ['name' => 'RCW 82.32.291 (reseller permit misuse penalty)', 'url' => 'https://app.leg.wa.gov/RCW/default.aspx?cite=82.32.291'],
        ['name' => 'Connecticut DRS, Sales and Use Tax Information (registration fee)', 'url' => 'https://portal.ct.gov/drs/sales-tax/tax-information'],
        ['name' => 'Arkansas DFA, Register for a Tax Account (sales tax permit fee)', 'url' => 'https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/register-for-a-tax-account/'],
        ['name' => 'Multistate Tax Commission, Uniform Sales and Use Tax Resale Certificate, Multijurisdiction (rev. October 14, 2022)', 'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf'],
        ['name' => 'Streamlined Sales Tax Certificate of Exemption', 'url' => 'https://www.streamlinedsalestax.org/docs/default-source/forms/exemption-certificateb926a7ab4a0d43e1ad4fe8eb19e79cbb.pdf?sfvrsn=857843d_5'],
        ['name' => 'Streamlined Sales Tax Governing Board, State Information (member states)', 'url' => 'https://www.streamlinedsalestax.org/about-us/state-information'],
    ];
@endphp

@section('content')
<x-guides.article :guide="$guide" :faq="$faq" :sources="$sources">
    <p>
        A seller's permit registers your business with the state so you can collect sales tax from your customers. A
        resale certificate is a document you give your supplier so you can buy inventory without paying sales tax on
        it, because the tax will be collected when you resell. Most businesses that buy goods to resell need both, and
        the permit usually comes first.
    </p>
    <p>
        Names vary. California calls the registration a seller's permit. Texas calls it a sales and use tax permit,
        and many states call it a sales tax license. In this guide, &quot;seller's permit&quot; means any of them.
    </p>

    <h2>What does each document do?</h2>
    <x-guides.table id="permit-vs-certificate-table">
        <thead>
            <tr>
                <td class="bg-zinc-50"></td>
                <th scope="col">Seller's permit</th>
                <th scope="col">Resale certificate</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100">
            <tr>
                <th scope="row">What it does</th>
                <td>Authorizes you to make taxable sales and collect tax</td>
                <td>Lets you buy items for resale without paying tax</td>
            </tr>
            <tr>
                <th scope="row">Who issues it</th>
                <td>The state tax agency</td>
                <td>Usually you fill it out; a few states issue it</td>
            </tr>
            <tr>
                <th scope="row">Who keeps it</th>
                <td>You, displayed at your place of business</td>
                <td>Your supplier keeps it on file</td>
            </tr>
            <tr>
                <th scope="row">Who sees it</th>
                <td>Your customers and the state</td>
                <td>Your suppliers</td>
            </tr>
            <tr>
                <th scope="row">Typical cost</th>
                <td>No fee to about $100, varies by state</td>
                <td>Usually nothing to file</td>
            </tr>
            <tr>
                <th scope="row">Does it expire</th>
                <td>Varies by state; some have no fixed term</td>
                <td>Varies by state</td>
            </tr>
        </tbody>
    </x-guides.table>
    <p>
        The permit comes with duties. Texas lists them: post the permit, collect tax on taxable sales, file returns on
        time even when you had no sales, and keep records
        (<a href="https://comptroller.texas.gov/taxes/sales/faq/permit.php" rel="noopener" target="_blank">Texas Comptroller permit FAQ</a>).
        The certificate has one job. It moves the tax from your purchase to your customer's purchase.
    </p>
    <p>
        The two are not interchangeable. Texas says a customer's permit number, or a copy of the permit, is not a
        substitute for a resale certificate
        (<a href="https://comptroller.texas.gov/taxes/sales/faq/resale.php" rel="noopener" target="_blank">Texas Comptroller resale FAQ</a>).
    </p>

    <h2>Who issues each one?</h2>
    <p>
        The <strong>seller's permit</strong> always comes from the state tax agency, such as the California Department
        of Tax and Fee Administration (CDTFA) or the Texas Comptroller. You apply online, and once approved you get a
        permit number. See our <a href="/sales-tax-registration">sales tax registration</a> pages for each state's
        agency and process.
    </p>
    <p>The <strong>resale certificate</strong> works one of two ways.</p>
    <ul>
        <li>
            <strong>You fill it out.</strong> Most states publish a form that the buyer completes and hands to the
            supplier. Texas uses Form 01-339, which tells you to give it to the supplier and not to send it to the
            Comptroller
            (<a href="https://comptroller.texas.gov/forms/01-339.pdf" rel="noopener" target="_blank">Form 01-339</a>).
            California offers Form CDTFA-230, but any document with the required elements counts
            (<a href="https://cdtfa.ca.gov/lawguides/vol1/sutr/1668.html" rel="noopener" target="_blank">Regulation 1668</a>).
            See our walkthrough of <a href="/guides/how-to-fill-out-texas-form-01-339">Texas Form 01-339</a>.
        </li>
        <li>
            <strong>The state issues it.</strong> Florida issues an Annual Resale Certificate to registered dealers each
            year, and it expires December 31
            (<a href="https://floridarevenue.com/taxes/taxesfees/Pages/annual_resale_certificate_sut.aspx" rel="noopener" target="_blank">Florida DOR</a>).
            Washington issues a reseller permit, generally valid for four years, at no cost
            (<a href="https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits/reseller-permit-your-questions-answered" rel="noopener" target="_blank">Washington DOR</a>).
        </li>
    </ul>
    <p>
        Either way, you usually need the permit first. Texas requires your 11-digit permit number on the certificate,
        or a statement that your application is pending, which is good for only 60 days
        (<a href="https://www.law.cornell.edu/regulations/texas/34-Tex-Admin-Code-SS-3-285" rel="noopener" target="_blank">34 Tex. Admin. Code 3.285</a>).
        California requires your seller's permit number, or an explanation of why you are not required to hold one
        (<a href="https://cdtfa.ca.gov/lawguides/vol1/sutr/1668.html" rel="noopener" target="_blank">Regulation 1668</a>).
    </p>

    <h2>What does each one cost?</h2>
    <p>Seller's permit fees, as of October 2026:</p>
    <ul>
        <li>
            <strong>California</strong>: no fee, though CDTFA may require a security deposit in some cases
            (<a href="https://cdtfa.ca.gov/formspubs/pub73.pdf" rel="noopener" target="_blank">Publication 73</a>).
        </li>
        <li>
            <strong>Texas</strong>: no fee, though you may have to post a security bond
            (<a href="https://comptroller.texas.gov/taxes/sales/faq/permit.php" rel="noopener" target="_blank">Texas Comptroller permit FAQ</a>).
        </li>
        <li>
            <strong>Connecticut</strong>: $100 to register to collect sales and use tax
            (<a href="https://portal.ct.gov/drs/sales-tax/tax-information" rel="noopener" target="_blank">Connecticut DRS</a>).
        </li>
        <li>
            <strong>Arkansas</strong>: $50, paid when you submit the application
            (<a href="https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/register-for-a-tax-account/" rel="noopener" target="_blank">Arkansas DFA</a>).
        </li>
    </ul>
    <p>
        Resale certificates are different. Where you fill out the form yourself, there is nothing to file and no fee
        to the state. Washington says there is no cost for a reseller permit
        (<a href="https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits/reseller-permit-your-questions-answered" rel="noopener" target="_blank">Washington DOR</a>).
        Each state's <a href="/resale-certificates">resale certificate page</a> lists its own form and rules.
    </p>

    <h2>What a seller must keep on file</h2>
    <p>
        If you sell to other businesses, the certificate protects you, not the buyer. Without one, the state treats
        the sale as taxable.
    </p>
    <p>
        Texas presumes every sale is taxable unless you accept a properly completed resale or exemption certificate.
        You are protected if you take it in good faith, at or before the sale, and do not know the sale is not for
        resale. Keep it at least four years from the date of the sale. A blanket certificate covers future purchases
        until the buyer revokes it in writing
        (<a href="https://www.law.cornell.edu/regulations/texas/34-Tex-Admin-Code-SS-3-285" rel="noopener" target="_blank">34 Tex. Admin. Code 3.285</a>).
        The rule gives an example: a jewelry seller should know a landscaping company is not in the business of
        reselling jewelry.
    </p>
    <p>
        California treats a document as a valid certificate if it has the buyer's signature, name and address,
        seller's permit number, a description of the property and the date
        (<a href="https://cdtfa.ca.gov/lawguides/vol1/sutr/1668.html" rel="noopener" target="_blank">Regulation 1668</a>).
        Check each buyer's permit with the state's lookup tool before you rely on it.
    </p>

    <h2>What happens if you misuse a resale certificate?</h2>
    <p>
        A resale certificate is a promise that you will resell what you bought. If you use the item yourself, you owe
        the tax. If you misuse the certificate on purpose, states add penalties.
    </p>
    <ul>
        <li>
            <strong>California</strong>: the tax, plus a penalty of 10 percent of the tax or $500, whichever is greater,
            for each purchase made for personal gain or to evade tax
            (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=RTC&amp;sectionNum=6094.5" rel="noopener" target="_blank">Rev. &amp; Tax. Code 6094.5</a>).
            CDTFA notes that your permit can also be revoked
            (<a href="https://cdtfa.ca.gov/formspubs/pub103/valid-resale-certificates.htm" rel="noopener" target="_blank">Publication 103</a>).
        </li>
        <li>
            <strong>Florida</strong>: the tax, plus a mandatory penalty of 200 percent of the tax, and a third-degree
            felony for a fraudulent claim
            (<a href="https://www.flsenate.gov/Laws/Statutes/2025/212.085" rel="noopener" target="_blank">Fla. Stat. 212.085</a>).
        </li>
        <li>
            <strong>Texas</strong>: a crime graded by the tax avoided, from a Class C misdemeanor under $20 to a
            second-degree felony at $20,000 or more
            (<a href="https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm#151.707" rel="noopener" target="_blank">Tex. Tax Code 151.707</a>).
        </li>
        <li>
            <strong>Washington</strong>: a penalty of 50 percent of the tax due on the improper purchase, on top of the
            tax and interest
            (<a href="https://app.leg.wa.gov/RCW/default.aspx?cite=82.32.291" rel="noopener" target="_blank">RCW 82.32.291</a>).
        </li>
    </ul>

    <h2>Can you use one certificate in many states?</h2>
    <p>Two multistate forms exist. Neither works everywhere.</p>
    <p>
        The <strong>Multistate Tax Commission (MTC) Uniform Sales and Use Tax Resale Certificate</strong> lists 37
        states plus Alaska's remote seller commission as accepting it, many with conditions in its footnotes
        (<a href="https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf" rel="noopener" target="_blank">MTC certificate, rev. October 14, 2022</a>).
        Florida, for example, also requires the seller to get an authorization number using your Florida resale
        certificate number. States not on the list include Indiana, Louisiana, Massachusetts, Mississippi, New York,
        West Virginia, Wyoming and the District of Columbia.
    </p>
    <p>
        The <strong>Streamlined Sales Tax (SST) Certificate of Exemption</strong> covers resale and other exemptions
        (<a href="https://www.streamlinedsalestax.org/docs/default-source/forms/exemption-certificateb926a7ab4a0d43e1ad4fe8eb19e79cbb.pdf?sfvrsn=857843d_5" rel="noopener" target="_blank">SST certificate</a>).
        It fits the 23 full member states and Tennessee, an associate member
        (<a href="https://www.streamlinedsalestax.org/about-us/state-information" rel="noopener" target="_blank">SST state information</a>).
        Texas accepts the MTC form but says the SST certificate may not be accepted as a resale certificate
        (<a href="https://www.law.cornell.edu/regulations/texas/34-Tex-Admin-Code-SS-3-285" rel="noopener" target="_blank">34 Tex. Admin. Code 3.285</a>).
    </p>

    <h2>What to do next</h2>
    <ol>
        <li>
            Find out whether your sales create a duty to register. Our guide on
            <a href="/guides/when-do-you-need-a-sales-tax-permit">when you need a sales tax permit</a> covers physical
            presence and economic nexus.
        </li>
        <li>
            Register in each state where you must collect. The
            <a href="/guides/sales-tax-registration-checklist">registration checklist</a> lists what the application asks
            for.
        </li>
        <li>
            Give each supplier the right certificate for its state, such as the
            <a href="/resale-certificates/texas">Texas</a> or <a href="/resale-certificates/california">California</a>
            form.
        </li>
    </ol>
    <p>
        eRegister prepares and files sales tax permit applications if you would rather not do it yourself; see
        <a href="/sales-tax-registration">sales tax registration</a>.
    </p>
    <p>This guide is general information, not tax advice. For a specific transaction, check with the state.</p>
</x-guides.article>
@endsection
