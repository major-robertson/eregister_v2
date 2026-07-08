{{--
    Nevada statutory form: Unconditional Waiver and Release Upon Progress
    Payment, NRS 108.2457(5)(b). Nevada allows NO deviation: subsection 5
    makes a waiver "unenforceable unless it is in the following forms in the
    following circumstances" (no substantially-similar allowance), so the
    statutory text is reproduced verbatim with only the blanks bound.

    The statutory Notice must appear "in type at least as large as the
    largest type otherwise on the document"; the shell caps all text at
    12pt and the .waiver-notice box renders 12pt bold, satisfying the rule.
    The statute prints this Notice in sentence case (NOT caps) and places it
    after the execution block, so text-transform is disabled and the box
    follows the signature area, matching the statutory print order. Note the
    progress-payment Notice wording differs from the final-payment one:
    "...enforceable against you if you sign it to the extent of the Payment
    Amount or the amount received." Do NOT add a notary or witness block.
    Verified against leg.state.nv.us (July 2026).
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">Nevada Revised Statutes § 108.2457(5)(b)</div>

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
</table>

<div class="waiver-body">
    <p>
        The undersigned has been paid and has received a progress payment in the above-referenced Payment
        Amount for all work, materials and equipment the undersigned furnished to the Customer for the
        above-described Property and does hereby waive and release any notice of lien, any private bond
        right, any claim for payment and any rights under any similar ordinance, rule or statute related
        to payment rights that the undersigned has on the above-described Property to the following
        extent:
    </p>
    <p>
        This release covers a progress payment for the work, materials and equipment furnished by the
        undersigned to the Property or to the Undersigned's Customer which are the subject of the Invoice
        or Payment Application, but only to the extent of the Payment Amount or such portion of the
        Payment Amount as the undersigned is actually paid, and does not cover any retention withheld, any
        items, modifications or changes pending approval, disputed items and claims, or items furnished
        that are not paid. The undersigned warrants that he or she either has already paid or will use the
        money received from this progress payment promptly to pay in full all laborers, subcontractors,
        materialmen and suppliers for all work, materials or equipment that are the subject of this waiver
        and release.
    </p>
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<div class="waiver-notice" style="text-transform: none; margin-top: 18px;">
    Notice: This document waives rights unconditionally and states that you have been paid for giving up
    those rights. This document is enforceable against you if you sign it to the extent of the Payment
    Amount or the amount received. If you have not been paid, use a conditional release form.
</div>

<p class="waiver-foot">
    Exact statutory form under NRS 108.2457(5)(b): Nevada requires the waiver to be in this form to be
    enforceable, and the Notice above must appear in type at least as large as the largest type otherwise
    on the document. Notarization and witnessing are not required. Form LW-NV-UP
    v{{ $waiver['form']['template_version'] }}.
</p>
