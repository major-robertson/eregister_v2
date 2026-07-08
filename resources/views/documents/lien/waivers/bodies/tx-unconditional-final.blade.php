{{--
    Texas statutory form: Unconditional Waiver and Release on Final Payment,
    Tex. Prop. Code § 53.284(e). The § 53.284(e)(1) notice MUST appear at the
    top of the document, printed in bold type at least as large as the largest
    type used in the document and not smaller than 10-point, so the notice box
    renders FIRST (12pt bold, equal to the shell's largest type), in the
    statute's own sentence case (Texas prescribes bold, not caps). § 53.283:
    it is prohibited to require this form unless the claimant was actually
    paid in good and sufficient funds.
--}}
<div class="waiver-notice" style="text-transform: none;">
    This document waives rights unconditionally and states that you have been paid for giving up those
    rights. It is prohibited for a person to require you to sign this document if you have not been paid
    the payment amount set forth below. If you have not been paid, use a conditional release form.
</div>

<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">State of Texas: Statutory form, Tex. Prop. Code &sect; 53.284(e)</div>

<table class="waiver-fields">
    <tr>
        <td class="label">Project:</td>
        <td class="value">{{ $waiver['project']['name'] ?? '' }}&nbsp;</td>
    </tr>
    <tr>
        <td class="label">Job No.:</td>
        <td class="value">{{ $waiver['project']['job_number'] ?? '' }}&nbsp;</td>
    </tr>
</table>

<div class="waiver-body">
    <p>
        The signer of this document has been paid in full for all labor, services, equipment, or materials
        furnished to the property or to
        <span class="fill">{{ $waiver['customer']['company'] ?? ($waiver['customer']['name'] ?? '') }}</span>
        (person with whom signer contracted) on the property of
        <span class="fill">{{ $waiver['owner']['company'] ?? ($waiver['owner']['name'] ?? '') }}</span> (owner)
        located at
        <span class="fill-wide">{{ $waiver['project']['address_line'] ?? '' }}</span> (location)
        to the following extent: <span class="fill-wide">&nbsp;</span> (job description).
        The signer therefore waives and releases any mechanic's lien right, any right arising from a
        payment bond that complies with a state or federal statute, any common law payment bond right, any
        claim for payment, and any rights under any similar ordinance, rule, or statute related to claim or
        payment rights for persons in the signer's position.
    </p>
    <p>
        The signer warrants that the signer has already paid or will use the funds received from this final
        payment to promptly pay in full all of the signer's laborers, subcontractors, materialmen, and
        suppliers for all work, materials, equipment, or services provided for or to the above referenced
        project up to the date of this waiver and release.
    </p>
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p class="waiver-foot">
    Statutory form per Tex. Prop. Code &sect; 53.284(e); substantial compliance required by
    &sect;&sect; 53.281(b), 53.284(a). A person may not require this unconditional waiver unless the
    claimant received payment in good and sufficient funds (&sect; 53.283).
    Form LW-TX-UF v{{ $waiver['form']['template_version'] }}.
</p>
