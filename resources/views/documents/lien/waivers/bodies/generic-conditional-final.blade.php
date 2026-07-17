{{--
    Generic house form: Conditional Waiver and Release of Lien (Final
    Payment). Effective only when the final payment actually clears; reserves
    listed disputed claims.
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">State of {{ $waiver['form']['state_name'] }}</div>

@include('documents.lien.waivers._identification-fields', ['waiver' => $waiver, 'rows' => ['claimant', 'customer', 'job_location', 'owner', 'invoice', 'amount', 'check_maker', 'check_number']])

<div class="waiver-body">
    <p>
        Upon receipt by the undersigned claimant ("Claimant") of a check from
        <span class="fill">{{ $waiver['check_maker'] ?? '' }}</span> in the sum of
        $<span class="fill">{{ $waiver['amount'] ?? '' }}</span> payable to
        <span class="fill">{{ $waiver['claimant']['company'] ?? '' }}</span>, and when the check has been
        properly endorsed and has been paid by the bank on which it is drawn, this document shall become
        effective to waive and release any mechanic's lien, construction lien, or similar claim or right the
        Claimant has against the property described above (the "Property") arising from all labor, services,
        equipment, or materials furnished by the Claimant to the Property.
    </p>
    <p>
        This release covers the <strong>final payment</strong> to the Claimant for the Property.
        @if (!empty($waiver['exceptions']))
            This document does not cover and the Claimant expressly reserves the following disputed claims:
            {{ $waiver['exceptions'] }}.
        @endif
    </p>
    <p>
        Before any recipient of this document relies on it, that person should verify evidence of payment to
        the Claimant. This document is not effective until the payment identified above has actually been
        received.
    </p>
    @include('documents.lien.waivers._extra-clauses', ['waiver' => $waiver])
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p class="waiver-foot">
    {{ $waiver['form']['state_name'] }} does not prescribe a statutory lien waiver form; this is a
    general-purpose conditional final waiver. Form LW-{{ $waiver['form']['state'] }}-CF
    v{{ $waiver['form']['template_version'] }}.
</p>
