@extends('layouts.landing')

@section('title', $guide['page_title'])
@section('description', $guide['description'])
@section('canonical', \App\Support\Seo\Guides::url($guide['slug']))
@section('og_type', 'article')

@php
    $faq = [
        ['q' => 'Who signs a lien waiver?', 'a' => 'The claimant signs: the subcontractor, supplier or contractor giving up lien rights in exchange for payment. For a company, an officer or employee with authority signs and adds a title. Texas requires the signature of the claimant or its authorized agent. The owner, lender or general contractor who receives the waiver does not sign it, though they should check that the signer actually has authority.'],
        ['q' => 'Does a lien waiver need to be notarized?', 'a' => 'Usually not. Most states require only a signature. Mississippi\'s statutory forms end in a sworn notary jurat, and Wyoming\'s form includes a notarial acknowledgment, so those two need a notary. Georgia\'s forms call for a witness rather than a notary. Where the statute prints a form with no notary block, do not add one, because changing a statutory form can cause problems.'],
        ['q' => 'What if the check amount does not match the lien waiver?', 'a' => 'Do not sign the waiver as written. Ask the payer for a corrected waiver that shows the amount you are actually receiving. A conditional waiver tied to the wrong amount can release more than you were paid. Never sign an unconditional waiver for more than has cleared. In Texas, no one may require an unconditional waiver for an amount you have not received in good funds.'],
        ['q' => 'What is a through date on a lien waiver?', 'a' => 'The through date is the last day of work that a progress waiver covers. Use the end date of the pay period the payment is for, not the date you sign. Work you perform after the through date stays protected by your lien rights. California\'s progress forms label this blank Through Date, and Florida\'s progress form asks you to insert the date.'],
        ['q' => 'Can I leave blanks on a lien waiver?', 'a' => 'Avoid it. Michigan\'s statutory forms warn the signer not to sign blank or incomplete forms. Georgia and Mississippi say a blank left incomplete does not void the form if the subject matter can reasonably be determined, but you lose control over how the gap is read. Write N/A or zero in fields that do not apply, and keep a copy.'],
    ];
    $sources = [
        ['name' => 'Cal. Civ. Code 8132 (conditional waiver and release on progress payment)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8132'],
        ['name' => 'Cal. Civ. Code 8134 (unconditional waiver and release on progress payment)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8134'],
        ['name' => 'Cal. Civ. Code 8136 (conditional waiver and release on final payment)', 'url' => 'https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&sectionNum=8136'],
        ['name' => 'Tex. Prop. Code 53.281 to 53.284 (waiver and release forms and signature)', 'url' => 'https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm'],
        ['name' => 'Fla. Stat. 713.20 (waiver or release of liens)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2025/713.20'],
        ['name' => 'MCL 570.1115 (Michigan construction lien waivers)', 'url' => 'https://legislature.mi.gov/Laws/MCL?objectName=mcl-570-1115'],
        ['name' => 'NRS 108.2457 (waiver and release of lien rights; forms)', 'url' => 'https://www.leg.state.nv.us/NRS/NRS-108.html'],
        ['name' => 'Georgia SB 315 (2020), enrolled text rewriting O.C.G.A. 44-14-366', 'url' => 'https://www.legis.ga.gov/api/legislation/document/20192020/194229'],
        ['name' => 'Mississippi SB 2622 (2014), enacting Miss. Code Ann. 85-7-419 and 85-7-433', 'url' => 'https://billstatus.ls.state.ms.us/documents/2014/pdf/SB/2600-2699/SB2622SG.pdf'],
        ['name' => 'Wyo. Stat. 29-10-101 (lien waiver form), in Title 29', 'url' => 'https://wyoleg.gov/statutes/compress/title29.pdf'],
    ];
@endphp

