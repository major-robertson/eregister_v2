{{--
    Michigan statutory form: PARTIAL UNCONDITIONAL WAIVER, MCL 570.1115(9)(a).
    The Construction Lien Act requires the form "in substantially the following
    format"; the text below tracks the statute verbatim with blanks bound to
    payload fields. The "(circle one) does / does not" election is circled by
    hand; the payload carries no election, so both options render. No notary
    or witness block: adding one alters the statutory format.
--}}
@php
    $property = implode('; ', array_filter([
        $waiver['project']['address_line'] ?? null,
        !empty($waiver['project']['county']) ? 'County of '.$waiver['project']['county'] : null,
        $waiver['project']['legal_description'] ?? null,
    ]));
    $claimantAddress = implode(', ', $waiver['claimant']['address_lines'] ?? []);
@endphp

<div class="waiver-title">Partial Unconditional Waiver</div>
<div class="waiver-statute">Michigan Construction Lien Act: MCL 570.1115(9)(a)</div>

<div class="waiver-body">
    <p>
        I/we have a contract with
        <span class="fill-wide">{{ $waiver['customer']['company'] ?? ($waiver['customer']['name'] ?? '') }}</span>
        (other contracting party) to provide
        <span class="fill-wide">{{ $waiver['project']['name'] ?? '' }}</span>
        for the improvement to the property described as
        <span class="fill-wide">{{ $property }}</span>, and by signing this waiver
        waive my/our construction lien to the amount of
        $<span class="fill">{{ $waiver['amount'] ?? '' }}</span>, for labor/materials provided through
        <span class="fill">{{ $waiver['through_date'] ?? '' }}</span> (date).
    </p>
    <p>
        This waiver, together with all previous waivers, if any, (circle one)
        <strong>does&nbsp;&nbsp;/&nbsp;&nbsp;does not</strong>
        cover all amounts due to me/us for contract improvement provided through the date shown
        above. If the improvement is provided to property that is a residential structure and if the
        owner or lessee of the property or the owner's or lessee's designee has received a notice of
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
    Michigan statutory form, MCL 570.1115(9)(a) (used in substantially the statutory format).
    Form LW-MI-PU v{{ $waiver['form']['template_version'] }}.
</p>
