@extends('layouts.landing')

@section('title', $guide['page_title'])
@section('description', $guide['description'])
@section('canonical', \App\Support\Seo\Guides::url($guide['slug']))
@section('og_type', 'article')

@php
    $faq = [
        ['q' => 'Can a subcontractor file a lien if the owner already paid the general contractor?', 'a' => 'Often yes, but some states protect owners who paid first. In Kentucky, on an owner-occupied one- or two-family home, the lien does not cover amounts the owner paid before getting your notice (KRS 376.010(5)). In Maryland, on a single-family home built for the owner\'s own residence, a subcontractor has no lien if the owner paid the contractor in full before the notice (Md. Code, Real Prop. § 9-104). Early notices protect you.'],
        ['q' => 'How long does a contractor have to pay a subcontractor?', 'a' => 'Your contract sets the first answer, and state law can set another. In Texas, a contractor must pay a subcontractor its share within 7 days after receiving the owner\'s payment, and late amounts bear interest at 1.5 percent a month (Tex. Prop. Code §§ 28.002, 28.004). Read your contract for the payment terms, then check your state\'s statute or ask counsel.'],
        ['q' => 'Can I stop work if I am not getting paid?', 'a' => 'Only if your contract or a statute allows it. In Texas, if the owner does not pay the contractor an undisputed amount on time, the contractor or a subcontractor may suspend work 10 days after giving written notice (Tex. Prop. Code § 28.009). Without that kind of right, walking off the job can put you in breach of contract. Confirm with counsel before you stop.'],
        ['q' => 'Do I need a lawyer to collect from a contractor?', 'a' => 'Not for the early steps. You can send a demand letter, a notice of intent and, in most states, record the lien yourself. You will need counsel to enforce a lien, because that means a lawsuit, and in Maryland even establishing the lien takes a court petition (Md. Code, Real Prop. § 9-105). Bring counsel in early on large claims or disputed work.'],
        ['q' => 'What if the unpaid job is a federal project?', 'a' => 'You cannot lien federal property, but you can claim on the prime contractor\'s payment bond under the Miller Act. If you contracted with a subcontractor rather than the prime, give the prime written notice within 90 days after your last work or materials. Any suit on the bond is due within one year after your last work or materials (40 U.S.C. § 3133).'],
    ];
    $sources = [
        ['name' => 'Tex. Prop. Code ch. 28 (§§ 28.002, 28.004, 28.009, prompt payment)', 'url' => 'https://tcss.legis.texas.gov/resources/PR/htm/PR.28.htm'],
        ['name' => 'Tex. Prop. Code ch. 53 (§§ 53.052, 53.056, 53.158)', 'url' => 'https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm'],
        ['name' => 'Cal. Civ. Code § 8204 (preliminary notice, 20 days)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8204'],
        ['name' => 'Cal. Civ. Code § 8460 (action to enforce within 90 days)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8460'],
        ['name' => 'Fla. Stat. § 713.08 (claim of lien, 90 days after final furnishing)', 'url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0700-0799/0713/Sections/0713.08.html'],
        ['name' => 'Fla. Stat. § 713.22 (one-year duration; notice of contest, 60 days)', 'url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0700-0799/0713/Sections/0713.22.html'],
        ['name' => 'Fla. Stat. § 713.29 (prevailing party attorney fees)', 'url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0700-0799/0713/Sections/0713.29.html'],
        ['name' => 'C.R.S. § 38-22-109(3) (notice of intent, ten days)', 'url' => 'https://codes.findlaw.com/co/title-38-property-real-and-personal/co-rev-st-sect-38-22-109/'],
        ['name' => '49 P.S. § 1501 (subcontractor\'s formal notice, 30 days)', 'url' => 'https://www.palegis.us/statutes/unconsolidated/law-information/view-statute?txtType=PDF&SessYr=1963&ActNum=0497.&SessInd=0'],
        ['name' => 'RCW 60.04.081 (frivolous or clearly excessive lien; attorney fees)', 'url' => 'https://app.leg.wa.gov/RCW/default.aspx?cite=60.04.081'],
        ['name' => 'KRS 376.010(5) (owner-occupied homes; payments before notice)', 'url' => 'https://apps.legislature.ky.gov/law/statutes/statute.aspx?id=54156'],
        ['name' => 'Md. Code, Real Prop. § 9-104 (notice of intention; owner who paid in full)', 'url' => 'https://mgaleg.maryland.gov/mgawebsite/laws/StatuteText?article=grp&section=9-104&enactments=false'],
        ['name' => 'Md. Code, Real Prop. § 9-105 (petition to establish lien)', 'url' => 'https://mgaleg.maryland.gov/mgawebsite/laws/StatuteText?article=grp&section=9-105&enactments=false'],
        ['name' => '40 U.S.C. § 3133 (Miller Act payment bond claims)', 'url' => 'https://www.govinfo.gov/content/pkg/USCODE-2024-title40/html/USCODE-2024-title40-subtitleII-partA-chap31-subchapIII-sec3133.htm'],
    ];
