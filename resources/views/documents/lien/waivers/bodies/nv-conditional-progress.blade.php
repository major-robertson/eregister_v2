{{--
    Nevada statutory form: Conditional Waiver and Release Upon Progress
    Payment, NRS 108.2457(5)(a). Nevada allows NO deviation: subsection 5
    makes a waiver "unenforceable unless it is in the following forms in the
    following circumstances" (no substantially-similar allowance), so the
    statutory text is reproduced verbatim with only the blanks bound. The
    statutory Dated / (Company Name) / By / Its execution block is rendered
    by the shared signature partial. Do NOT add a notary or witness block;
    the statute prescribes neither, and extra execution formalities deviate
    from the mandatory form. Verified against leg.state.nv.us (July 2026).
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">Nevada Revised Statutes § 108.2457(5)(a)</div>

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
        Upon receipt by the undersigned of a check in the above-referenced Payment Amount payable to the
        undersigned, and when the check has been properly endorsed and has been paid by the bank on which
        it is drawn, this document becomes effective to release and the undersigned shall be deemed to
        waive any notice of lien, any private bond right, any claim for payment and any rights under any
        similar ordinance, rule or statute related to payment rights that the undersigned has on the
        above-described Property to the following extent:
    </p>
    <p>
        This release covers a progress payment for the work, materials or equipment furnished by the
        undersigned to the Property or to the Undersigned's Customer which are the subject of the Invoice
        or Payment Application, but only to the extent of the Payment Amount or such portion of the
        Payment Amount as the undersigned is actually paid, and does not cover any retention withheld, any
        items, modifications or changes pending approval, disputed items and claims, or items furnished
        that are not paid. Before any recipient of this document relies on it, the recipient should verify
        evidence of payment to the undersigned. The undersigned warrants that he or she either has already
        paid or will use the money received from this progress payment promptly to pay in full all
        laborers, subcontractors, materialmen and suppliers for all work, materials or equipment that are
        the subject of this waiver and release.
    </p>
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p class="waiver-foot">
    Exact statutory form under NRS 108.2457(5)(a): Nevada requires the waiver to be in this form to be
    enforceable. Notarization and witnessing are not required. Form LW-NV-CP
    v{{ $waiver['form']['template_version'] }}.
</p>
