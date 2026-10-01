{{--
    Nevada Notice of Lien, the form of NRS 108.226(5) ("A notice of lien must
    be substantially in the following form"), verified against
    leg.state.nv.us (NRS chapter 108, revised 2025) on 2026-09-30. Verbatim:
    the opening sentence, items 1 to 8 and the verification ("being first
    duly sworn on oath according to law, deposes and says: I have read the
    foregoing Notice of Lien ..."). The notice is verified by oath and need
    not be acknowledged (NRS 108.226(3)).

    Where the page departs from the form's layout:
      - The form's "Assessor's Parcel Numbers" line prints above the title
        (clauses.notice_box, instruments/clauses/nv-assessors-parcel-numbers).
      - Item 7 (terms of payment) is a ruled blank: there is no payment-terms
        field, and Document details' contract type is only written or oral.
        The claimant writes the terms in before signing.
      - The claimant's mailing address follows item 8, because NRS
        111.312(1)(a) wants a mailing address on a recorded notice of lien.
      - The form's "(Print Name of Lien Claimant) By: (Authorized Signature)"
        and "(Authorized Signature of Lien Claimant)" are one signature, the
        shell's execution block right after the verification. The form's
        "Subscribed and sworn to before me" is the shell's jurat, whose venue
        stays blank so a notary outside Nevada can complete it (NRS 240.164);
        the form prints "State of Nevada" because it assumes a Nevada notary.
    Never edit the wording without re-checking the statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $signer = $doc['signer'];
    $claimant = $parties['claimant'];
    $owner = $parties['owner'];
    $hiring = $parties['hiring'];
    $amounts = $project['amounts'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
<p>The undersigned claims a lien upon the property described in this notice for work, materials or equipment furnished or to be furnished for the improvement of the property:</p>

<table class="item">
    <tr>
        <td class="n">1.</td>
        <td>The amount of the original contract is: ${!! $blank($amounts['contract']['formatted'], 'fill fill-mid') !!}</td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">2.</td>
        <td>The total amount of all additional or changed work, materials and equipment, if any, is: ${!! $blank($amounts['change_orders']['formatted'], 'fill fill-mid') !!}</td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">3.</td>
        <td>The total amount of all payments received to date is: ${!! $blank($amounts['payments']['formatted'], 'fill fill-mid') !!}</td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">4.</td>
        <td>The amount of the lien, after deducting all just credits and offsets, is: ${!! $blank($filing['amount'], 'fill fill-mid') !!}</td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">5.</td>
        <td>The name of the owner, if known, of the property is: {!! $blank($owner['display_name'] ?? null, 'fill fill-wide') !!}</td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">6.</td>
        <td>The name of the person by whom the lien claimant was employed or to whom the lien claimant furnished or agreed to furnish work, materials or equipment is: {!! $blank($hiring['display_name'] ?? null, 'fill fill-wide') !!}</td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">7.</td>
        <td>A brief statement of the terms of payment of the lien claimant's contract is: <span class="fill fill-wide">&nbsp;</span></td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">8.</td>
        <td>
            A description of the property to be charged with the lien is:
            @include('documents.lien._parts.property')
        </td>
    </tr>
</table>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])

<p>Lien claimant: {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}, whose mailing address is {!! $blank($claimant['address_line'] ?? null, 'fill fill-wide') !!}.</p>

@include('documents.lien._parts.clauses', ['only' => 'before_signature'])

<div class="keep" style="margin-top: 12pt;">
    <p>@if ($signer['name']){{ $signer['name'] }}@else<span class="fill fill-wide">&nbsp;</span> (print name)@endif, being first duly sworn on oath according to law, deposes and says:</p>
    <p class="indent">I have read the foregoing Notice of Lien, know the contents thereof and state that the same is true of my own personal knowledge, except those matters stated upon information and belief, and, as to those matters, I believe them to be true.</p>
</div>
