{{--
    Texas statutory form: Conditional Waiver and Release on Final Payment,
    Tex. Prop. Code § 53.284(d). A waiver is unenforceable unless it
    substantially complies with this form (§ 53.284(a)); statutory text is
    reproduced verbatim with blanks bound to payload fields. Effective only
    when the identified check has been properly endorsed and paid by the bank
    on which it is drawn.
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">State of Texas: Statutory form, Tex. Prop. Code &sect; 53.284(d)</div>

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
        On receipt by the signer of this document of a check from
        <span class="fill">{{ $waiver['check_maker'] ?? '' }}</span> (maker of check) in the sum of
        $<span class="fill">{{ $waiver['amount'] ?? '' }}</span> payable to
        <span class="fill">{{ $waiver['claimant']['company'] ?? '' }}</span> (payee or payees of check)
        and when the check has been properly endorsed and has been paid by the bank on which it is drawn,
        this document becomes effective to release any mechanic's lien right, any right arising from a
        payment bond that complies with a state or federal statute, any common law payment bond right, any
        claim for payment, and any rights under any similar ordinance, rule, or statute related to claim or
        payment rights for persons in the signer's position that the signer has on the property of
        <span class="fill">{{ $waiver['owner']['company'] ?? ($waiver['owner']['name'] ?? '') }}</span> (owner)
        located at
        <span class="fill-wide">{{ $waiver['project']['address_line'] ?? '' }}</span> (location)
        to the following extent: <span class="fill-wide">&nbsp;</span> (job description).
    </p>
    <p>
        This release covers the final payment to the signer for all labor, services, equipment, or
        materials furnished to the property or to
        <span class="fill">{{ $waiver['customer']['company'] ?? ($waiver['customer']['name'] ?? '') }}</span>
        (person with whom signer contracted).
    </p>
    @if (!empty($waiver['exceptions']))
        <p><strong>Additional exceptions reserved by the signer:</strong> {{ $waiver['exceptions'] }}</p>
    @endif
    <p>
        Before any recipient of this document relies on this document, the recipient should verify evidence
        of payment to the signer.
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
    Statutory form per Tex. Prop. Code &sect; 53.284(d); substantial compliance required by
    &sect;&sect; 53.281(b), 53.284(a). A conditional release is effective only if evidence of payment to
    the claimant exists (&sect; 53.281(b)(3)). Form LW-TX-CF v{{ $waiver['form']['template_version'] }}.
</p>
