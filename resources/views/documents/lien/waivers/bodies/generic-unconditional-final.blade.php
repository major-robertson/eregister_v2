{{--
    Generic house form: Unconditional Waiver and Release of Lien (Final
    Payment). The most consequential form: waives everything on signing, so it
    carries the caution box and keeps the disputed-claims exception line.
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">State of {{ $waiver['form']['state_name'] }}</div>

<div class="waiver-notice">
    Notice: This document waives rights unconditionally and states that you have been paid for giving up
    those rights. It is enforceable against you if you sign it, even if you have not been paid. If you have
    not been paid, use a conditional waiver and release form.
</div>

@include('documents.lien.waivers._identification-fields', ['waiver' => $waiver, 'rows' => ['claimant', 'customer', 'job_location', 'owner', 'invoice', 'amount']])

<div class="waiver-body">
    <p>
        The undersigned claimant ("Claimant") has been paid in full for all labor, services, equipment, or
        materials furnished to the property described above (the "Property") and does hereby unconditionally
        waive and release any mechanic's lien, construction lien, or similar claim or right the Claimant has
        against the Property.
    </p>
    <p>
        This release covers the <strong>final payment</strong> of
        $<span class="fill">{{ $waiver['amount'] ?? '' }}</span> to the Claimant for the Property.
        @if (!empty($waiver['exceptions']))
            This document does not cover and the Claimant expressly reserves the following disputed claims:
            {{ $waiver['exceptions'] }}.
        @endif
    </p>
    @include('documents.lien.waivers._extra-clauses', ['waiver' => $waiver])
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p class="waiver-foot">
    {{ $waiver['form']['state_name'] }} does not prescribe a statutory lien waiver form; this is a
    general-purpose unconditional final waiver. Form LW-{{ $waiver['form']['state'] }}-UF
    v{{ $waiver['form']['template_version'] }}.
</p>
