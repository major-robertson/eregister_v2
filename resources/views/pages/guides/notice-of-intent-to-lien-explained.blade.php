@extends('layouts.landing')

@section('title', $guide['page_title'])
@section('description', $guide['description'])
@section('canonical', \App\Support\Seo\Guides::url($guide['slug']))
@section('og_type', 'article')

@php
    $faq = [
        ['q' => 'Is a notice of intent to lien required?', 'a' => 'Only in some states. Arkansas, Colorado and Missouri require notice a set number of days before the lien is filed, and North Dakota requires certified-mail notice before recording. Kentucky and Maryland require a notice of intention from subcontractors and suppliers within 120 days after their work. Nevada requires one on residential work, and Pennsylvania requires one from subcontractors. Everywhere else it is optional.'],
        ['q' => 'How long after a notice of intent can I file a lien?', 'a' => 'Wait out the lead time your statute sets, counted from service or mailing. That is at least 10 days in Arkansas, Colorado, Missouri and North Dakota, and at least 30 days for Pennsylvania subcontractors. In Missouri, file only after ten full days. Do not let the wait push you past your filing deadline. Send the notice early enough that both fit.'],
        ['q' => 'Does a notice of intent to lien need to be notarized?', 'a' => 'The statutes in our table describe the notice and how to deliver it, not a notary. Proof of delivery can be sworn, though. Colorado requires an affidavit of service or mailing to be recorded with the lien statement (C.R.S. § 38-22-109(3)). In Missouri, a private person who serves the notice verifies the service by affidavit (RSMo § 429.100).'],
        ['q' => 'Is a notice of intent the same as a preliminary notice?', 'a' => 'No. A preliminary notice goes out near the start of a job to tell the owner who you are. A notice of intent goes out after payment is late, shortly before you file a lien. Some states require both, some one, some neither. Missing either one, where required, can cost you your lien rights.'],
        ['q' => 'Can I send a notice of intent to lien by email?', 'a' => 'Not as your only method where the statute names one. Colorado allows personal service or registered or certified mail. North Dakota requires certified mail. Missouri requires service by an officer or a competent adult. Email is not listed in any of those statutes. Use a method that leaves proof of the date, such as certified mail with a return receipt.'],
    ];
    $sources = [
        ['name' => 'Ark. Code Ann. § 18-44-114 (ten days\' notice before filing; service methods)', 'url' => 'https://codes.findlaw.com/ar/title-18-property/ar-code-sect-18-44-114/'],
        ['name' => 'C.R.S. § 38-22-109(3) (notice of intent, ten days, affidavit of service)', 'url' => 'https://codes.findlaw.com/co/title-38-property-real-and-personal/co-rev-st-sect-38-22-109/'],
        ['name' => 'C.R.S. § 38-22-128 (excessive lien forfeited, costs and attorney fees)', 'url' => 'https://codes.findlaw.com/co/title-38-property-real-and-personal/co-rev-st-sect-38-22-128/'],
        ['name' => 'KRS 376.010(4), (5) (notice of intention, 120 or 75 days after last furnishing)', 'url' => 'https://apps.legislature.ky.gov/law/statutes/statute.aspx?id=54156'],
        ['name' => 'La. R.S. 9:4822(D) (notice of nonpayment on residential work)', 'url' => 'https://legis.la.gov/Legis/Law.aspx?d=108062'],
        ['name' => 'Md. Code, Real Prop. § 9-104 (notice of intention to claim a lien, 120 days)', 'url' => 'https://mgaleg.maryland.gov/mgawebsite/laws/StatuteText?article=grp&section=9-104&enactments=false'],
        ['name' => 'RSMo § 429.100 (ten days\' notice before filing)', 'url' => 'https://revisor.mo.gov/main/OneSection.aspx?section=429.100'],
        ['name' => 'NRS 108.226(6), (7) (15-day notice of intent to lien, residential work)', 'url' => 'https://www.leg.state.nv.us/nrs/nrs-108.html'],
        ['name' => 'N.J.S.A. 2A:44A-21 (Notice of Unpaid Balance and Right to File Lien, residential)', 'url' => 'https://codes.findlaw.com/nj/title-2a-administration-of-civil-and-criminal-justice/nj-st-sect-2a-44a-21/'],
        ['name' => 'N.D.C.C. § 35-27-02(4) (certified mail notice ten days before recording)', 'url' => 'https://ndlegis.gov/cencode/t35c27.pdf'],
        ['name' => 'Tenn. Code Ann. § 66-11-145 (notice of nonpayment)', 'url' => 'https://codes.findlaw.com/tn/title-66-property/tn-code-sect-66-11-145/'],
        ['name' => 'Mechanics\' Lien Law of 1963, 49 P.S. § 1501 (subcontractor\'s formal notice, 30 days)', 'url' => 'https://www.palegis.us/statutes/unconsolidated/law-information/view-statute?txtType=PDF&SessYr=1963&ActNum=0497.&SessInd=0'],
        ['name' => 'N.Y. Lien Law § 10 (filing deadline)', 'url' => 'https://www.nysenate.gov/legislation/laws/LIE/10'],
    ];
