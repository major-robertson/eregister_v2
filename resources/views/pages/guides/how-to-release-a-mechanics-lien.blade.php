@extends('layouts.landing')

@section('title', $guide['page_title'])
@section('description', $guide['description'])
@section('canonical', \App\Support\Seo\Guides::url($guide['slug']))
@section('og_type', 'article')

@php
    $faq = [
        ['q' => 'How long does a contractor have to release a lien after payment?', 'a' => 'It depends on the state. Some count from payment: 10 days in Nevada, 20 days in Arizona, 30 days in Kentucky and New Jersey. Others count from the owner\'s written request: 10 days in Colorado, Illinois and Texas, 30 days in Pennsylvania and Tennessee. Several large states, including California, Florida and New York, set no deadline after payment, but the owner has court remedies.'],
        ['q' => 'Who files a lien release?', 'a' => 'The lien claimant, or someone it authorizes, signs the release. That can be the company\'s officer, its agent or its attorney. The release is then recorded in the same office where the lien was recorded, such as the county recorder, county clerk or clerk of court. The owner often records it, but the claimant is the one who must sign and deliver it.'],
        ['q' => 'Does a lien release need to be notarized?', 'a' => 'Often, yes. Florida requires the lienor\'s notarized signature on a satisfaction or release (Fla. Stat. § 713.21(2)). New York requires the certificate to be acknowledged or proved (N.Y. Lien Law § 19). Texas requires a release in a form that can be recorded (Tex. Prop. Code § 53.152). Check the recording office\'s rules before you sign.'],
        ['q' => 'What happens if a contractor will not release a lien after being paid?', 'a' => 'Send a written demand for the release first, because many deadlines start with it. If the claimant still refuses, statutes give the owner remedies: a daily penalty in Colorado, $2,500 plus fees in Illinois, $1,000 plus damages in Arizona, or damages and attorney fees in New Jersey and Tennessee. Courts can also order the lien discharged. Talk to counsel about which applies.'],
        ['q' => 'Is a lien release the same as a lien waiver?', 'a' => 'No. A lien waiver is signed in exchange for payment and gives up the right to file a lien for the amount paid. It is usually not recorded. A lien release removes a lien that was already recorded against the property, and it is recorded in the same office as the lien. If no lien was ever filed, you need a waiver, not a release.'],
    ];
    $sources = [
        ['name' => 'C.R.S. § 38-22-118 (satisfaction within ten days of request; $10 a day)', 'url' => 'https://codes.findlaw.com/co/title-38-property-real-and-personal/co-rev-st-sect-38-22-118/'],
        ['name' => 'KRS 382.365 (release within 30 days of satisfaction)', 'url' => 'https://apps.legislature.ky.gov/law/statutes/statute.aspx?id=35623'],
        ['name' => 'La. R.S. 9:4833 (request for cancellation within ten days)', 'url' => 'https://legis.la.gov/Legis/Law.aspx?d=108066'],
        ['name' => 'Md. Code, Real Prop. § 9-105 (petition to establish lien)', 'url' => 'https://mgaleg.maryland.gov/mgawebsite/laws/StatuteText?article=grp&section=9-105&enactments=false'],
        ['name' => 'RSMo § 429.120 (acknowledgment of satisfaction)', 'url' => 'https://revisor.mo.gov/main/OneSection.aspx?section=429.120'],
        ['name' => 'RSMo § 429.130 (penalty for refusing to satisfy)', 'url' => 'https://revisor.mo.gov/main/OneSection.aspx?section=429.130'],
        ['name' => 'NRS 108.2437 (discharge within ten days of satisfaction)', 'url' => 'https://www.leg.state.nv.us/nrs/nrs-108.html'],
        ['name' => 'N.J.S.A. 2A:44A-30 (certificate of discharge)', 'url' => 'https://codes.findlaw.com/nj/title-2a-administration-of-civil-and-criminal-justice/nj-st-sect-2a-44a-30/'],
        ['name' => 'N.D.C.C. ch. 35-27 (construction liens)', 'url' => 'https://ndlegis.gov/cencode/t35c27.pdf'],
        ['name' => 'Tenn. Code Ann. § 66-11-135 (release within 30 days of demand)', 'url' => 'https://codes.findlaw.com/tn/title-66-property/tn-code-sect-66-11-135/'],
        ['name' => 'Tex. Prop. Code § 53.152 (release within ten days of request)', 'url' => 'https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm'],
        ['name' => 'Cal. Civ. Code § 8480 (petition for release order)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8480'],
        ['name' => 'Cal. Civ. Code § 8488 (release order; attorney fees)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8488'],
        ['name' => 'Fla. Stat. § 713.21 (discharge of lien)', 'url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0700-0799/0713/Sections/0713.21.html'],
        ['name' => 'N.Y. Lien Law § 19 (discharge by certificate, in whole or in part)', 'url' => 'https://www.nysenate.gov/legislation/laws/LIE/19'],
        ['name' => 'N.Y. Lien Law § 59 (notice to commence action; vacating lien)', 'url' => 'https://www.nysenate.gov/legislation/laws/LIE/59'],
        ['name' => 'O.C.G.A. § 44-14-367 (expired lien needs no release)', 'url' => 'https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-367/'],
        ['name' => 'N.C. Gen. Stat. § 44A-16 (discharge of claim of lien)', 'url' => 'https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_44A/GS_44A-16.html'],
        ['name' => 'A.R.S. § 33-1006 (release within 20 days; $1,000 and damages)', 'url' => 'https://www.azleg.gov/ars/33/01006.htm'],
        ['name' => 'RCW 60.04.071 (release of lien rights)', 'url' => 'https://app.leg.wa.gov/RCW/default.aspx?cite=60.04.071'],
        ['name' => 'RCW 65.04.045 (recording requirements, reference numbers)', 'url' => 'https://app.leg.wa.gov/RCW/default.aspx?cite=65.04.045'],
        ['name' => '49 P.S. § 1704 (satisfaction within 30 days of request; penalty)', 'url' => 'https://www.palegis.us/statutes/unconsolidated/law-information/view-statute?txtType=PDF&SessYr=1963&ActNum=0497.&SessInd=0'],
        ['name' => '770 ILCS 60/35 (release within ten days of demand; $2,500)', 'url' => 'https://www.ilga.gov/legislation/ilcs/ilcs3.asp?ActID=2254&ChapterID=63'],
    ];
