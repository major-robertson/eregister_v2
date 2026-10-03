@extends('layouts.landing')

@section('title', $guide['page_title'])
@section('description', $guide['description'])
@section('canonical', \App\Support\Seo\Guides::url($guide['slug']))
@section('og_type', 'article')

@php
    $faq = [
        ['q' => 'Can I file a mechanics lien myself?', 'a' => 'In most states, yes. The claimant or its agent prepares the claim, signs it under oath or verification, and records it with the county office. Maryland is the main exception: a lien there is established by a petition in circuit court, which is a lawsuit (Md. Code, Real Prop. § 9-105). Enforcing any lien also takes a lawsuit, so plan on counsel for that step.'],
        ['q' => 'How long do I have to file a mechanics lien?', 'a' => 'It depends on the state and your role. Florida allows 90 days after your final furnishing. New York allows eight months, or four months for a single-family dwelling. Texas counts in months: the 15th day of the 4th month after the month you last furnished, or the 3rd month on residential work. Check your state page or the deadline calculator.'],
        ['q' => 'Does a mechanics lien have to be notarized?', 'a' => 'Most states require the claim to be sworn, which usually means signing before a notary. Texas and Florida both require a sworn claim. California is different: the claimant verifies the claim under penalty of perjury, and a properly verified claim is recorded without a notary acknowledgment (Cal. Civ. Code § 8416). New York requires a verified notice of lien (N.Y. Lien Law § 9).'],
        ['q' => 'What happens after I file a mechanics lien?', 'a' => 'You serve a copy on the owner, often within days: Texas allows 5 days after filing, Georgia two business days. The lien then sits on the property\'s title. If you are not paid, you must sue to enforce it before it expires: 90 days after recording in California, one year in Florida and New York. When you are paid, you release it.'],
        ['q' => 'Can I file a mechanics lien on a public project?', 'a' => 'No. Public property generally cannot be liened. On public work, unpaid subcontractors and suppliers claim against the prime contractor\'s payment bond instead. Bond claims have their own notice and suit deadlines. In Texas, for example, the bond claim notice is due by the 15th day of the 3rd month after each month you performed work or delivered material (Tex. Gov\'t Code § 2253.041).'],
    ];
    $sources = [
        ['name' => 'Tex. Prop. Code ch. 53 (§§ 53.021, 53.052, 53.054, 53.055, 53.056, 53.152, 53.158)', 'url' => 'https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm'],
        ['name' => 'Tex. Gov\'t Code ch. 2253 (§ 2253.041, public work payment bond notice)', 'url' => 'https://tcss.legis.texas.gov/resources/GV/htm/GV.2253.htm'],
        ['name' => 'Cal. Civ. Code § 8060 (recording with the county recorder)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8060'],
        ['name' => 'Cal. Civ. Code § 8204 (preliminary notice, 20 days)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8204'],
        ['name' => 'Cal. Civ. Code § 8414 (recording deadline for claimants other than direct contractors)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8414'],
        ['name' => 'Cal. Civ. Code § 8416 (contents, verification, service on owner)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8416'],
        ['name' => 'Cal. Civ. Code § 8460 (action to enforce within 90 days)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8460'],
        ['name' => 'Fla. Stat. § 713.06 (notice to owner, 45 days)', 'url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0700-0799/0713/Sections/0713.06.html'],
        ['name' => 'Fla. Stat. § 713.08 (claim of lien, recording, service)', 'url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0700-0799/0713/Sections/0713.08.html'],
        ['name' => 'Fla. Stat. § 713.22 (duration of lien)', 'url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0700-0799/0713/Sections/0713.22.html'],
        ['name' => 'N.Y. Lien Law § 9 (contents and verification of notice of lien)', 'url' => 'https://www.nysenate.gov/legislation/laws/LIE/9'],
        ['name' => 'N.Y. Lien Law § 10 (filing deadline)', 'url' => 'https://www.nysenate.gov/legislation/laws/LIE/10'],
        ['name' => 'N.Y. Lien Law § 11 (service on owner, proof within 35 days)', 'url' => 'https://www.nysenate.gov/legislation/laws/LIE/11'],
        ['name' => 'N.Y. Lien Law § 17 (duration of lien)', 'url' => 'https://www.nysenate.gov/legislation/laws/LIE/17'],
        ['name' => 'N.Y. Lien Law § 39 (wilfully exaggerated lien is void)', 'url' => 'https://www.nysenate.gov/legislation/laws/LIE/39'],
        ['name' => 'O.C.G.A. § 44-14-361.1 (filing, copy to owner, lien action)', 'url' => 'https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-361-1/'],
        ['name' => 'O.C.G.A. § 44-14-367 (395-day expiration statement in 12-point bold)', 'url' => 'https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-367/'],
        ['name' => 'C.R.S. § 38-22-109 (notice of intent, ten days)', 'url' => 'https://codes.findlaw.com/co/title-38-property-real-and-personal/co-rev-st-sect-38-22-109/'],
        ['name' => 'Md. Code, Real Prop. § 9-105 (petition to establish lien)', 'url' => 'https://mgaleg.maryland.gov/mgawebsite/laws/StatuteText?article=grp&section=9-105&enactments=false'],
    ];
