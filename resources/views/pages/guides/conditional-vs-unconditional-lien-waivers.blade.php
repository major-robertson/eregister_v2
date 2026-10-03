@extends('layouts.landing')

@section('title', $guide['page_title'])
@section('description', $guide['description'])
@section('canonical', \App\Support\Seo\Guides::url($guide['slug']))
@section('og_type', 'article')

@php
    $faq = [
        ['q' => 'Is a conditional lien waiver binding if the check bounces?', 'a' => 'Generally no. A conditional waiver takes effect only once you are actually paid. California\'s conditional forms work only when the named check is paid by the bank it is drawn on, and Utah and Nevada void a waiver given for a check that fails to clear. Wyoming is the exception: its single form can stay in effect even if uncertified funds are later dishonored, so ask for certified funds there.'],
        ['q' => 'Should I sign an unconditional lien waiver before I am paid?', 'a' => 'No. An unconditional waiver gives up your lien rights on signing, whether or not money ever arrives. California\'s unconditional forms carry a notice that says so. Texas bars anyone from requiring an unconditional waiver until you have received that payment in good and sufficient funds. Sign a conditional waiver first, then trade it for an unconditional one after the funds clear.'],
        ['q' => 'What is the difference between a progress and a final lien waiver?', 'a' => 'A progress waiver covers work through a specific date, called the through date, and leaves later work and unpaid retainage protected. A final waiver covers the whole job, so once it takes effect your lien rights on that project are gone. Use a final waiver only for the last payment, and list any amount still in dispute where the form allows it.'],
        ['q' => 'Do lien waivers have to be notarized?', 'a' => 'In most states, no. Among the statutory-form states, Mississippi\'s forms end in a notary jurat and Wyoming\'s form carries a notarial acknowledgment. Georgia\'s forms have a witness line instead. California, Arizona, Nevada and Michigan require no notary, and the current Texas statute asks only for the claimant\'s or an authorized agent\'s signature. Do not add a notary block where the form has none.'],
        ['q' => 'Which states require a statutory lien waiver form?', 'a' => 'Twelve states set waiver forms or form rules by statute. Arizona, California, Michigan, Nevada and Texas each prescribe four forms. Georgia, Mississippi and Utah prescribe two. Wyoming has one form for every payment. Florida offers safe-harbor forms. Massachusetts and Missouri each prescribe one form for a narrow situation. In the other states, the waiver\'s wording is a matter of contract.'],
    ];
    $sources = [
        ['name' => 'Cal. Civ. Code 8132 (conditional waiver and release on progress payment)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8132'],
        ['name' => 'Cal. Civ. Code 8134 (unconditional waiver and release on progress payment)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8134'],
        ['name' => 'Cal. Civ. Code 8136 (conditional waiver and release on final payment)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8136'],
        ['name' => 'Cal. Civ. Code 8138 (unconditional waiver and release on final payment)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8138'],
        ['name' => 'Tex. Prop. Code ch. 53, subch. L, 53.281 to 53.287 (waiver and release of lien or payment bond claim)', 'url' => 'https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm'],
        ['name' => 'Fla. Stat. 713.20 (waiver or release of liens)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2025/713.20'],
        ['name' => 'A.R.S. 33-1008 (waiver of lien)', 'url' => 'https://www.azleg.gov/ars/33/01008.htm'],
        ['name' => 'NRS 108.2457 (waiver and release of lien rights; forms)', 'url' => 'https://www.leg.state.nv.us/NRS/NRS-108.html'],
        ['name' => 'MCL 570.1115 (Michigan construction lien waivers)', 'url' => 'https://legislature.mi.gov/Laws/MCL?objectName=mcl-570-1115'],
        ['name' => 'Utah Code 38-1a-802 (waiver or limitation of a lien right; forms)', 'url' => 'https://le.utah.gov/xcode/Title38/Chapter1A/38-1a-S802.html'],
        ['name' => 'Georgia SB 315 (2020), enrolled text rewriting O.C.G.A. 44-14-366', 'url' => 'https://www.legis.ga.gov/api/legislation/document/20192020/194229'],
        ['name' => 'Mississippi SB 2622 (2014), enacting Miss. Code Ann. 85-7-419 and 85-7-433', 'url' => 'https://billstatus.ls.state.ms.us/documents/2014/pdf/SB/2600-2699/SB2622SG.pdf'],
        ['name' => 'Wyo. Stat. 29-10-101 (lien waiver form), in Title 29', 'url' => 'https://wyoleg.gov/statutes/compress/title29.pdf'],
        ['name' => 'Mass. Gen. Laws ch. 254, sec. 32 (void lien waivers; partial waiver and subordination)', 'url' => 'https://malegislature.gov/Laws/GeneralLaws/PartIII/TitleIV/Chapter254/Section32'],
        ['name' => 'Mo. Rev. Stat. 429.016 (unconditional final lien waiver for residential real property)', 'url' => 'https://revisor.mo.gov/main/OneSection.aspx?section=429.016'],
    ];