@endphp

@section('content')
<x-guides.article :guide="$guide" :faq="$faq" :sources="$sources">
    <p>
        If a contractor or owner is not paying you, check your contract and your lien deadline first. Then escalate in
        order: a demand letter, a notice of intent to lien, the lien itself, and a suit to enforce it. Each step adds
        pressure and each has its own clock, so start counting from your last day on the job today.
    </p>

    <h2>Check your contract and start the deadline clock</h2>
    <p>Before you send anything, pull the contract. Look for:</p>
    <ul>
        <li>
            <strong>Payment terms.</strong> When each payment was due, and whether you sent the invoices or pay
            applications it requires.
        </li>
        <li>
            <strong>Pay-if-paid or pay-when-paid clauses.</strong> These tie your payment to the contractor getting paid.
            Whether a court will enforce one varies by state, so confirm with counsel.
        </li>
        <li>
            <strong>Dispute clauses.</strong> Arbitration, notice-of-claim or venue terms can change where and how you can
            sue.
        </li>
    </ul>
    <p>
        Next, check whether a statute already gives you rights. Texas, for example, requires a contractor to pay its
        subcontractor within 7 days after the owner pays, and unpaid amounts bear interest at 1.5 percent a month
        (<a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.28.htm" rel="noopener" target="_blank">Tex. Prop. Code §§ 28.002, 28.004</a>).
    </p>
    <p>
        Then start the clock. Your <strong>last furnishing date</strong> is the last day you performed work or
        delivered materials on the job. Most lien deadlines count from it. Some notices are due much earlier. A
        California preliminary notice is due within 20 days after you first furnish work
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8204" rel="noopener" target="_blank">Cal. Civ. Code § 8204</a>).
        A Texas subcontractor sends a notice of claim for each unpaid month
        (<a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm" rel="noopener" target="_blank">Tex. Prop. Code § 53.056</a>).
        Put your dates into the <a href="/liens/deadline-calculator">lien deadline calculator</a> and look up your
        <a href="/liens">state's lien rules</a>.
    </p>

    <h2>Send a payment demand letter</h2>
    <p>
        A <strong>demand letter</strong> is a written request for a specific amount by a specific date. It is often
        enough. It shows you are organized, it fixes the amount in writing, and it gives the other side a clean chance
        to pay.
    </p>
    <p>
        Include the job name and address, what you furnished, the invoices that are unpaid, the total owed after
        credits, and a short pay-by date. Say what you will do next if you are not paid, such as file a lien. Send it
        to whoever hired you, and copy the owner if your state allows a lien against the property. Keep proof of the
        date you sent it.
    </p>
    <p>
        The pay-by date must leave room for the next steps. A demand letter does not pause any lien deadline.
        eRegister's <a href="/liens/payment-demand-letter">payment demand letter</a> is drafted and mailed for
        {{ \App\Support\Seo\Prices::lien('demand_letter', 'full_service') }}.
    </p>

    <h2>Send a notice of intent to lien</h2>
    <p>
        A <strong>notice of intent to lien</strong> warns the owner that you will file a lien by a stated date. In
        some states it is required before you can file. Colorado requires one at least ten days before the lien
        statement is filed
        (<a href="https://codes.findlaw.com/co/title-38-property-real-and-personal/co-rev-st-sect-38-22-109/" rel="noopener" target="_blank">C.R.S. § 38-22-109(3)</a>).
        Pennsylvania requires a subcontractor to give the owner formal notice at least 30 days before filing
        (<a href="https://www.palegis.us/statutes/unconsolidated/law-information/view-statute?txtType=PDF&amp;SessYr=1963&amp;ActNum=0497.&amp;SessInd=0" rel="noopener" target="_blank">49 P.S. § 1501</a>).
    </p>
    <p>
        In other states it is optional, but it reaches the owner directly. Owners often pay, or push the contractor to
        pay, to keep a lien off their title. See
        <a href="/guides/notice-of-intent-to-lien-explained">notice of intent to lien explained</a> for the states
        that require one, or use the <a href="/liens/notice-of-intent-to-lien">notice of intent service</a>.
    </p>

    <h2>File the mechanics lien</h2>
    <p>
        A <strong>mechanics lien</strong> is a claim against the property that secures what you are owed for improving
        it. File it before your state's deadline, even if talks are still going. In Florida that is no later than 90
        days after your final furnishing
        (<a href="https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&amp;URL=0700-0799/0713/Sections/0713.08.html" rel="noopener" target="_blank">Fla. Stat. § 713.08(5)</a>).
        In Texas it is the 15th day of the 4th month after the month you last furnished
        (<a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm" rel="noopener" target="_blank">Tex. Prop. Code § 53.052</a>).
    </p>
    <p>
        Claim only what you are owed. In Washington, a court can release a frivolous or clearly excessive lien and
        order you to pay the other side's attorney fees
        (<a href="https://app.leg.wa.gov/RCW/default.aspx?cite=60.04.081" rel="noopener" target="_blank">RCW 60.04.081</a>).
        Our guide on <a href="/guides/how-to-file-a-mechanics-lien">how to file a mechanics lien</a> covers the claim,
        recording and service.
    </p>

    <h2>Enforce the lien before it expires</h2>
    <p>
        If the lien does not bring payment, you must sue to foreclose it within the statute's window. California gives
        you 90 days after recording
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8460" rel="noopener" target="_blank">Cal. Civ. Code § 8460</a>).
        Florida gives you one year, but an owner can record a notice of contest that cuts your time to 60 days after
        service
        (<a href="https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&amp;URL=0700-0799/0713/Sections/0713.22.html" rel="noopener" target="_blank">Fla. Stat. § 713.22</a>).
        Texas allows until the first anniversary of the last day you could have filed (§ 53.158).
    </p>
    <p>
        On public work you cannot lien the property. On a federal job you claim on the prime contractor's payment bond
        under the Miller Act. A supplier or subcontractor hired by a subcontractor must give the prime written notice
        within 90 days after its last work or materials, and any suit is due within one year
        (<a href="https://www.govinfo.gov/content/pkg/USCODE-2024-title40/html/USCODE-2024-title40-subtitleII-partA-chap31-subchapIII-sec3133.htm" rel="noopener" target="_blank">40 U.S.C. § 3133</a>).
    </p>

    <h2>What each step costs you in time</h2>
    <x-guides.table id="nonpayment-timeline-table">
        <thead>
            <tr>
                <th scope="col">Step</th>
                <th scope="col">When to take it</th>
                <th scope="col">Time it takes</th>
                <th scope="col">Statute example</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100">
            <tr>
                <th scope="row">Check contract and deadlines</th>
                <td>As soon as a payment is late</td>
                <td>An hour or two</td>
                <td>Texas prompt pay: 7 days after the owner pays</td>
            </tr>
            <tr>
                <th scope="row">Demand letter</th>
                <td>Right after a missed payment</td>
                <td>A few days to send, plus your pay-by date</td>
                <td>None required</td>
            </tr>
            <tr>
                <th scope="row">Notice of intent</th>
                <td>Before the lien, when required or useful</td>
                <td>The statutory lead time</td>
                <td>10 days in Colorado, 30 days for Pennsylvania subs</td>
            </tr>
            <tr>
                <th scope="row">File the lien</th>
                <td>Before the filing deadline</td>
                <td>A day or two to prepare and record</td>
                <td>90 days after final furnishing in Florida</td>
            </tr>
            <tr>
                <th scope="row">Enforce the lien</th>
                <td>Before the lien expires</td>
                <td>A lawsuit, often months</td>
                <td>90 days after recording in California</td>
            </tr>
        </tbody>
    </x-guides.table>

    <h2>When to bring in counsel</h2>
    <p>You can handle the letter, the notice and, in most states, the lien yourself. Bring in counsel when:</p>
    <ul>
        <li>
            <strong>You need to sue.</strong> Enforcing a lien is a lawsuit. In Maryland even establishing the lien takes
            a petition in circuit court
            (<a href="https://mgaleg.maryland.gov/mgawebsite/laws/StatuteText?article=grp&amp;section=9-105&amp;enactments=false" rel="noopener" target="_blank">Md. Code, Real Prop. § 9-105</a>).
        </li>
        <li>
            <strong>The work is disputed.</strong> Quality or change-order disputes shape what amount you can safely
            claim.
        </li>
        <li>
            <strong>Fees can shift.</strong> In Florida, the prevailing party in a lien suit recovers attorney fees
            (<a href="https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&amp;URL=0700-0799/0713/Sections/0713.29.html" rel="noopener" target="_blank">Fla. Stat. § 713.29</a>).
            Losing can cost more than the claim.
        </li>
        <li><strong>The contract has an arbitration clause</strong> or the claim is large.</li>
    </ul>
    <p>
        Whatever you do, do not let talks run past a deadline. A promise to pay next week does not extend your lien
        rights. Check your <a href="/liens">state's lien page</a> for the exact dates and start the next step on time.
    </p>
</x-guides.article>
@endsection