@endphp

@section('content')
<x-guides.article :guide="$guide" :faq="$faq" :sources="$sources">
    <p>
        To release a mechanics lien, the claimant signs a release once paid and records it in the same office where
        the lien was recorded. Many states set a deadline for that after payment or after the owner's written request.
        A claimant who misses it can owe the owner damages, a daily penalty or attorney fees.
    </p>

    <h2>When do you have to release a mechanics lien?</h2>
    <p>
        A <strong>mechanics lien</strong> is a claim against real property that secures payment for work or materials.
        A <strong>lien release</strong> is the recorded document that removes it. Statutes use other names too:
        satisfaction, discharge, cancellation.
    </p>
    <p>
        You owe a release when the debt behind the lien is paid. In Texas the duty applies once the debt is paid by
        collected funds, meaning money that has actually cleared
        (<a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm" rel="noopener" target="_blank">Tex. Prop. Code § 53.152</a>).
        Some states also require a release when the lien has expired or been forfeited. Tennessee covers liens that
        are forfeited, expired, satisfied or lost in court
        (<a href="https://codes.findlaw.com/tn/title-66-property/tn-code-sect-66-11-135/" rel="noopener" target="_blank">Tenn. Code Ann. § 66-11-135</a>).
    </p>
    <p>
        Other states let a lien die on its own. In Florida and North Carolina a lien is discharged when the claimant
        fails to enforce it in time
        (<a href="https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&amp;URL=0700-0799/0713/Sections/0713.21.html" rel="noopener" target="_blank">Fla. Stat. § 713.21</a>;
        <a href="https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_44A/GS_44A-16.html" rel="noopener" target="_blank">N.C. Gen. Stat. § 44A-16</a>).
        Georgia says no release is required for a lien that has expired that way
        (<a href="https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-367/" rel="noopener" target="_blank">O.C.G.A. § 44-14-367</a>).
        A recorded release still clears the title faster, which is what the owner wants.
    </p>

    <h2>Release deadlines and penalties in 20 states</h2>
    <p>
        &quot;Request&quot; means a written request or demand from the owner or another interested party. Where a cell
        says &quot;none found,&quot; our research did not find a statutory deadline or penalty; confirm with counsel.
    </p>
    <x-guides.table id="lien-release-deadlines-table">
        <thead>
            <tr>
                <th scope="col">State</th>
                <th scope="col">Deadline</th>
                <th scope="col">Penalty for not releasing</th>
                <th scope="col">Statute</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100">
            <tr>
                <th scope="row"><a href="/liens/lien-release/arizona">Arizona</a></th>
                <td>20 days after satisfaction</td>
                <td>$1,000 plus actual damages</td>
                <td>
                    <a href="https://www.azleg.gov/ars/33/01006.htm" rel="noopener" target="_blank">A.R.S. § 33-1006</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/arkansas">Arkansas</a></th>
                <td>None found</td>
                <td>None found</td>
                <td>Confirm with counsel</td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/california">California</a></th>
                <td>None after payment</td>
                <td>
                    If you do not sue within 90 days of recording, the owner can petition for a release order; the winner recovers
                    attorney fees
                </td>
                <td>
                    <a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8480" rel="noopener" target="_blank">Cal. Civ. Code §§ 8480</a>,
                    <a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8488" rel="noopener" target="_blank">8488</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/colorado">Colorado</a></th>
                <td>10 days after request, once the lien and recording costs are paid</td>
                <td>$10 for each day of delay</td>
                <td>
                    <a href="https://codes.findlaw.com/co/title-38-property-real-and-personal/co-rev-st-sect-38-22-118/" rel="noopener" target="_blank">C.R.S. § 38-22-118</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/florida">Florida</a></th>
                <td>None found</td>
                <td>The owner can get a summons requiring you to show cause within 20 days</td>
                <td>
                    <a href="https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&amp;URL=0700-0799/0713/Sections/0713.21.html" rel="noopener" target="_blank">Fla. Stat. § 713.21</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/georgia">Georgia</a></th>
                <td>None found</td>
                <td>None found</td>
                <td>
                    <a href="https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-367/" rel="noopener" target="_blank">O.C.G.A. § 44-14-367</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/illinois">Illinois</a></th>
                <td>10 days after request, once the claim and filing costs are paid</td>
                <td>$2,500, plus costs and attorney fees</td>
                <td>
                    <a href="https://www.ilga.gov/legislation/ilcs/ilcs3.asp?ActID=2254&amp;ChapterID=63" rel="noopener" target="_blank">770 ILCS 60/35</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/kentucky">Kentucky</a></th>
                <td>30 days after satisfaction</td>
                <td>
                    Court-ordered release with costs and a reasonable attorney fee; $100 a day starting the 15th day after written
                    notice, if no good cause
                </td>
                <td>
                    <a href="https://apps.legislature.ky.gov/law/statutes/statute.aspx?id=35623" rel="noopener" target="_blank">KRS 382.365</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/louisiana">Louisiana</a></th>
                <td>10 days after request, once the claim is extinguished or was improperly filed</td>
                <td>Damages and reasonable attorney fees, if no reasonable cause</td>
                <td>
                    <a href="https://legis.la.gov/Legis/Law.aspx?d=108066" rel="noopener" target="_blank">La. R.S. 9:4833</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/maryland">Maryland</a></th>
                <td>Depends on the court case that established the lien</td>
                <td>Confirm with counsel</td>
                <td>
                    <a href="https://mgaleg.maryland.gov/mgawebsite/laws/StatuteText?article=grp&amp;section=9-105&amp;enactments=false" rel="noopener" target="_blank">Md. Code, Real Prop. § 9-105</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/missouri">Missouri</a></th>
                <td>When required, once paid; filed with the circuit clerk</td>
                <td>Liable for the injury caused if you refuse for 10 days after payment and request</td>
                <td>
                    <a href="https://revisor.mo.gov/main/OneSection.aspx?section=429.120" rel="noopener" target="_blank">RSMo §§ 429.120</a>,
                    <a href="https://revisor.mo.gov/main/OneSection.aspx?section=429.130" rel="noopener" target="_blank">429.130</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/nevada">Nevada</a></th>
                <td>As soon as practicable, no later than 10 days after satisfaction</td>
                <td>Actual damages or $100, whichever is greater, plus attorney fees and costs</td>
                <td>
                    <a href="https://www.leg.state.nv.us/nrs/nrs-108.html" rel="noopener" target="_blank">NRS 108.2437</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/new-jersey">New Jersey</a></th>
                <td>30 days after payment, or 7 days after a demand</td>
                <td>Court costs, attorney fees and damages</td>
                <td>
                    <a href="https://codes.findlaw.com/nj/title-2a-administration-of-civil-and-criminal-justice/nj-st-sect-2a-44a-30/" rel="noopener" target="_blank">N.J.S.A. 2A:44A-30</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/new-york">New York</a></th>
                <td>None found</td>
                <td>The owner can demand that you sue, then ask a court to vacate the lien</td>
                <td>
                    <a href="https://www.nysenate.gov/legislation/laws/LIE/19" rel="noopener" target="_blank">N.Y. Lien Law §§ 19</a>,
                    <a href="https://www.nysenate.gov/legislation/laws/LIE/59" rel="noopener" target="_blank">59</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/north-carolina">North Carolina</a></th>
                <td>None found</td>
                <td>The owner can deposit cash, or a bond for 1.25 times the claim, with the clerk</td>
                <td>
                    <a href="https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_44A/GS_44A-16.html" rel="noopener" target="_blank">N.C. Gen. Stat. § 44A-16</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/north-dakota">North Dakota</a></th>
                <td>None found</td>
                <td>None found</td>
                <td>
                    <a href="https://ndlegis.gov/cencode/t35c27.pdf" rel="noopener" target="_blank">N.D.C.C. ch. 35-27</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/pennsylvania">Pennsylvania</a></th>
                <td>30 days after request, once paid</td>
                <td>Court-ordered satisfaction and a penalty up to the amount of the claim</td>
                <td>
                    <a href="https://www.palegis.us/statutes/unconsolidated/law-information/view-statute?txtType=PDF&amp;SessYr=1963&amp;ActNum=0497.&amp;SessInd=0" rel="noopener" target="_blank">49 P.S. § 1704</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/tennessee">Tennessee</a></th>
                <td>30 days after written demand, once paid, expired or forfeited</td>
                <td>Damages, costs and reasonable attorney fees</td>
                <td>
                    <a href="https://codes.findlaw.com/tn/title-66-property/tn-code-sect-66-11-135/" rel="noopener" target="_blank">Tenn. Code Ann. § 66-11-135</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/texas">Texas</a></th>
                <td>10 days after request, once paid by collected funds</td>
                <td>None in § 53.152; confirm with counsel</td>
                <td>
                    <a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm" rel="noopener" target="_blank">Tex. Prop. Code § 53.152</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/lien-release/washington">Washington</a></th>
                <td>Immediately, on demand, once paid</td>
                <td>Costs, attorney fees and damages if the delay was unjustified</td>
                <td>
                    <a href="https://app.leg.wa.gov/RCW/default.aspx?cite=60.04.071" rel="noopener" target="_blank">RCW 60.04.071</a>
                </td>
            </tr>
        </tbody>
    </x-guides.table>
    <p>Penalty amounts are as of October 2026.</p>

    <h2>Who signs the release and where is it recorded?</h2>
    <p>
        The claimant signs, or someone it authorizes. In Illinois that can be the claimant or someone it authorized in
        writing. In Florida, whoever signed the claim of lien can sign the release.
    </p>
    <p>
        Most offices want a notarized or acknowledged signature. Since October 1, 2023, a Florida release must carry
        the lienor's notarized signature and the recording number and date stamped on the lien
        (<a href="https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&amp;URL=0700-0799/0713/Sections/0713.21.html" rel="noopener" target="_blank">Fla. Stat. § 713.21(2)</a>).
        A New York certificate must be acknowledged or proved
        (<a href="https://www.nysenate.gov/legislation/laws/LIE/19" rel="noopener" target="_blank">N.Y. Lien Law § 19</a>).
    </p>
    <p>
        Record it in the office where the lien was recorded. Tennessee says so directly, and treats the lien as
        released on the day the release is recorded
        (<a href="https://codes.findlaw.com/tn/title-66-property/tn-code-sect-66-11-135/" rel="noopener" target="_blank">§ 66-11-135(b), (c)</a>).
        Point the release to the lien it cancels. In Washington, the first page of a recorded document lists the
        reference numbers of documents it releases
        (<a href="https://app.leg.wa.gov/RCW/default.aspx?cite=65.04.045" rel="noopener" target="_blank">RCW 65.04.045</a>).
        In New Jersey, the certificate states the lien claim's filing date, book and page, the owner, the property and
        the party the work was for
        (<a href="https://codes.findlaw.com/nj/title-2a-administration-of-civil-and-criminal-justice/nj-st-sect-2a-44a-30/" rel="noopener" target="_blank">N.J.S.A. 2A:44A-30(a)</a>).
    </p>

    <h2>Should you sign a full or a partial release?</h2>
    <p>
        A <strong>full release</strong> removes the whole lien. A <strong>partial release</strong> removes part of it,
        either part of the amount or part of the property. Statutes allow both. A New York certificate can discharge a
        lien in whole or in part, specifying the part
        (<a href="https://www.nysenate.gov/legislation/laws/LIE/19" rel="noopener" target="_blank">N.Y. Lien Law § 19</a>).
        The Texas duty covers the release &quot;to the extent of the indebtedness paid&quot; (§ 53.152).
    </p>
    <p>
        Match the release to what you were paid. If you received half, release half and say what remains. Do not sign
        a full release on a promise of the rest.
    </p>

    <h2>What if the claimant refuses to release the lien?</h2>
    <p>
        If you are the owner, start with a written request or demand. In Colorado, Illinois, Pennsylvania, Tennessee
        and Texas, that letter is what starts the release clock. Keep proof of the date it was received.
    </p>
    <p>
        If the claimant still refuses, the statutes give you tools. In New Jersey, any interested party can bring a
        summary court proceeding to discharge the lien. In Florida, the clerk can issue a summons requiring the
        claimant to show cause within 20 days, and the court orders the lien cancelled if the claimant does not
        (<a href="https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&amp;URL=0700-0799/0713/Sections/0713.21.html" rel="noopener" target="_blank">Fla. Stat. § 713.21(4)</a>).
        In New York, a notice can require the claimant to sue within a set time or face a court order vacating the
        lien
        (<a href="https://www.nysenate.gov/legislation/laws/LIE/59" rel="noopener" target="_blank">N.Y. Lien Law § 59</a>).
        Penalties in the table then add to the claimant's cost.
    </p>

    <h2>Common mistakes</h2>
    <ul>
        <li>
            <strong>Confusing a release with a waiver.</strong> A <a href="/liens/lien-waivers">lien waiver</a> is
            exchanged for payment and gives up the right to file a lien. A release removes a lien already recorded.
        </li>
        <li><strong>Releasing before the money clears.</strong> Wait until the payment is final.</li>
        <li><strong>Recording in the wrong office</strong>, or without the lien's recording number.</li>
        <li><strong>Signing a full release for a partial payment.</strong></li>
        <li>
            <strong>Ignoring the request.</strong> In several states, the penalty clock starts with the owner's letter.
        </li>
    </ul>
    <p>
        eRegister's <a href="/liens/lien-release">lien release service</a> prepares the release for
        {{ \App\Support\Seo\Prices::lien('lien_release') }}. If you have not filed yet, see
        <a href="/guides/how-to-file-a-mechanics-lien">how to file a mechanics lien</a> instead.
    </p>
</x-guides.article>
@endsection
