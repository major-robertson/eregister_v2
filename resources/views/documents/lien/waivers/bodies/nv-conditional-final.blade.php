{{--
    Nevada statutory form: Conditional Waiver and Release Upon Final
    Payment, NRS 108.2457(5)(c). Nevada allows NO deviation: subsection 5
    makes a waiver "unenforceable unless it is in the following forms in the
    following circumstances" (no substantially-similar allowance), so the
    statutory text is reproduced verbatim with only the blanks bound. This
    is the only one of the four Nevada forms with a "Payment Period" field
    (bound to the through-date). "Amount of Disputed Claims" is bound to the
    exceptions field. Do NOT add a notary or witness block; the statute
    prescribes neither. Verified against leg.state.nv.us (July 2026).
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">Nevada Revised Statutes § 108.2457(5)(c)</div>

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
        <td class="label">Payment Period:</td>
        <td class="value">{{ $waiver['through_date'] ?? '' }}&nbsp;</td>
    </tr>
    <tr>
        <td class="label">Amount of Disputed Claims:</td>
        <td class="value">{{ $waiver['exceptions'] ?? '' }}&nbsp;</td>
    </tr>
</table>

<div class="waiver-body">
    <p>
        Upon receipt by the undersigned of a check in the above-referenced Payment Amount payable to the
        undersigned, and when the check has been properly endorsed and has been paid by the bank on which
        it is drawn, this document becomes effective to release and the undersigned shall be deemed to
        waive any notice of lien, any private bond right, any claim for payment and any rights under any
        similar ordinance, rule or statute related to payment rights that the undersigned has on the
        above-described Property to the following extent:
    </p>
    <p>
        This release covers the final payment to the undersigned for all work, materials or equipment
        furnished by the undersigned to the Property or to the Undersigned's Customer and does not cover
        payment for Disputed Claims, if any. Before any recipient of this document relies on it, the
        recipient should verify evidence of payment to the undersigned. The undersigned warrants that he
        or she either has already paid or will use the money received from the final payment promptly to
        pay in full all laborers, subcontractors, materialmen and suppliers for all work, materials or
        equipment that are the subject of this waiver and release.
    </p>
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p class="waiver-foot">
    Exact statutory form under NRS 108.2457(5)(c): Nevada requires the waiver to be in this form to be
    enforceable. Notarization and witnessing are not required. Form LW-NV-CF
    v{{ $waiver['form']['template_version'] }}.
</p>