@endphp

@section('content')
<x-guides.article :guide="$guide" :faq="$faq" :sources="$sources">
    <p>
        To file a mechanics lien, you send any notices your state requires, prepare a claim that states what you are
        owed and on what property, sign it under oath, and record it with the right county office before your
        deadline. Then you serve a copy on the owner. If you are still not paid, you sue to enforce the lien before it
        expires.
    </p>

    <h2>What are the steps to file a mechanics lien?</h2>
    <p>
        A mechanics lien is a claim against real property that secures payment for the labor or materials that
        improved it. Every state has one, and the basic steps are the same everywhere. What changes from state to
        state is the deadlines, the notices and a few details. The examples in this guide come from Texas, California,
        Florida, New York and Georgia.
    </p>
    <x-guides.table id="lien-filing-steps-table">
        <thead>
            <tr>
                <th scope="col">Step</th>
                <th scope="col">What you do</th>
                <th scope="col">States that add or change it (examples)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100">
            <tr>
                <th scope="row">1. Confirm lien rights</th>
                <td>Check that the statute covers your role and that the property is private</td>
                <td>Texas excludes suppliers to suppliers</td>
            </tr>
            <tr>
                <th scope="row">2. Preliminary notice</th>
                <td>Notify the owner early in the job</td>
                <td>California (20 days), Florida (45 days), Texas (monthly notice)</td>
            </tr>
            <tr>
                <th scope="row">3. Notice of intent</th>
                <td>Warn the owner shortly before filing</td>
                <td>Colorado (at least 10 days before filing)</td>
            </tr>
            <tr>
                <th scope="row">4. Prepare the claim</th>
                <td>List the parties, the work, the property and the amount</td>
                <td>Georgia requires a bold expiration statement</td>
            </tr>
            <tr>
                <th scope="row">5. Sign under oath</th>
                <td>Swear to or verify the claim</td>
                <td>California uses verification, not a notary</td>
            </tr>
            <tr>
                <th scope="row">6. Record it</th>
                <td>File with the county office before the deadline</td>
                <td>Maryland uses a court petition</td>
            </tr>
            <tr>
                <th scope="row">7. Serve the owner</th>
                <td>Send a copy within the statutory window</td>
                <td>New York requires proof of service within 35 days</td>
            </tr>
            <tr>
                <th scope="row">8. Enforce or release</th>
                <td>Sue before the lien expires, or release it once paid</td>
                <td>California gives you 90 days to sue</td>
            </tr>
        </tbody>
    </x-guides.table>

    <h2>Check your lien rights and send the required notices</h2>
    <p>
        Lien rights come from statute, so first confirm the statute covers you. Contractors, subcontractors and most
        suppliers are covered. Some are not. In Texas, a company that only sells to a material supplier is outside the
        chain the statute protects, so it has no lien
        (<a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm" rel="noopener" target="_blank">Tex. Prop. Code § 53.021</a>).
        Public property generally cannot be liened at all. On public work you claim against the contractor's payment
        bond instead.
    </p>
    <p>
        Next, find your <strong>last furnishing date</strong>. That is the last day you performed work or delivered
        materials on the job. Most filing deadlines count from it, or from completion of the whole project.
    </p>
    <p>
        Many states also make you send notices before you can file. Missing one can end your lien rights, even if you
        file on time.
    </p>
    <ul>
        <li>
            A <strong>preliminary notice</strong> goes out early in the job and tells the owner who you are. In California
            you serve it within 20 days after you first furnish work. A late notice only protects work from the 20 days
            before it was served onward
            (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8204" rel="noopener" target="_blank">Cal. Civ. Code § 8204</a>).
            Florida's notice to owner is due within 45 days after you start
            (<a href="https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&amp;URL=0700-0799/0713/Sections/0713.06.html" rel="noopener" target="_blank">Fla. Stat. § 713.06</a>).
            In Texas, subcontractors and suppliers send a notice of claim for each unpaid month
            (<a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm" rel="noopener" target="_blank">Tex. Prop. Code § 53.056</a>).
        </li>
        <li>
            A <strong>notice of intent to lien</strong> is a warning sent shortly before you file. Colorado requires one,
            served at least ten days before the lien statement is filed
            (<a href="https://codes.findlaw.com/co/title-38-property-real-and-personal/co-rev-st-sect-38-22-109/" rel="noopener" target="_blank">C.R.S. § 38-22-109(3)</a>).
        </li>
    </ul>
    <p>
        The <a href="/guides/preliminary-notice-requirements-by-state">preliminary notice requirements by state</a>
        guide lists who must send one where. Our guide to the
        <a href="/guides/notice-of-intent-to-lien-explained">notice of intent to lien</a> covers the states that
        require a warning first.
    </p>

    <h2>Find your filing deadline</h2>
    <p>Each state sets its own filing window, and it is short.</p>
    <ul>
        <li>
            <strong>Florida:</strong> no later than 90 days after your final furnishing
            (<a href="https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&amp;URL=0700-0799/0713/Sections/0713.08.html" rel="noopener" target="_blank">Fla. Stat. § 713.08(5)</a>).
        </li>
        <li>
            <strong>New York:</strong> within eight months after the last work or materials, or four months for a
            single-family dwelling
            (<a href="https://www.nysenate.gov/legislation/laws/LIE/10" rel="noopener" target="_blank">N.Y. Lien Law § 10</a>).
        </li>
        <li>
            <strong>Texas:</strong> by the 15th day of the 4th month after the month you last furnished, or the 3rd month
            on residential projects
            (<a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm" rel="noopener" target="_blank">Tex. Prop. Code § 53.052</a>).
        </li>
        <li>
            <strong>California:</strong> for subcontractors and suppliers, before the earlier of 90 days after completion
            of the whole project or 30 days after the owner records a notice of completion
            (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8414" rel="noopener" target="_blank">Cal. Civ. Code § 8414</a>).
        </li>
        <li>
            <strong>Georgia:</strong> within 90 days after you completed the work or furnished the materials
            (<a href="https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-361-1/" rel="noopener" target="_blank">O.C.G.A. § 44-14-361.1</a>).
        </li>
    </ul>
    <p>
        See <a href="/guides/mechanics-lien-deadlines-by-state">mechanics lien deadlines by state</a> for all 50, or
        run your dates through the <a href="/liens/deadline-calculator">lien deadline calculator</a>.
    </p>

    <h2>Prepare and sign the claim</h2>
    <p>
        The claim goes by different names: lien affidavit in Texas, claim of lien in Florida, notice of lien in New
        York. The contents are similar everywhere:
    </p>
    <ul>
        <li>Your name and address</li>
        <li>The owner's name</li>
        <li>Who hired you</li>
        <li>A description of the work or materials</li>
        <li>A description of the property, often the legal description from the deed</li>
        <li>The amount still owed, after credits</li>
        <li>In many states, your first and last furnishing dates</li>
    </ul>
    <p>
        Some states add required wording. A Georgia claim must state, in at least 12-point bold type, that it expires
        395 days after filing unless a notice of the lien action is filed
        (<a href="https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-367/" rel="noopener" target="_blank">O.C.G.A. § 44-14-367</a>).
    </p>
    <p>
        Most states want the claim sworn. Texas requires a sworn statement of the amount
        (<a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm" rel="noopener" target="_blank">§ 53.054</a>),
        and New York requires a verified notice
        (<a href="https://www.nysenate.gov/legislation/laws/LIE/9" rel="noopener" target="_blank">N.Y. Lien Law § 9</a>).
        In California the claimant verifies the claim under penalty of perjury instead
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8416" rel="noopener" target="_blank">Cal. Civ. Code § 8416</a>).
    </p>
    <p>
        Claim only what you are owed. In New York, a court that finds the amount wilfully exaggerated declares the
        lien void
        (<a href="https://www.nysenate.gov/legislation/laws/LIE/39" rel="noopener" target="_blank">N.Y. Lien Law § 39</a>).
    </p>

    <h2>Record the claim and serve the owner</h2>
    <p>
        Record the claim in the county where the property sits. California claims go to the county recorder
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8060" rel="noopener" target="_blank">Cal. Civ. Code § 8060</a>),
        Texas affidavits to the county clerk, and Georgia claims to the clerk of superior court. Maryland is the
        outlier: you establish the lien by filing a petition in circuit court
        (<a href="https://mgaleg.maryland.gov/mgawebsite/laws/StatuteText?article=grp&amp;section=9-105&amp;enactments=false" rel="noopener" target="_blank">Md. Code, Real Prop. § 9-105</a>).
    </p>
    <p>Then send the owner a copy. The windows are tight:</p>
    <ul>
        <li>
            <strong>Texas:</strong> within 5 days after filing, to the owner and, if you are not the original contractor,
            to the original contractor (§ 53.055).
        </li>
        <li>
            <strong>Georgia:</strong> within two business days, by registered or certified mail or statutory overnight
            delivery.
        </li>
        <li><strong>Florida:</strong> before recording or within 15 days after (§ 713.08(4)(c)).</li>
        <li>
            <strong>New York:</strong> between 5 days before and 30 days after filing. File proof of service within 35
            days, or the notice ends as a lien
            (<a href="https://www.nysenate.gov/legislation/laws/LIE/11" rel="noopener" target="_blank">N.Y. Lien Law § 11</a>).
        </li>
    </ul>

    <h2>Enforce the lien, or release it</h2>
    <p>A lien does not last forever. If you are not paid, you must sue to foreclose it before it expires.</p>
    <ul>
        <li>
            <strong>California:</strong> within 90 days after recording
            (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8460" rel="noopener" target="_blank">§ 8460</a>).
        </li>
        <li><strong>Texas:</strong> by the first anniversary of the last day you could have filed (§ 53.158).</li>
        <li>
            <strong>Florida:</strong> within one year after recording
            (<a href="https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&amp;URL=0700-0799/0713/Sections/0713.22.html" rel="noopener" target="_blank">§ 713.22</a>).
        </li>
        <li>
            <strong>New York:</strong> within one year after filing, unless you file an extension, which is not available
            on a single-family dwelling
            (<a href="https://www.nysenate.gov/legislation/laws/LIE/17" rel="noopener" target="_blank">N.Y. Lien Law § 17</a>).
        </li>
        <li>
            <strong>Georgia:</strong> within 365 days after filing. File a notice of the lien action with the clerk within
            30 days after you start the suit.
        </li>
    </ul>
    <p>
        When you are paid, release the lien. In Texas you must give a release within 10 days after a written request
        (§ 53.152). Our guide on <a href="/guides/how-to-release-a-mechanics-lien">how to release a mechanics lien</a>
        has the rules for 20 states.
    </p>

    <h2>What to do next</h2>
    <p>
        Start with your state's page, such as <a href="/liens/texas">Texas</a>,
        <a href="/liens/california">California</a>, <a href="/liens/florida">Florida</a>,
        <a href="/liens/new-york">New York</a> or <a href="/liens/georgia">Georgia</a>. Each one lists the notices,
        the deadlines and the filing office. Write down your last furnishing date today and count forward from it.
    </p>
    <p>
        If you would rather not prepare the documents yourself, eRegister's
        <a href="/liens">mechanics lien service</a> prepares and records the claim for you.
    </p>
</x-guides.article>
@endsection