@section('content')
<x-guides.article :guide="$guide" :faq="$faq" :sources="$sources">
    <p>
        To fill out a lien waiver, enter who is giving up rights (the claimant), who is paying (the customer), the
        property owner and job address, the through date, and the exact payment amount. Then list any exceptions, such
        as retainage or disputed extras, and sign with your title and date. In a few states you also need a notary or
        a witness.
    </p>
    <p>
        A lien waiver is a document in which a contractor, subcontractor or supplier gives up the right to file a
        mechanics lien for work covered by a payment. If you are not sure which of the four waiver types to use, read
        <a href="/guides/conditional-vs-unconditional-lien-waivers">conditional vs unconditional lien waivers</a>
        first. This guide covers the fields.
    </p>

    <h2>Pick the right form before you fill it in</h2>
    <p>
        Twelve states set waiver forms by statute. In states such as California, Texas and Nevada, you fill in the
        blanks on the state's form and change nothing else. California voids a waiver that is not in substantially the
        statutory form
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8132" rel="noopener" target="_blank">Cal. Civ. Code 8132</a>).
        Nevada requires the exact statutory form
        (<a href="https://www.leg.state.nv.us/NRS/NRS-108.html" rel="noopener" target="_blank">NRS 108.2457</a>). Your
        state's page shows the right forms: for example, <a href="/liens/lien-waivers/ca">California</a>,
        <a href="/liens/lien-waivers/tx">Texas</a> or <a href="/liens/lien-waivers/ga">Georgia</a>.
    </p>
    <p>In other states, the waiver is a contract document. The fields below still apply.</p>

    <h2>Fill in each field</h2>
    <p>Field names vary by state. The table uses the most common labels and notes where states differ.</p>
    <x-guides.table id="lien-waiver-fields-table">
        <thead>
            <tr>
                <th scope="col">Field</th>
                <th scope="col">What to enter</th>
                <th scope="col">Watch for</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100">
            <tr>
                <th scope="row">Claimant</th>
                <td>Your legal business name, as on your contract</td>
                <td>Not a trade name the customer does not know</td>
            </tr>
            <tr>
                <th scope="row">Customer</th>
                <td>The party that hired you and is paying you</td>
                <td>For a sub, usually the general contractor, not the owner</td>
            </tr>
            <tr>
                <th scope="row">Owner</th>
                <td>The property owner of record</td>
                <td>Check the deed or the notice of commencement</td>
            </tr>
            <tr>
                <th scope="row">Project or job location</th>
                <td>Street address or legal description</td>
                <td>Add the job number if the payer uses one</td>
            </tr>
            <tr>
                <th scope="row">Through date</th>
                <td>Last day of work the payment covers</td>
                <td>Progress waivers only; not your signing date</td>
            </tr>
            <tr>
                <th scope="row">Payment amount</th>
                <td>The exact amount of this payment</td>
                <td>Must match the check or transfer</td>
            </tr>
            <tr>
                <th scope="row">Maker and payee of check</th>
                <td>Who wrote the check and who it is payable to</td>
                <td>Joint checks name more than one payee</td>
            </tr>
            <tr>
                <th scope="row">Exceptions</th>
                <td>Retainage, unpaid extras, disputed amounts</td>
                <td>Leave nothing you are owed unlisted</td>
            </tr>
            <tr>
                <th scope="row">Signature, title, date</th>
                <td>Authorized signer, title, signing date</td>
                <td>A notary or witness in a few states</td>
            </tr>
        </tbody>
    </x-guides.table>
    <p>
        California's conditional progress form shows the typical set: claimant, customer, job location, owner, through
        date, maker of check, amount of check, payee, exceptions, then signature, title and date
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8132" rel="noopener" target="_blank">Cal. Civ. Code 8132</a>).
        The Texas conditional progress form asks for the project, job number, maker of check, amount, payee, owner,
        location and a job description
        (<a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm" rel="noopener" target="_blank">Tex. Prop. Code 53.284</a>).
        Florida's forms ask for the amount, your customer, the owner and a property description, and the progress form
        adds a through date
        (<a href="https://www.flsenate.gov/Laws/Statutes/2025/713.20" rel="noopener" target="_blank">Fla. Stat. 713.20</a>).
    </p>

    <h2>Get the amount, through date and exceptions right</h2>
    <p>These three fields decide what you give up.</p>
    <p>
        <strong>Amount.</strong> Enter the payment you are receiving now, not the contract total and not the full pay
        application if the payer is cutting it. Nevada's unconditional progress form releases rights only to the
        extent of the payment amount, or the part of it you are actually paid
        (<a href="https://www.leg.state.nv.us/NRS/NRS-108.html" rel="noopener" target="_blank">NRS 108.2457</a>).
    </p>
    <p>
        <strong>Through date.</strong> Use the end of the billing period. California's progress forms label this blank
        &quot;Through Date&quot;
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8132" rel="noopener" target="_blank">Cal. Civ. Code 8132</a>).
    </p>
    <p>
        <strong>Exceptions.</strong> California's progress forms carve out retentions and unpaid extras, and the
        conditional form also lets you list earlier progress payments you signed for but never received
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8132" rel="noopener" target="_blank">Cal. Civ. Code 8132</a>,
        <a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8134" rel="noopener" target="_blank">8134</a>).
        Its final forms have one line, for disputed claims for extras
        (<a href="https://leginfo.legislature.ca.gov/faces/codes_displaySection.xhtml?lawCode=CIV&amp;sectionNum=8136" rel="noopener" target="_blank">Cal. Civ. Code 8136</a>).
        Fill in the dollar amount of any open change order. On Michigan's partial waivers, circle &quot;does&quot; or
        &quot;does not&quot; to say whether the waiver, with earlier ones, covers everything due through the date
        shown
        (<a href="https://legislature.mi.gov/Laws/MCL?objectName=mcl-570-1115" rel="noopener" target="_blank">MCL 570.1115</a>).
    </p>

    <h2>Sign it, and add a notary or witness only where required</h2>
    <p>
        Texas requires the signature of the claimant or the claimant's authorized agent
        (<a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm" rel="noopener" target="_blank">Tex. Prop. Code 53.281</a>).
        For a company, the signer should be someone with authority to release the company's rights, and should write a
        title.
    </p>
    <p>Most states need nothing more. The exceptions:</p>
    <ul>
        <li>
            <strong>Mississippi</strong>: both statutory forms end in a jurat, so you swear to the waiver before a notary
            (<a href="https://billstatus.ls.state.ms.us/documents/2014/pdf/SB/2600-2699/SB2622SG.pdf" rel="noopener" target="_blank">SB 2622, Miss. Code Ann. 85-7-433</a>).
            See <a href="/liens/lien-waivers/ms">Mississippi waivers</a>.
        </li>
        <li>
            <strong>Wyoming</strong>: the single statutory form includes a notarial acknowledgment
            (<a href="https://wyoleg.gov/statutes/compress/title29.pdf" rel="noopener" target="_blank">Wyo. Stat. 29-10-101</a>).
            See <a href="/liens/lien-waivers/wy">Wyoming waivers</a>.
        </li>
        <li>
            <strong>Georgia</strong>: the forms are signed under seal with a witness line
            (<a href="https://www.legis.ga.gov/api/legislation/document/20192020/194229" rel="noopener" target="_blank">SB 315, O.C.G.A. 44-14-366</a>).
        </li>
    </ul>
    <p>
        Where the statute prints a form without a notary block, do not add one. Altering a statutory form invites a
        dispute over whether it still complies.
    </p>

    <h2>What to do when the amount does not match the check</h2>
    <p>
        The payer sends a waiver for the full pay application, then cuts a smaller check. Or the check is right and
        the waiver is wrong.
    </p>
    <ol>
        <li>
            <strong>Do not sign the waiver as written.</strong> A waiver for more than you are paid can release the
            difference.
        </li>
        <li>
            <strong>Ask for a corrected waiver</strong> that shows the actual amount and the right maker and payee. If you
            fill out your own, use the real figure.
        </li>
        <li>
            <strong>List the shortfall as an exception</strong> if the form allows it, or note it in the cover email.
        </li>
        <li>
            <strong>Never sign an unconditional waiver for money that has not cleared.</strong> Texas bars anyone from
            requiring an unconditional waiver for an amount you have not received in good and sufficient funds
            (<a href="https://tcss.legis.texas.gov/resources/PR/htm/PR.53.htm" rel="noopener" target="_blank">Tex. Prop. Code 53.283</a>).
        </li>
    </ol>
    <p>
        If the payer refuses to pay the difference, keep track of your lien deadlines and consider a
        <a href="/liens/payment-demand-letter">payment demand letter</a>. If you later get paid on a lien you already
        filed, you will need a <a href="/liens/lien-release">lien release</a>.
    </p>

    <h2>Common mistakes</h2>
    <ul>
        <li>Writing the contract total instead of this payment.</li>
        <li>Using the signing date as the through date.</li>
        <li>
            Leaving the exceptions blank when retainage or a change order is open. Michigan's forms warn the signer not to
            sign blank or incomplete forms
            (<a href="https://legislature.mi.gov/Laws/MCL?objectName=mcl-570-1115" rel="noopener" target="_blank">MCL 570.1115</a>).
        </li>
        <li>Naming the owner as the customer on a subcontract.</li>
        <li>Signing a statutory form that someone has edited.</li>
        <li>Not keeping a copy of what you signed.</li>
    </ul>
    <p>
        eRegister's <a href="/liens/lien-waivers">lien waiver generator</a> puts these fields on your state's form,
        and the basic tier is free. Paid tiers are listed on the
        <a href="/liens/lien-waivers/pricing">waiver pricing page</a>.
    </p>
    <p>
        This guide is general information, not legal advice. If a waiver has been edited or the amounts are in
        dispute, confirm with counsel before you sign.
    </p>
</x-guides.article>
@endsection