@endphp

@section('content')
<x-guides.article :guide="$guide" :faq="$faq" :sources="$sources">
    <p>
        A conditional lien waiver gives up your lien rights only after the payment it names actually reaches you. An
        unconditional lien waiver gives them up the moment you sign, even if the check never clears. Use the
        conditional version until the money is in your account, then trade it for the unconditional one.
    </p>
    <p>
        A lien waiver (also called a waiver and release) is a document a contractor, subcontractor or supplier signs
        to confirm payment and give up the right to file a mechanics lien for that amount. A mechanics lien is a claim
        against the property you improved. Owners and lenders ask for waivers as proof that everyone down the chain is
        being paid.
    </p>

    <h2>What are the four types of lien waivers?</h2>
    <p>
        Most waiver systems sort waivers on two questions. Is the waiver tied to a payment you have not received yet
        (conditional) or one you already have (unconditional)? Does it cover a payment during the job (progress) or
        the last payment (final)? That gives four types. Arizona, California, Michigan, Nevada and Texas print all
        four in their statutes.
    </p>
    <x-guides.table id="lien-waiver-types-table">
        <thead>
            <tr>
                <th scope="col">Type</th>
                <th scope="col">When you sign it</th>
                <th scope="col">When it takes effect</th>
                <th scope="col">What it covers</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100">
            <tr>
                <th scope="row">Conditional, progress</th>
                <td>Before or with a progress payment</td>
                <td>When the named payment clears</td>
                <td>Work through the through date, minus listed exceptions</td>
            </tr>
            <tr>
                <th scope="row">Unconditional, progress</th>
                <td>After the progress payment clears</td>
                <td>On signing</td>
                <td>Work through the through date, minus listed exceptions</td>
            </tr>
            <tr>
                <th scope="row">Conditional, final</th>
                <td>Before or with the last payment</td>
                <td>When the final payment clears</td>
                <td>The whole job, minus any disputed amount you list</td>
            </tr>
            <tr>
                <th scope="row">Unconditional, final</th>
                <td>After the last payment clears</td>
                <td>On signing</td>
                <td>The whole job, minus any disputed amount you list</td>
            </tr>
        </tbody>
    </x-guides.table>
    <p>
        California's conditional progress form says it takes effect only when the named check has been paid by the
        bank it is drawn on
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8132" rel="noopener" target="_blank">Cal. Civ. Code 8132</a>).
        Its unconditional forms carry a notice to the claimant that the document is enforceable even if you have not
        been paid
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8134" rel="noopener" target="_blank">Cal. Civ. Code 8134</a>,
        <a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8138" rel="noopener" target="_blank">8138</a>).
    </p>

    <h2>When should you use each one?</h2>
    <p>
        Use a <strong>conditional progress</strong> waiver when the general contractor or owner wants a waiver before
        releasing your pay application. You hand it over with the invoice. It does nothing until the payment clears.
    </p>
    <p>
        Use an <strong>unconditional progress</strong> waiver only after that progress payment is in your account.
        Many payers ask for it with the next pay application, as proof the last payment arrived.
    </p>
    <p>
        Use a <strong>conditional final</strong> waiver to collect your last payment, including retainage. Retainage
        is the part of each payment the payer holds back until the job is done.
    </p>
    <p>
        Use an <strong>unconditional final</strong> waiver only when every dollar is in your account. After that, your
        lien rights on the job are gone.
    </p>
    <p>
        If you have not been paid and the deadline to protect your rights is close, a waiver is the wrong tool. Look
        at a <a href="/liens/notice-of-intent-to-lien">notice of intent to lien</a> or a
        <a href="/liens/payment-demand-letter">payment demand letter</a> instead, and check your dates in the
        <a href="/guides/mechanics-lien-deadlines-by-state">mechanics lien deadlines guide</a>.
    </p>

    <h2>Why is signing an unconditional waiver early so risky?</h2>
    <p>
        An unconditional waiver works like a receipt. It says you were paid. If you sign it before the money clears
        and the check bounces, the owner can rely on the waiver, and you may have no lien left to file.
    </p>
    <p>
        Some states build protection into the law. Texas bars anyone from requiring an unconditional waiver unless you
        actually received that payment in good and sufficient funds
        (<a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm" rel="noopener" target="_blank">Tex. Prop. Code 53.283</a>).
        Utah prescribes no unconditional forms at all. Both of its forms take effect only when your check is paid, and
        a waiver tied to a check that fails to clear is void
        (<a href="https://le.utah.gov/xcode/Title38/Chapter1A/38-1a-S802.html" rel="noopener" target="_blank">Utah Code 38-1a-802</a>).
        Nevada voids any waiver given for a check that does not clear
        (<a href="https://www.leg.state.nv.us/NRS/NRS-108.html" rel="noopener" target="_blank">NRS 108.2457</a>).
    </p>
    <p>
        Two states start a clock instead. In Georgia, a signed waiver becomes effective 90 days after you sign it,
        even without payment, unless you first file an affidavit of nonpayment in the county where the property sits
        (<a href="https://www.legis.ga.gov/api/legislation/document/20192020/194229" rel="noopener" target="_blank">SB 315, rewriting O.C.G.A. 44-14-366</a>).
        Mississippi uses 60 days
        (<a href="https://billstatus.ls.state.ms.us/documents/2014/pdf/SB/2600-2699/SB2622SG.pdf" rel="noopener" target="_blank">SB 2622, enacting Miss. Code Ann. 85-7-419</a>).
        If you sign a waiver in either state and the money does not come, put that deadline on your calendar the same
        day.
    </p>

    <h2>Which states require a statutory waiver form?</h2>
    <p>
        In twelve states the statute sets the form, or the rules for one narrow form. Use the state's wording. Do not
        add terms, and do not add a notary block the form does not have.
    </p>
    <ul>
        <li>
            <strong>Arizona</strong>: four forms. A waiver is unenforceable unless it substantially follows them
            (<a href="https://www.azleg.gov/ars/33/01008.htm" rel="noopener" target="_blank">A.R.S. 33-1008</a>). See
            <a href="/liens/lien-waivers/az">Arizona waivers</a>.
        </li>
        <li>
            <strong>California</strong>: four forms. A waiver not in substantially the statutory form is null and void
            (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8136" rel="noopener" target="_blank">Cal. Civ. Code 8136</a>
            and the sections cited above). See <a href="/liens/lien-waivers/ca">California waivers</a>.
        </li>
        <li>
            <strong>Florida</strong>: safe-harbor forms for progress and final payments. No one may require a different
            form, and you may condition a waiver on payment of a check
            (<a href="https://www.flsenate.gov/Laws/Statutes/2025/713.20" rel="noopener" target="_blank">Fla. Stat. 713.20</a>).
        </li>
        <li>
            <strong>Georgia</strong>: two forms, interim and final, in at least 12-point type, with a witness line
            (<a href="https://www.legis.ga.gov/api/legislation/document/20192020/194229" rel="noopener" target="_blank">O.C.G.A. 44-14-366</a>).
        </li>
        <li>
            <strong>Massachusetts</strong>: no general form. Contract clauses that bar lien filing are void. The one
            statutory form is a partial waiver and subordination for contractors who recorded a notice of contract
            (<a href="https://malegislature.gov/Laws/GeneralLaws/PartIII/TitleIV/Chapter254/Section32" rel="noopener" target="_blank">Mass. Gen. Laws ch. 254, sec. 32</a>).
        </li>
        <li>
            <strong>Michigan</strong>: four forms, called partial and full, each conditional or unconditional
            (<a href="https://legislature.mi.gov/Laws/MCL?objectName=mcl-570-1115" rel="noopener" target="_blank">MCL 570.1115</a>).
        </li>
        <li>
            <strong>Mississippi</strong>: two forms, interim and final, sworn before a notary
            (<a href="https://billstatus.ls.state.ms.us/documents/2014/pdf/SB/2600-2699/SB2622SG.pdf" rel="noopener" target="_blank">Miss. Code Ann. 85-7-419 and 85-7-433</a>).
        </li>
        <li>
            <strong>Missouri</strong>: one form, for an unconditional final waiver on residential real property
            (<a href="https://revisor.mo.gov/main/OneSection.aspx?section=429.016" rel="noopener" target="_blank">Mo. Rev. Stat. 429.016</a>).
        </li>
        <li>
            <strong>Nevada</strong>: four forms, with no &quot;substantially&quot; allowance, so use the exact text
            (<a href="https://www.leg.state.nv.us/NRS/NRS-108.html" rel="noopener" target="_blank">NRS 108.2457</a>).
        </li>
        <li>
            <strong>Texas</strong>: four forms that a waiver must substantially comply with
            (<a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm" rel="noopener" target="_blank">Tex. Prop. Code 53.284</a>).
            See <a href="/liens/lien-waivers/tx">Texas waivers</a>.
        </li>
        <li>
            <strong>Utah</strong>: two forms, both conditional on the check clearing
            (<a href="https://le.utah.gov/xcode/Title38/Chapter1A/38-1a-S802.html" rel="noopener" target="_blank">Utah Code 38-1a-802</a>).
        </li>
        <li>
            <strong>Wyoming</strong>: one form for every payment, with a notarial acknowledgment
            (<a href="https://wyoleg.gov/statutes/compress/title29.pdf" rel="noopener" target="_blank">Wyo. Stat. 29-10-101</a>).
        </li>
    </ul>
    <p>
        In the other states, no statute sets the form. The waiver's wording is a matter of contract, so read it line
        by line before you sign.
    </p>

    <h2>What does the through date mean?</h2>
    <p>
        The through date is the last day of work a progress waiver covers. California's progress forms have a blank
        labeled &quot;Through Date&quot;
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8132" rel="noopener" target="_blank">Cal. Civ. Code 8132</a>).
        Florida's progress form waives lien rights for work furnished through a date you insert
        (<a href="https://www.flsenate.gov/Laws/Statutes/2025/713.20" rel="noopener" target="_blank">Fla. Stat. 713.20</a>).
        Work after that date stays protected.
    </p>
    <p>
        Match the through date to the period the payment actually covers. If your pay application runs through March
        31, do not sign a waiver through April 15.
    </p>

    <h2>What stays protected: retainage and disputed work</h2>
    <p>
        A progress waiver should leave out money you have not been paid. California's unconditional progress form
        lists retentions and unpaid extras as exceptions the waiver does not affect
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8134" rel="noopener" target="_blank">Cal. Civ. Code 8134</a>).
        The Texas conditional progress form excludes unpaid retention and pending modifications and changes
        (<a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm" rel="noopener" target="_blank">Tex. Prop. Code 53.284</a>).
        Wyoming's form has blanks to reserve retainage and any sum not yet paid
        (<a href="https://wyoleg.gov/statutes/compress/title29.pdf" rel="noopener" target="_blank">Wyo. Stat. 29-10-101</a>).
    </p>
    <p>
        Final waivers leave much less room. California's final forms have one exception line, for disputed claims for
        extras, with a dollar amount
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8136" rel="noopener" target="_blank">Cal. Civ. Code 8136</a>).
        If a change order is in dispute, write the amount there. Leaving it blank can give up that claim.
    </p>

    <h2>Common mistakes</h2>
    <ul>
        <li>Signing an unconditional waiver with the invoice instead of after payment.</li>
        <li>Signing a final waiver for a progress payment, which can release retainage.</li>
        <li>Using a through date later than the work the payment covers.</li>
        <li>Adding a notary block or extra terms to a statutory form.</li>
        <li>Forgetting the 90-day (Georgia) or 60-day (Mississippi) clock after signing.</li>
    </ul>
    <p>
        eRegister's lien waiver generator fills in each state's form, and the basic tier is free; start at the
        <a href="/liens/lien-waivers">lien waiver page</a>. For a field-by-field walkthrough, read
        <a href="/guides/how-to-fill-out-a-lien-waiver">how to fill out a lien waiver</a>.
    </p>
    <p>
        This guide is general information, not legal advice. For a waiver tied to a large or disputed payment, confirm
        with counsel.
    </p>
</x-guides.article>
@endsection
