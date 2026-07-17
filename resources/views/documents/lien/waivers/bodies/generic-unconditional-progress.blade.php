{{--
    Generic house form: Unconditional Waiver and Release of Lien (Progress
    Payment). Effective on signing even if payment fails, so it carries a
    prominent caution box (best practice mirroring the statutory-state notices).
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">State of {{ $waiver['form']['state_name'] }}</div>

<div class="waiver-notice">
    Notice: This document waives rights unconditionally and states that you have been paid for giving up
    those rights. It is enforceable against you if you sign it, even if you have not been paid. If you have
    not been paid, use a conditional waiver and release form.
</div>

@include('documents.lien.waivers._identification-fields', ['waiver' => $waiver])

<div class="waiver-body">
    <p>
        The undersigned claimant ("Claimant") has been paid and has received a progress payment in the sum of
        $<span class="fill">{{ $waiver['amount'] ?? '' }}</span> for labor, services, equipment, or materials
        furnished to the property described above (the "Property"), and does hereby unconditionally waive and
        release any mechanic's lien, construction lien, or similar claim or right the Claimant has against the
        Property arising from labor, services, equipment, or materials furnished through
        <span class="fill">{{ $waiver['through_date'] ?? '' }}</span> (the "Through Date").
    </p>
    <p>
        This release covers a <strong>progress payment</strong> only, and only to the extent of the payment
        amount stated above. This document does not cover and the Claimant expressly reserves: (1) any
        retention withheld; (2) amounts for extra work or materials, or for disputed claims; (3) pending or
        unapproved change orders or modifications; and (4) labor, services, equipment, or materials furnished
        after the Through Date.
    </p>
    @if (!empty($waiver['exceptions']))
        <p><strong>Additional exceptions:</strong> {{ $waiver['exceptions'] }}</p>
    @endif
    @include('documents.lien.waivers._extra-clauses', ['waiver' => $waiver])
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p class="waiver-foot">
    {{ $waiver['form']['state_name'] }} does not prescribe a statutory lien waiver form; this is a
    general-purpose unconditional progress waiver. Form LW-{{ $waiver['form']['state'] }}-UP
    v{{ $waiver['form']['template_version'] }}.
</p>
