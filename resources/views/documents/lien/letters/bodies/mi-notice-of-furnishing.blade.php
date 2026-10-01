{{--
    Michigan Notice of Furnishing, the form of MCL 570.1109(4) ("must be in
    substantially the following form"), verified against legislature.mi.gov
    (Michigan Compiled Laws complete through PA 103 of 2026) on 2026-09-30.
    The form's words are verbatim, with the filing's data in its blanks: the
    designee (the owner party; the app has no designee role), the other
    contracting party (the hiring party), the type of work, the property with
    ruled blanks for the liber and page of the recorded notice of
    commencement (no Document details key carries them) and the county, the
    alternative "or (a copy of which is attached to this notice)", the
    WARNING TO OWNER in capitals as the statute prints it, and the claimant,
    signer and capacity over the form's own captions (the address of the
    party signing is the claimant's mailing address). The shell's signature
    block carries the signature and the date. Never edit the wording without
    re-checking the statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $signer = $doc['signer'];
    $claimant = $parties['claimant'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
    $county = $project['county'] ? $project['county'].' County' : null;
@endphp
<table class="fields">
    <tr>
        <td class="k" style="width: 0.5in;">To:</td>
        <td>@include('documents.lien._parts.party', ['party' => $parties['owner']])</td>
    </tr>
</table>

<p>Please take notice that the undersigned is furnishing to</p>

<div class="indent">
    @include('documents.lien._parts.party', ['party' => $parties['hiring']])
</div>

<p>
    certain labor or material for {!! $blank($filing['description_of_work'], 'fill fill-wide') !!},
    in connection with the improvements to the real property described in the notice of commencement recorded in liber <span class="fill fill-short">&nbsp;</span>, on page <span class="fill fill-short">&nbsp;</span>, {!! $blank($county, 'fill fill-mid') !!} records,
</p>

<div class="indent">
    @include('documents.lien._parts.property')
</div>

<p>or (a copy of which is attached to this notice)</p>

<div class="notice-box">WARNING TO OWNER: THIS NOTICE IS REQUIRED BY THE MICHIGAN CONSTRUCTION LIEN ACT. IF YOU HAVE QUESTIONS ABOUT YOUR RIGHTS AND DUTIES UNDER THIS ACT, YOU SHOULD CONTACT AN ATTORNEY TO PROTECT YOU FROM THE POSSIBILITY OF PAYING TWICE FOR THE IMPROVEMENTS TO YOUR PROPERTY.</div>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])

<div class="keep">
    <div>{!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}{{ ($claimant['address_line'] ?? null) ? ', '.$claimant['address_line'] : '' }}</div>
    <div class="sig-caption">(name and address of lien claimant)</div>
    <div style="margin-top: 6pt;">by {!! $blank($signer['name'], 'fill fill-mid') !!}, {!! $blank($signer['title'], 'fill fill-mid') !!}</div>
    <div class="sig-caption">(name and capacity of party signing for lien claimant)</div>
    <div style="margin-top: 6pt;">{!! $blank($claimant['address_line'] ?? null, 'fill fill-wide') !!}</div>
    <div class="sig-caption">(address of party signing)</div>
</div>

@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
