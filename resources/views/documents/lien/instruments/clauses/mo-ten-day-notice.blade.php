{{--
    Missouri ten-day notice recital for the Statement of Mechanic's Lien.
    RSMo § 429.100: every person except the original contractor gives the
    owner or agent ten days' notice before filing the lien "that he holds a
    claim against such building or improvement, setting forth the amount and
    from whom the same is due" (verified against revisor.mo.gov on
    2026-09-30). House wording that tracks the statute, not statutory text.

    Prints only when the claimant is not the original contractor: the
    payload's in_privity is false (the claimant did not contract with the
    owner), or unknown while a service date is on file. The date comes from
    Document details (notice served date) and prints as a ruled blank until
    it is set. The generic "Prior notice" item stays off in Missouri because
    the state's prelim title is the § 429.012 notice to owner.
--}}
@php
    $form = $doc['form'];
    $inPrivity = $doc['project']['in_privity'];
    $servedAt = $doc['details']['notice_served_at'];
    $recite = $inPrivity === false || ($inPrivity === null && $servedAt !== null);
@endphp
@if ($recite)
    <p><span class="lbl">Notice before filing.</span> On @if ($servedAt){{ $servedAt }}@else<span class="fill">&nbsp;</span>@endif, Claimant served notice on the owner that it holds a claim against the building or improvement, stating the amount and from whom it is due. That notice was served at least ten days before this {{ $form['title'] }} was filed (RSMo § 429.100).</p>
@endif
