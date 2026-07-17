{{--
    Generic house form: Conditional Waiver and Release of Lien (Progress
    Payment). For states with no statutory waiver form. Conservative scope:
    effective only on payment, limited to the amount and through-date, and
    expressly reserves retention, disputed/extra work, and pending changes.
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">State of {{ $waiver['form']['state_name'] }}</div>

@include('documents.lien.waivers._identification-fields', ['waiver' => $waiver])

<div class="waiver-body">
    <p>
        Upon receipt by the undersigned claimant ("Claimant") of a check from
        <span class="fill">{{ $waiver['check_maker'] ?? '' }}</span> in the sum of
        $<span class="fill">{{ $waiver['amount'] ?? '' }}</span> payable to
        <span class="fill">{{ $waiver['claimant']['company'] ?? '' }}</span>, and when the check has been
        properly endorsed and has been paid by the bank on which it is drawn, this document shall become
        effective to waive and release any mechanic's lien, construction lien, or similar claim or right the
        Claimant has against the property described above (the "Property") arising from labor, services,
        equipment, or materials furnished by the Claimant to the Property.
    </p>
    <p>
        This release covers a <strong>progress payment</strong> only, for labor, services, equipment, or
        materials furnished through
        <span class="fill">{{ $waiver['through_date'] ?? '' }}</span> (the "Through Date"), and only to the
        extent of the payment amount stated above. This document does not cover and the Claimant expressly
        reserves: (1) any retention withheld; (2) amounts for extra work or materials, or for disputed claims;
        (3) pending or unapproved change orders or modifications; and (4) labor, services, equipment, or
        materials furnished after the Through Date.
    </p>
    @if (!empty($waiver['exceptions']))
        <p><strong>Additional exceptions:</strong> {{ $waiver['exceptions'] }}</p>
    @endif
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
    general-purpose conditional progress waiver. Form LW-{{ $waiver['form']['state'] }}-CP
    v{{ $waiver['form']['template_version'] }}.
</p>
