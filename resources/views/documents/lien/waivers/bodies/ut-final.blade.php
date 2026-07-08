{{--
    Utah statutory form: Utah Waiver and Release Upon Final Payment, Utah
    Code § 38-1a-802(4)(c). A waiver meets the statute if it is in
    substantially this form (§ 38-1a-802(4)(a)); statutory text is reproduced
    verbatim with blanks bound to payload fields. Despite the title, the form
    is condition-styled: effective only once the undersigned endorses a check
    in the referenced Payment Amount and the check is paid by the depository
    institution on which it is drawn; § 38-1a-802(3) voids the waiver if the
    check fails to clear. The statute prescribes its own header fields (no
    Payment Period on the final form), so the _identification-fields partial
    is skipped.
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">State of Utah: Statutory form, Utah Code &sect; 38-1a-802(4)(c)</div>

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
        To the extent provided below, this document becomes effective to release and the undersigned is
        considered to waive any notice of lien or right under Utah Code Ann., Title 38, Chapter 1a,
        Preconstruction and Construction Liens, or any bond right under Utah Code Ann., Title 14,
        Contractors' Bonds, or Section 63G-6a-1103 related to payment rights the undersigned has on the
        above described Property once:
    </p>
    <p style="margin-left: 24px;">
        (1) the undersigned endorses a check in the above referenced Payment Amount payable to the
        undersigned; and
    </p>
    <p style="margin-left: 24px;">
        (2) the check is paid by the depository institution on which it is drawn.
    </p>
    <p>
        This waiver and release applies to the final payment for the work, materials, equipment, or
        combination of work, materials, and equipment furnished by the undersigned to the Property or to
        the Undersigned's Customer.
    </p>
    @if (!empty($waiver['exceptions']))
        <p><strong>Additional exceptions reserved by the undersigned:</strong> {{ $waiver['exceptions'] }}</p>
    @endif
    <p>
        The undersigned warrants that the undersigned either has already paid or will use the money the
        undersigned receives from the final payment promptly to pay in full all the undersigned's
        laborers, subcontractors, materialmen, and suppliers for all work, materials, equipment, or
        combination of work, materials, and equipment that are the subject of this waiver and release.
    </p>
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p class="waiver-foot">
    Statutory form per Utah Code &sect; 38-1a-802(4)(c); substantial compliance permitted by
    &sect; 38-1a-802(4)(a). Enforceable only if the claimant receives payment of the amount identified
    (&sect; 38-1a-802(2)(b)). If the check fails to clear the depository institution on which it is
    drawn for any reason, this waiver and release is void (&sect; 38-1a-802(3)).
    Form LW-UT-FP v{{ $waiver['form']['template_version'] }}.
</p>
