{{--
    Washington Claim of Lien, RCW 60.04.091(2): "A claim of lien
    substantially in the following form shall be sufficient". The opening
    lines, items 1 to 8 (in the form's order and capitals) and the sworn
    statement are verbatim from app.leg.wa.gov, verified 2026-09-30, with
    two changes. The form cites "*chapter 64.04 RCW"; the code reviser's
    note says chapter 60.04 RCW "was apparently intended", so that is what
    prints. The form's venue ("STATE OF WASHINGTON, COUNTY OF …, ss.") is
    carried by the notary certificate's venue lines, because clients often
    swear outside Washington. The claimant signs once, under the sworn
    statement (the shell's execution block), and the jurat follows, as the
    form's "Subscribed and sworn to before me" does. Item 5 prints "unknown"
    only when the project has no owner party, as the form directs.

    Above the form, one house line carries the abbreviated legal description
    that RCW 65.04.045(1)(f) wants on page 1: with the 3-inch recorder space
    and the caption, item 4 (the full description) starts on page 2. The
    index line beside the caption carries the parcel number. Never edit the
    statutory wording without re-checking the statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $clauses = $doc['form']['clauses'];
    $signer = $doc['signer'];
    $claimant = $parties['claimant'];
    $owner = $parties['owner'];
    $hiring = $parties['hiring'];
    $claimantName = $claimant['display_name'] ?? null;
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';

    // "…, being sworn, says:" names the signer, and the capacity when a person signs for a company.
    $affiant = $blank($signer['name'], 'fill fill-wide');

    if ($claimantName !== null && $signer['name'] !== $claimantName) {
        $affiant .= ', '.$blank($signer['title'], 'fill fill-mid').' of '.e($claimantName);
    }
@endphp
<p class="small">Abbreviated legal description: {!! $blank($project['legal_description'], 'fill fill-wide') !!} (complete legal description in item 4)</p>

<p>{!! $blank($claimantName, 'fill fill-wide') !!}, claimant, vs {!! $blank($hiring['display_name'] ?? null, 'fill fill-wide') !!}, name of person indebted to claimant:</p>

<p>Notice is hereby given that the person named below claims a lien pursuant to chapter 60.04 RCW. In support of this lien the following information is submitted:</p>

<table class="item">
    <tr>
        <td class="n">1.</td>
        <td>
            <div>NAME OF LIEN CLAIMANT: {!! $blank($claimantName, 'fill fill-wide') !!}</div>
            <div>TELEPHONE NUMBER: {!! $blank($claimant['phone'] ?? null) !!}</div>
            <div>ADDRESS: {!! $blank($claimant['address_line'] ?? null, 'fill fill-wide') !!}</div>
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">2.</td>
        <td>DATE ON WHICH THE CLAIMANT BEGAN TO PERFORM LABOR, PROVIDE PROFESSIONAL SERVICES, SUPPLY MATERIAL OR EQUIPMENT OR THE DATE ON WHICH EMPLOYEE BENEFIT CONTRIBUTIONS BECAME DUE: {!! $blank($project['dates']['first_furnish']) !!}</td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">3.</td>
        <td>NAME OF PERSON INDEBTED TO THE CLAIMANT: {!! $blank($hiring['display_name'] ?? null, 'fill fill-wide') !!}</td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">4.</td>
        <td>
            DESCRIPTION OF THE PROPERTY AGAINST WHICH A LIEN IS CLAIMED (Street address, legal description or other information that will reasonably describe the property):
            @include('documents.lien._parts.property')
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">5.</td>
        <td>NAME OF THE OWNER OR REPUTED OWNER (If not known state "unknown"): {{ $owner['display_name'] ?? 'unknown' }}</td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">6.</td>
        <td>THE LAST DATE ON WHICH LABOR WAS PERFORMED; PROFESSIONAL SERVICES WERE FURNISHED; CONTRIBUTIONS TO AN EMPLOYEE BENEFIT PLAN WERE DUE; OR MATERIAL, OR EQUIPMENT WAS FURNISHED: {!! $blank($project['dates']['last_furnish']) !!}</td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">7.</td>
        <td>PRINCIPAL AMOUNT FOR WHICH THE LIEN IS CLAIMED IS: ${!! $blank($filing['amount'], 'fill fill-mid') !!}</td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">8.</td>
        <td>IF THE CLAIMANT IS THE ASSIGNEE OF THIS CLAIM SO STATE HERE: <span class="fill fill-wide">&nbsp;</span></td>
    </tr>
</table>

@foreach ((array) ($clauses['affirmations'] ?? []) as $affirmation)
    <p>{{ $affirmation }}</p>
@endforeach

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])

{{-- Kept with the signature and jurat that follow it (the shell's execution block). --}}
<p style="page-break-after: avoid;">{!! $affiant !!}, being sworn, says: I am the claimant (or attorney of the claimant, or administrator, representative, or agent of the trustees of an employee benefit plan) above named; I have read or heard the foregoing claim, read and know the contents thereof, and believe the same to be true and correct and that the claim of lien is not frivolous and is made with reasonable cause, and is not clearly excessive under penalty of perjury.</p>
