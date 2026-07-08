{{--
    Michigan statutory form: FULL CONDITIONAL WAIVER, MCL 570.1115(9)(d).
    The full unconditional form plus the statutory sentence "This waiver is
    conditioned on actual payment of ____." (the statute leaves the amount
    as a blank in this form). Effective upon payment of the amount indicated
    (MCL 570.1115(4)). The full forms say "improvement of the property",
    preserved as printed. No notary or witness block: adding one alters the
    statutory format.
--}}
@php
    $property = implode('; ', array_filter([
        $waiver['project']['address_line'] ?? null,
        !empty($waiver['project']['county']) ? 'County of '.$waiver['project']['county'] : null,
        $waiver['project']['legal_description'] ?? null,
    ]));
    $claimantAddress = implode(', ', $waiver['claimant']['address_lines'] ?? []);
@endphp

<div class="waiver-title">Full Conditional Waiver</div>
<div class="waiver-statute">Michigan Construction Lien Act: MCL 570.1115(9)(d)</div>

<div class="waiver-body">
    <p>
        My/our contract with
        <span class="fill-wide">{{ $waiver['customer']['company'] ?? ($waiver['customer']['name'] ?? '') }}</span>
        (other contracting party) to provide
        <span class="fill-wide">{{ $waiver['project']['name'] ?? '' }}</span>
        for the improvement of the property described as
        <span class="fill-wide">{{ $property }}</span>
        has been fully paid and satisfied. By signing this waiver, all my/our construction lien
        rights against the described property are waived and released.
    </p>
    <p>
        <strong>This waiver is conditioned on actual payment of
        <span class="fill">{{ ($waiver['amount'] ?? null) !== null ? '$'.$waiver['amount'] : '' }}</span>.</strong>
        If the improvement is provided to property that is a residential structure and if the owner
        or lessee of the property or the owner's or lessee's designee has received a notice of
        furnishing from me/one of us or if I/we are not required to provide one, and the owner,
        lessee, or designee has not received this waiver directly from me/one of us, the owner,
        lessee, or designee may not rely upon it without contacting me/one of us, either in writing,
        by telephone, or personally, to verify that it is authentic.
    </p>
    @if (!empty($waiver['exceptions']))
        <p><strong>Exceptions:</strong> {{ $waiver['exceptions'] }}</p>
    @endif
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<table class="waiver-fields" style="margin-top: 12px;">
    <tr>
        <td class="label">Address:</td>
        <td class="value">{{ $claimantAddress }}&nbsp;</td>
    </tr>
    <tr>
        <td class="label">Telephone:</td>
        <td class="value">{{ $waiver['claimant']['phone'] ?? '' }}&nbsp;</td>
    </tr>
</table>

<div class="waiver-notice" style="margin-top: 16px;">
    DO NOT SIGN BLANK OR INCOMPLETE FORMS. RETAIN A COPY.
</div>

<p class="waiver-foot">
    Michigan statutory form, MCL 570.1115(9)(d) (used in substantially the statutory format);
    effective upon payment of the amount indicated (MCL 570.1115(4)).
    Form LW-MI-FC v{{ $waiver['form']['template_version'] }}.
</p>
