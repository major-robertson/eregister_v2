{{--
    NRS 108.226(6): the 15-day notice of intent to lien must incorporate
    "substantially the same information required in a notice of lien". The
    generic notice already gives the claimant, the person who employed it,
    the property, the work and the amounts; this adds the two items of NRS
    108.226(2) it lacks, worded as items 5 and 7 of the notice of lien form
    in NRS 108.226(5): the owner and the terms of payment (a ruled blank,
    since there is no payment-terms field). Verified against leg.state.nv.us
    on 2026-09-30. Never edit the wording without re-checking the statute.
--}}
@php
    $owner = $doc['parties']['owner'];
@endphp
<table class="fields">
    <tr>
        <td class="k">The name of the owner, if known, of the property is:</td>
        <td>@if ($owner !== null && $owner['display_name']){{ $owner['display_name'] }}@else<span class="fill fill-wide">&nbsp;</span>@endif</td>
    </tr>
    <tr>
        <td class="k">A brief statement of the terms of payment of the lien claimant's contract is:</td>
        <td><span class="fill fill-wide">&nbsp;</span></td>
    </tr>
</table>
