@extends('layouts.landing')

@section('title', $guide['page_title'])
@section('description', $guide['description'])
@section('canonical', \App\Support\Seo\Guides::url($guide['slug']))
@section('og_type', 'article')

@php
    $faq = [
        ['q' => 'What happens if you miss a mechanics lien deadline?', 'a' => 'You usually lose the lien. California, for example, says a claimant may not enforce a lien unless the claim is recorded within the statutory period. Losing the lien does not erase the debt. You can still send a demand letter, sue on your contract, or make a payment bond claim on public work, each with its own deadlines. Confirm your options with counsel quickly.'],
        ['q' => 'Can a mechanics lien deadline be extended?', 'a' => 'The filing deadline rarely can. A few states allow narrow extensions of other deadlines. A Nevada residential notice of intent adds 15 days to the recording deadline. In Texas, a recorded written agreement with the owner can extend the time to sue to two years after filing. In New York, a filed extension adds up to one more year, except on a single-family dwelling. Check your statute before relying on any of these.'],
        ['q' => 'If my lien deadline falls on a weekend, do I get until Monday?', 'a' => 'Only if your statute says so. Florida extends a deadline that falls on a weekend or holiday to the end of the next business day (Fla. Stat. § 713.011). Texas extends it to the next day that is not a weekend or legal holiday (Tex. Prop. Code § 53.003(e)). Other states differ, so file early rather than count on a rollover.'],
        ['q' => 'Is a late preliminary notice worthless?', 'a' => 'Not always. In California, a notice served late still protects work you furnished in the 20 days before you served it, and everything after (Cal. Civ. Code § 8204). You lose only the earlier work. In other states a late notice can bar the lien entirely. If you are late, send the notice now and check your state\'s rule.'],
        ['q' => 'How do I avoid missing a lien deadline?', 'a' => 'Calendar every deadline the day a job starts, not when payment is late. Send the preliminary notice on every job where it is required. Log your last day of work or delivery for each job. File the lien well before the last day, and calendar the suit deadline the day you record. A deadline calculator helps you check the math.'],
    ];
    $sources = [
        ['name' => 'Cal. Civ. Code § 8204 (preliminary notice, 20 days)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8204'],
        ['name' => 'Cal. Civ. Code § 8414 (recording deadline; notice of completion)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8414'],
        ['name' => 'Cal. Civ. Code § 8416 (verification of claim)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8416'],
        ['name' => 'Cal. Civ. Code § 8460 (action to enforce within 90 days; credit extension)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8460'],
        ['name' => 'Fla. Stat. § 713.06 (notice to owner, 45 days)', 'url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0700-0799/0713/Sections/0713.06.html'],
        ['name' => 'Fla. Stat. § 713.08 (90 days after final furnishing; record in each county)', 'url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0700-0799/0713/Sections/0713.08.html'],
        ['name' => 'Fla. Stat. § 713.011 (weekend and holiday deadlines)', 'url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0700-0799/0713/Sections/0713.011.html'],
        ['name' => 'Tex. Prop. Code ch. 53 (§§ 53.003(e), 53.052, 53.054, 53.158)', 'url' => 'https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm'],
        ['name' => 'Tex. Gov\'t Code ch. 2253 (§§ 2253.041, 2253.078, public work payment bonds)', 'url' => 'https://tcss.legis.texas.gov/resources/GV/htm/GV.2253.htm'],
        ['name' => 'N.Y. Lien Law § 9 (verified notice of lien)', 'url' => 'https://www.nysenate.gov/legislation/laws/LIE/9'],
        ['name' => 'N.Y. Lien Law § 10 (eight months; four months for a single-family dwelling)', 'url' => 'https://www.nysenate.gov/legislation/laws/LIE/10'],
        ['name' => 'N.Y. Lien Law § 11 (proof of service within 35 days)', 'url' => 'https://www.nysenate.gov/legislation/laws/LIE/11'],
        ['name' => 'N.Y. Lien Law § 17 (duration and extension)', 'url' => 'https://www.nysenate.gov/legislation/laws/LIE/17'],
        ['name' => 'KRS 376.010(5) (owner-occupied homes, 75 days)', 'url' => 'https://apps.legislature.ky.gov/law/statutes/statute.aspx?id=54156'],
        ['name' => 'KRS 376.080 (copy to owner within seven days)', 'url' => 'https://apps.legislature.ky.gov/law/statutes/statute.aspx?id=35289'],
        ['name' => 'O.C.G.A. § 44-14-361.1 (90 days after completion of the work)', 'url' => 'https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-361-1/'],
        ['name' => 'O.C.G.A. § 44-14-367 (lien action within 365 days)', 'url' => 'https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-367/'],
        ['name' => 'NRS 108.226(6), (7) (residential notice of intent)', 'url' => 'https://www.leg.state.nv.us/nrs/nrs-108.html'],
        ['name' => 'Md. Code, Real Prop. § 9-105 (petition in circuit court)', 'url' => 'https://mgaleg.maryland.gov/mgawebsite/laws/StatuteText?article=grp&section=9-105&enactments=false'],
        ['name' => '40 U.S.C. § 3133 (Miller Act payment bond claims)', 'url' => 'https://www.govinfo.gov/content/pkg/USCODE-2024-title40/html/USCODE-2024-title40-subtitleII-partA-chap31-subchapIII-sec3133.htm'],
    ];
