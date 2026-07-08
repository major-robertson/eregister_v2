{{--
    Nevada statutory form: Unconditional Waiver and Release Upon Final
    Payment, NRS 108.2457(5)(d). Nevada allows NO deviation: subsection 5
    makes a waiver "unenforceable unless it is in the following forms in the
    following circumstances" (no substantially-similar allowance), so the
    statutory text is reproduced verbatim with only the blanks bound.
    "Amount of Disputed Claims" is bound to the exceptions field; unlike the
    conditional final form there is no "Payment Period" field.

    The statutory Notice must appear "in type at least as large as the
    largest type otherwise on the document"; the shell caps all text at
    12pt and the .waiver-notice box renders 12pt bold, satisfying the rule.
    The statute prints this Notice in sentence case (NOT caps) and places it
    after the execution block, so text-transform is disabled and the box
    follows the signature area, matching the statutory print order. Note the
    final-payment Notice wording differs from the progress-payment one:
    "...enforceable against you if you sign it, even if you have not been
    paid." Do NOT add a notary or witness block; the statute prescribes
    neither, and extra execution formalities deviate from the mandatory
    form. Verified against leg.state.nv.us (July 2026).
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">Nevada Revised Statutes § 108.2457(5)(d)</div>

<table class="waiver-fields">
    <tr>
        <td class="label">Property Name:</td>
        <td class="value">{{ $waiver['project']['name'] ?? '' }}&nbsp;</td>
    </tr>
    <tr>
        <td class="label">Property Location:</td>
        <td class="value">{{ $waiver['project']['address_line'] ?? '' }}&nbsp;</td>
    </tr>
    <tr>
        <td class="label">Undersigned's Customer:</td>
        <td class="value">{{ $waiver['customer']['company'] ?? ($waiver['customer']['name'] ?? '') }}&nbsp;</td>
    </tr>
    <tr>
        <td class="label">Invoice/Payment Application Number:</td>
        <td class="value">{{ $waiver['invoice_number'] ?? '' }}&nbsp;</td>
    </tr>
    <tr>
        <td class="label">Payment Amount:</td>
        <td class="value">{{ ($waiver['amount'] ?? null) !== null ? '$'.$waiver['amount'] : '' }}&nbsp;</td>
    </tr>
    <tr>
        <td class="label">Amount of Disputed Claims:</td>
        <td class="value">{{ $waiver['exceptions'] ?? '' }}&nbsp;</td>
    </tr>
</table>

<div class="waiver-body">
    <p>
        The undersigned has been paid in full for all work, materials and equipment furnished to the
        Customer for the above-described Property and does hereby waive and release any notice of lien,
        any private bond right, any claim for payment and any rights under any similar ordinance, rule or
        statute related to payment rights that the undersigned has on the above-described Property, except
        for the payment of Disputed Claims, if any, noted above. The undersigned warrants that he or she
        either has already paid or will use the money received from this final payment promptly to pay in
        full all laborers, subcontractors, materialmen and suppliers for all work, materials and equipment
        that are the subject of this waiver and release.
    </p>
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<div class="waiver-notice" style="text-transform: none; margin-top: 18px;">
    Notice: This document waives rights unconditionally and states that you have been paid for giving up
    those rights. This document is enforceable against you if you sign it, even if you have not been paid.
    If you have not been paid, use a conditional release form.
</div>

<p class="waiver-foot">
    Exact statutory form under NRS 108.2457(5)(d): Nevada requires the waiver to be in this form to be
    enforceable, and the Notice above must appear in type at least as large as the largest type otherwise
    on the document. Notarization and witnessing are not required. Form LW-NV-UF
    v{{ $waiver['form']['template_version'] }}.
</p>
