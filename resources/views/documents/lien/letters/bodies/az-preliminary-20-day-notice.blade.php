{{--
    Arizona Preliminary Twenty Day Lien Notice, A.R.S. § 33-992.01(D): "shall
    follow substantially the following form", verified against azleg.gov on
    2026-09-29. Two columns of parties and claimant details, the general
    description, the jobsite and the estimate, then the bold-faced Notice to
    Property Owner (letters/clauses/az-notice-to-property-owner), the two
    ten-day paragraphs "in type at least as large as the largest type
    otherwise on the document", the signature (shell) and the § 33-992.02
    acknowledgment of receipt (clauses.after_execution). Never edit the
    wording without re-checking the statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $claimant = $parties['claimant'];
@endphp
<p>In accordance with Arizona Revised Statutes section 33-992.01, this is not a lien. This is not a reflection on the integrity of any contractor or subcontractor.</p>

{{-- One table per row so DOMPDF can break between them instead of pushing the whole grid to the next page. --}}
<table class="two-col">
    <tr>
        <td class="left">
            <div class="lbl">The name and address of the owner or reputed owner are:</div>
            @include('documents.lien._parts.party', ['party' => $parties['owner']])
        </td>
        <td>
            <div class="lbl">This preliminary lien notice has been completed by (name and address of claimant):</div>
            @include('documents.lien._parts.party', ['party' => $claimant, 'contact' => true])
            <div>Date: {{ $doc['date'] }}</div>
        </td>
    </tr>
</table>
<table class="two-col">
    <tr>
        <td class="left">
            <div class="lbl">The name and address of the original contractor are:</div>
            @include('documents.lien._parts.party', ['party' => $parties['gc'] ?? ($project['in_privity'] ? $claimant : null)])
        </td>
        <td>
            <div class="lbl">You are hereby notified that the claimant has furnished or will furnish labor, professional services, materials, machinery, fixtures or tools of the following general description:</div>
            @include('documents.lien._parts.work')
        </td>
    </tr>
</table>
<table class="two-col">
    <tr>
        <td class="left">
            <div class="lbl">The name and address of any lender or reputed lender and assigns are:</div>
            @include('documents.lien._parts.party', ['party' => $parties['lender']])
        </td>
        <td>
            <div class="lbl">In the construction, alteration or repair of the building, structure or improvement located at:</div>
            <div>@if ($project['address']['single_line']){{ $project['address']['single_line'] }}@else<span class="fill fill-wide">&nbsp;</span>@endif</div>
        </td>
    </tr>
</table>
<table class="two-col">
    <tr>
        <td class="left">
            <div class="lbl">The name and address of the person with whom the claimant has contracted are:</div>
            @include('documents.lien._parts.party', ['party' => $parties['hiring']])
        </td>
        <td>
            <div class="lbl">And situated on that certain lot(s) or parcel(s) of land in @if ($project['county']){{ $project['county'] }}@else<span class="fill fill-mid">&nbsp;</span>@endif County, Arizona, described as follows:</div>
            <div>@if ($project['legal_description']){!! nl2br(e($project['legal_description'])) !!}@else<span class="fill fill-wide">&nbsp;</span>@endif</div>
            @if ($project['apn'])
                <div>{{ $project['parcel_label'] }}: {{ $project['apn'] }}</div>
            @endif
            <div style="margin-top: 6pt;"><span class="lbl">An estimate of the total price of the labor, professional services, materials, machinery, fixtures or tools furnished or to be furnished is:</span> $@if ($filing['estimate']){{ $filing['estimate'] }}@else<span class="fill fill-mid">&nbsp;</span>@endif</div>
        </td>
    </tr>
</table>

@include('documents.lien.letters.clauses.az-notice-to-property-owner')

<p class="bold-statement">Within ten days after the receipt of this preliminary twenty day notice the owner or other interested party is required to furnish all information necessary to correct any inaccuracies in the notice pursuant to Arizona Revised Statutes section 33-992.01, subsection J or lose as a defense any inaccuracy of that information.</p>

<p class="bold-statement">Within ten days after the receipt of this preliminary twenty day notice if any payment bond has been recorded in compliance with Arizona Revised Statutes section 33-1003, the owner must provide a copy of the payment bond, including the name and address of the surety company and bonding agent providing the payment bond to the person who has given the preliminary twenty day notice. In the event that the owner or other interested party fails to provide the bond information within that ten day period, the claimant shall retain lien rights to the extent precluded or prejudiced from asserting a claim against the bond as a result of not timely receiving the bond information.</p>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