@endphp

@section('content')
<x-guides.article :guide="$guide" :faq="$faq" :sources="$sources">
    <p>
        If you missed a mechanics lien deadline, the lien is usually gone. Most statutes say a late claim cannot be
        enforced. You may still get paid through a demand letter, a suit on your contract, or a bond claim on public
        work.
    </p>
    <p>
        A <strong>mechanics lien</strong> is a claim against real property that secures payment for the work or
        materials that improved it. Below are seven common ways claimants lose one, each with a state example, and
        what you can still do. Your <a href="/liens">state lien page</a> has the exact rules where you work.
    </p>

    <h2>How do claimants lose lien rights?</h2>
    <x-guides.table id="lien-deadline-mistakes-table">
        <thead>
            <tr>
                <th scope="col">Mistake</th>
                <th scope="col">Example state</th>
                <th scope="col">The rule</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100">
            <tr>
                <th scope="row">Late preliminary notice</th>
                <td>California</td>
                <td>
                    Serve within 20 days after first furnishing; a late notice covers only the prior 20 days and after
                </td>
            </tr>
            <tr>
                <th scope="row">Wrong trigger date</th>
                <td>California</td>
                <td>A recorded notice of completion cuts the window to 30 days for subcontractors and suppliers</td>
            </tr>
            <tr>
                <th scope="row">Counting from the invoice</th>
                <td>Texas</td>
                <td>The deadline is the 15th day of the 4th month after the month of last furnishing</td>
            </tr>
            <tr>
                <th scope="row">Missing residential rules</th>
                <td>New York</td>
                <td>Four months, not eight, for a single-family dwelling</td>
            </tr>
            <tr>
                <th scope="row">No oath or verification</th>
                <td>Texas</td>
                <td>The affidavit needs a sworn statement of the amount</td>
            </tr>
            <tr>
                <th scope="row">Wrong office or county</th>
                <td>Florida</td>
                <td>Record in the county where the property is, and in each county if it spans more than one</td>
            </tr>
            <tr>
                <th scope="row">Missed post-filing step or suit deadline</th>
                <td>New York, California</td>
                <td>Proof of service within 35 days; suit within 90 days of recording</td>
            </tr>
        </tbody>
    </x-guides.table>

    <h2>Sending the preliminary notice late</h2>
    <p>
        A <strong>preliminary notice</strong> tells the owner, early in the job, that you are working there. In many
        states it is a condition of the lien. California requires it within 20 days after you first furnish work. A
        late notice still works, but only for work from the 20 days before it was served onward
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8204" rel="noopener" target="_blank">Cal. Civ. Code § 8204</a>).
        Florida's notice to owner is due before you start or within 45 days after, and before the owner's final
        payment
        (<a href="https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&amp;URL=0700-0799/0713/Sections/0713.06.html" rel="noopener" target="_blank">Fla. Stat. § 713.06</a>).
        See the <a href="/liens/preliminary-notice">preliminary notice</a> page for what it takes.
    </p>

    <h2>Using the wrong date to count from</h2>
    <h3>Counting from the wrong event</h3>
    <p>
        Deadlines do not all run from your own last day. In California, a subcontractor or supplier records before the
        earlier of 90 days after completion of the whole project or 30 days after the owner records a notice of
        completion
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8414" rel="noopener" target="_blank">Cal. Civ. Code § 8414</a>).
        If you do not check the county records, the 30-day window can close before you notice. Georgia counts 90 days
        after you completed the work or furnished the materials
        (<a href="https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-361-1/" rel="noopener" target="_blank">O.C.G.A. § 44-14-361.1</a>).
    </p>
    <h3>Counting from the invoice instead of last furnishing</h3>
    <p>
        Your <strong>last furnishing date</strong> is the last day you performed work or delivered materials on the
        job. It is not your invoice date, and it is not the date payment was due. Florida runs 90 days from your final
        furnishing
        (<a href="https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&amp;URL=0700-0799/0713/Sections/0713.08.html" rel="noopener" target="_blank">Fla. Stat. § 713.08(5)</a>).
        Texas does not count days at all: the deadline is the 15th day of the 4th month after the month you last
        furnished
        (<a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm" rel="noopener" target="_blank">Tex. Prop. Code § 53.052</a>).
        Run your dates through the <a href="/liens/deadline-calculator">lien deadline calculator</a>.
    </p>

    <h2>Missing the residential rules</h2>
    <p>
        Homes often get shorter deadlines or extra steps. In New York the notice of lien is due within eight months,
        but four months for a single-family dwelling
        (<a href="https://www.nysenate.gov/legislation/laws/LIE/10" rel="noopener" target="_blank">N.Y. Lien Law § 10</a>).
        In Texas, residential projects move the affidavit deadline from the 4th month to the 3rd (§ 53.052). Nevada
        requires a 15-day notice of intent on residential work only
        (<a href="https://www.leg.state.nv.us/nrs/nrs-108.html" rel="noopener" target="_blank">NRS 108.226(6)</a>). In
        Kentucky, a subcontractor on an owner-occupied one- or two-family home must notify the owner within 75 days
        after last furnishing
        (<a href="https://apps.legislature.ky.gov/law/statutes/statute.aspx?id=54156" rel="noopener" target="_blank">KRS 376.010(5)</a>).
    </p>

    <h2>Skipping the oath, or filing in the wrong place</h2>
    <p>
        Most states require a sworn or verified claim. The Texas affidavit must contain a sworn statement of the
        amount (§ 53.054). New York requires the notice of lien to be verified
        (<a href="https://www.nysenate.gov/legislation/laws/LIE/9" rel="noopener" target="_blank">N.Y. Lien Law § 9</a>).
        California requires the claimant to verify the claim
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8416" rel="noopener" target="_blank">Cal. Civ. Code § 8416</a>).
    </p>
    <p>
        File where the property is. In Florida, if the property lies in two or more counties, record the claim in each
        (§ 713.08(5)). In Maryland you do not record a claim at all: you file a petition in the circuit court for the
        county where the land is, within 180 days
        (<a href="https://mgaleg.maryland.gov/mgawebsite/laws/StatuteText?article=grp&amp;section=9-105&amp;enactments=false" rel="noopener" target="_blank">Md. Code, Real Prop. § 9-105</a>).
    </p>

    <h2>Missing the steps after you file</h2>
    <p>
        Filing on time is not the end. In New York, you must file proof of service on the owner within 35 days after
        filing, or the notice ends as a lien
        (<a href="https://www.nysenate.gov/legislation/laws/LIE/11" rel="noopener" target="_blank">N.Y. Lien Law § 11</a>).
        In Kentucky, a copy of the statement goes to the owner within 7 days after filing, or the lien is dissolved
        (<a href="https://apps.legislature.ky.gov/law/statutes/statute.aspx?id=35289" rel="noopener" target="_blank">KRS 376.080</a>).
    </p>
    <p>
        Then the lien expires unless you sue. California allows 90 days after recording
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8460" rel="noopener" target="_blank">Cal. Civ. Code § 8460</a>).
        New York allows one year, which you can extend once by filing an extension, except on a single-family dwelling
        (<a href="https://www.nysenate.gov/legislation/laws/LIE/17" rel="noopener" target="_blank">N.Y. Lien Law § 17</a>).
        Georgia allows 365 days, plus a notice of the lien action filed with the clerk
        (<a href="https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-367/" rel="noopener" target="_blank">O.C.G.A. § 44-14-367</a>).
    </p>

    <h2>What can you do after a missed deadline?</h2>
    <p>
        First, check that it really passed. Recount from the correct trigger. A late California notice may still cover
        your later work. Florida and Texas move a deadline that falls on a weekend or holiday to the next business day
        (<a href="https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&amp;URL=0700-0799/0713/Sections/0713.011.html" rel="noopener" target="_blank">Fla. Stat. § 713.011</a>;
        Tex. Prop. Code § 53.003(e)).
    </p>
    <p>If it did pass, the lien is gone but the debt is not.</p>
    <ul>
        <li>
            <strong>Send a demand letter.</strong> A firm written demand often gets a response. eRegister's
            <a href="/liens/payment-demand-letter">payment demand letter</a> is drafted and mailed for
            {{ \App\Support\Seo\Prices::lien('demand_letter', 'full_service') }}.
        </li>
        <li>
            <strong>Sue on the contract.</strong> You can still sue whoever hired you for the unpaid balance. Contract
            claims have their own limitation periods under state law, so confirm yours with counsel.
        </li>
        <li>
            <strong>Make a bond claim on public work.</strong> Public property cannot be liened, so unpaid parties claim
            on the prime contractor's payment bond. On federal work, a party hired by a subcontractor gives the prime
            written notice within 90 days after its last work, and sues within one year
            (<a href="https://www.govinfo.gov/content/pkg/USCODE-2024-title40/html/USCODE-2024-title40-subtitleII-partA-chap31-subchapIII-sec3133.htm" rel="noopener" target="_blank">40 U.S.C. § 3133</a>).
            On Texas public work, notice is due by the 15th day of the 3rd month after each month of work, and suit within
            one year after the notice is mailed
            (<a href="https://tcss.legis.texas.gov/resources/GV/htm/GV.2253.htm" rel="noopener" target="_blank">Tex. Gov't Code §§ 2253.041, 2253.078</a>).
        </li>
    </ul>
    <p>
        For the next job, see the
        <a href="/guides/mechanics-lien-deadlines-by-state">mechanics lien deadlines by state</a> and our guide to
        <a href="/guides/what-to-do-when-a-contractor-or-owner-doesnt-pay">what to do when a contractor or owner does not pay</a>.
    </p>
</x-guides.article>
@endsection