@endphp

@section('content')
<x-guides.article :guide="$guide" :faq="$faq" :sources="$sources">
    <p>
        A notice of intent to lien is a written warning that you will file a mechanics lien if you are not paid by a
        stated date. A few states require one before you can file, with a set lead time. Everywhere else it is
        optional, and it often gets an overdue bill paid without a lien.
    </p>

    <h2>What is a notice of intent to lien?</h2>
    <p>
        A <strong>mechanics lien</strong> is a claim against real property that secures payment for work or materials
        that improved it. A <strong>notice of intent to lien</strong> is the step just before it. It goes to the
        property owner, and often to the general contractor, and says how much you are owed, for what job, and that
        you will file a lien if the balance is not paid.
    </p>
    <p>
        It is different from a <strong>preliminary notice</strong>, which you send near the start of a job to tell the
        owner you are working there. The notice of intent comes later, once payment is overdue. Some states require
        one of them, some both, some neither.
    </p>

    <h2>Which states require a notice of intent?</h2>
    <p>
        The statutes below set a pre-lien notice rule. Read the &quot;when&quot; column closely. In Arkansas,
        Colorado, Missouri, North Dakota and Pennsylvania you count back from the day you file. In Kentucky, Maryland,
        New Jersey and Tennessee the clock counts forward from your work, and it can run out before you are ready to
        file.
    </p>
    <x-guides.table id="noi-states-table">
        <thead>
            <tr>
                <th scope="col">State</th>
                <th scope="col">Who sends it</th>
                <th scope="col">When</th>
                <th scope="col">Statute</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100">
            <tr>
                <th scope="row"><a href="/liens/notice-of-intent-to-lien/arkansas">Arkansas</a></th>
                <td>Every claimant</td>
                <td>At least 10 days before the lien is filed</td>
                <td>
                    <a href="https://codes.findlaw.com/ar/title-18-property/ar-code-sect-18-44-114/" rel="noopener" target="_blank">Ark. Code Ann. § 18-44-114</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/notice-of-intent-to-lien/colorado">Colorado</a></th>
                <td>Every claimant</td>
                <td>Served at least 10 days before the lien statement is filed</td>
                <td>
                    <a href="https://codes.findlaw.com/co/title-38-property-real-and-personal/co-rev-st-sect-38-22-109/" rel="noopener" target="_blank">C.R.S. § 38-22-109(3)</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/notice-of-intent-to-lien/kentucky">Kentucky</a></th>
                <td>Subcontractors and suppliers with no contract with the owner</td>
                <td>
                    Within 120 days after last furnishing on claims over $1,000; 75 days on smaller claims and on an
                    owner-occupied one- or two-family home
                </td>
                <td>
                    <a href="https://apps.legislature.ky.gov/law/statutes/statute.aspx?id=54156" rel="noopener" target="_blank">KRS 376.010(4), (5)</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/notice-of-intent-to-lien/louisiana">Louisiana</a></th>
                <td>Optional. Subcontractors and suppliers on residential work with no notice of contract filed</td>
                <td>At least 10 days before filing; it extends the filing period to 70 days</td>
                <td>
                    <a href="https://legis.la.gov/Legis/Law.aspx?d=108062" rel="noopener" target="_blank">La. R.S. 9:4822(D)</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/notice-of-intent-to-lien/maryland">Maryland</a></th>
                <td>Subcontractors</td>
                <td>Within 120 days after doing the work or furnishing the materials</td>
                <td>
                    <a href="https://mgaleg.maryland.gov/mgawebsite/laws/StatuteText?article=grp&amp;section=9-104&amp;enactments=false" rel="noopener" target="_blank">Md. Code, Real Prop. § 9-104</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/notice-of-intent-to-lien/missouri">Missouri</a></th>
                <td>Everyone except the original contractor</td>
                <td>At least 10 days before the lien is filed</td>
                <td>
                    <a href="https://revisor.mo.gov/main/OneSection.aspx?section=429.100" rel="noopener" target="_blank">RSMo § 429.100</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/notice-of-intent-to-lien/nevada">Nevada</a></th>
                <td>Everyone except laborers, on single-family and multifamily residential work only</td>
                <td>A 15-day notice served before you record; it adds 15 days to the recording deadline</td>
                <td>
                    <a href="https://www.leg.state.nv.us/nrs/nrs-108.html" rel="noopener" target="_blank">NRS 108.226(6), (7)</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/notice-of-intent-to-lien/new-jersey">New Jersey</a></th>
                <td>
                    Residential work only. The step is a Notice of Unpaid Balance and Right to File Lien, lodged with the county
                    clerk
                </td>
                <td>Within 60 days after last furnishing, then a demand for arbitration</td>
                <td>
                    <a href="https://codes.findlaw.com/nj/title-2a-administration-of-civil-and-criminal-justice/nj-st-sect-2a-44a-21/" rel="noopener" target="_blank">N.J.S.A. 2A:44A-21</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/notice-of-intent-to-lien/north-dakota">North Dakota</a></th>
                <td>Claimants hired through a contractor or subcontractor (send it in every case to be safe)</td>
                <td>By certified mail at least 10 days before the lien is recorded</td>
                <td>
                    <a href="https://ndlegis.gov/cencode/t35c27.pdf" rel="noopener" target="_blank">N.D.C.C. § 35-27-02(4)</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/notice-of-intent-to-lien/tennessee">Tennessee</a></th>
                <td>
                    Remote contractors, except on one- to four-family residential units. The step is a notice of nonpayment
                </td>
                <td>Within 90 days after the last day of each month with unpaid work</td>
                <td>
                    <a href="https://codes.findlaw.com/tn/title-66-property/tn-code-sect-66-11-145/" rel="noopener" target="_blank">Tenn. Code Ann. § 66-11-145</a>
                </td>
            </tr>
            <tr>
                <th scope="row"><a href="/liens/notice-of-intent-to-lien/pennsylvania">Pennsylvania</a></th>
                <td>Subcontractors</td>
                <td>Formal written notice at least 30 days before the claim is filed</td>
                <td>
                    <a href="https://www.palegis.us/statutes/unconsolidated/law-information/view-statute?txtType=PDF&amp;SessYr=1963&amp;ActNum=0497.&amp;SessInd=0" rel="noopener" target="_blank">49 P.S. § 1501</a>
                </td>
            </tr>
        </tbody>
    </x-guides.table>
    <p>
        Two rows are not a notice of intent in the usual sense. New Jersey's residential step is a filed notice
        followed by arbitration, and Tennessee's is a monthly notice of nonpayment. Both are listed because missing
        them costs you the lien. Maryland's lien is then established by a court petition, so confirm the next steps
        there with counsel.
    </p>

    <h2>Why send one when your state does not require it?</h2>
    <p>
        In most states the notice is optional. It still works, for plain reasons. A lien clouds the owner's title,
        which can hold up a sale or a loan draw. The owner often pays, or leans on the general contractor to pay, to
        keep that from happening. The notice also puts your numbers in writing and gives everyone a last chance to fix
        an error before a public filing.
    </p>
    <p>
        It has one cost: time. An optional notice does not stop your filing clock. In New York, for example, nothing
        in the filing statute extends the deadline because you sent a warning
        (<a href="https://www.nysenate.gov/legislation/laws/LIE/10" rel="noopener" target="_blank">N.Y. Lien Law § 10</a>).
        Pick a pay-by date that leaves room to file if the money does not come. The
        <a href="/liens/deadline-calculator">lien deadline calculator</a> shows how much room you have.
    </p>

    <h2>What should a notice of intent say?</h2>
    <p>
        Some statutes set the contents. Arkansas and Missouri require the amount and who owes it
        (<a href="https://revisor.mo.gov/main/OneSection.aspx?section=429.100" rel="noopener" target="_blank">RSMo § 429.100</a>).
        Kentucky requires your intention to hold the property liable and the amount you will claim. Louisiana requires
        the amount and nature of the obligation
        (<a href="https://legis.la.gov/Legis/Law.aspx?d=108062" rel="noopener" target="_blank">La. R.S. 9:4822(D)</a>).
        Nevada requires substantially the same information as the notice of lien itself. Pennsylvania lists six items:
        your name, who you contracted with, the amount due, the general nature of the work or materials, the date you
        completed the work, and a description of the property
        (<a href="https://www.palegis.us/statutes/unconsolidated/law-information/view-statute?txtType=PDF&amp;SessYr=1963&amp;ActNum=0497.&amp;SessInd=0" rel="noopener" target="_blank">49 P.S. § 1501(c)</a>).
        Maryland prescribes a form, so use its wording.
    </p>
    <p>Where the statute is silent, include:</p>
    <ul>
        <li>Your company name and address</li>
        <li>The owner's name and the property address or legal description</li>
        <li>Who hired you and the general contractor's name</li>
        <li>What you furnished and when you last furnished it</li>
        <li>The amount owed, after payments and credits</li>
        <li>The date by which you need payment</li>
        <li>A plain statement that you will file a mechanics lien if you are not paid</li>
    </ul>
    <p>
        Keep the amount exact. In Colorado, knowingly liening for more than is due forfeits the lien and makes you pay
        the owner's costs and attorney fees
        (<a href="https://codes.findlaw.com/co/title-38-property-real-and-personal/co-rev-st-sect-38-22-128/" rel="noopener" target="_blank">C.R.S. § 38-22-128</a>).
        A demand that matches your later lien helps you if the claim is ever challenged.
    </p>

    <h2>How do you send it?</h2>
    <p>Use the method your statute names, and keep proof.</p>
    <ul>
        <li>
            <strong>Colorado:</strong> personal service, or registered or certified mail with return receipt requested. An
            affidavit of the service is recorded with the lien statement.
        </li>
        <li><strong>North Dakota:</strong> certified mail.</li>
        <li>
            <strong>Missouri:</strong> service by an officer who serves civil process or by any competent adult, proved by
            the officer's return or the server's affidavit.
        </li>
        <li>
            <strong>Arkansas:</strong> an officer, a competent adult, return-receipt mail restricted to the addressee, or
            a delivery service with third-party proof of delivery.
        </li>
        <li>
            <strong>Maryland:</strong> registered or certified mail with return receipt, or personal delivery. If the
            owner cannot be reached, it may be posted on the building before a witness.
        </li>
        <li><strong>Nevada:</strong> personal delivery or certified mail.</li>
        <li>
            <strong>Pennsylvania:</strong> first-class, registered or certified mail, or service by an adult like a writ
            of summons. If neither works, post it on a conspicuous public part of the property.
        </li>
        <li><strong>Kentucky:</strong> proof that it was mailed to the owner's last known address is enough.</li>
    </ul>
    <p>
        In optional states, certified mail with a return receipt gives you proof of the date. eRegister's
        <a href="/liens/notice-of-intent-to-lien">notice of intent service</a> prepares and mails the letter for
        {{ \App\Support\Seo\Prices::lien('noi', 'full_service') }}.
    </p>

    <h2>Common mistakes</h2>
    <ul>
        <li>
            <strong>Counting the wrong way.</strong> In Kentucky and Maryland the 120 days run from your work. Waiting
            until you are ready to file can mean the notice is already late.
        </li>
        <li>
            <strong>Filing before the lead time ends.</strong> In Missouri, file only after ten full days have passed
            since service.
        </li>
        <li>
            <strong>Sending it to the owner only.</strong> Colorado requires service on the principal contractor too, and
            Nevada on the reputed prime contractor.
        </li>
        <li>
            <strong>Letting it eat the filing window.</strong> If the pay-by date falls after your lien deadline, the
            warning has cost you the lien.
        </li>
    </ul>
    <p>
        If the date passes without payment, the next step is the lien. Our guide on
        <a href="/guides/how-to-file-a-mechanics-lien">how to file a mechanics lien</a> walks through it, and the
        <a href="/liens">mechanics lien service</a> can prepare it.
    </p>
</x-guides.article>
@endsection
