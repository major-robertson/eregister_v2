{{--
    Wyoming statutory form, LIEN WAIVER, Wyo. Stat. § 29-10-101(b): "The
    form for waiver of a lien shall be completed in substantially the
    following form" (substantial, not verbatim, compliance). Wyoming
    prescribes this SINGLE form for all payments (no conditional or
    progress/final variants), so both unconditional kinds render this body.

    Statutory print order is preserved: the mandatory claimant note, the
    LIEN WAIVER caption, the TO/PROJECT/FROM/DATE/PAYMENT header, the
    operative waiver paragraph (reproduced verbatim, including the retainage
    reservation blank, the unpaid-sum reservation blank, and BOTH
    dishonored-payment sentences: the waiver survives dishonor of
    uncertified funds the claimant accepted, but does not apply if payment
    tendered by the owner is dishonored or revoked), the By/Title/Date
    signature block, and the notarial acknowledgment. The statute prints the
    claimant note in sentence case, so the .waiver-notice box disables the
    caps transform.

    The retainage and unpaid-sum blanks have no payload fields; they render
    as ruled blanks the claimant completes before signing (the waiver is
    wet-signed anyway: the acknowledgment is part of the form, so e-sign is
    disabled for WY and $esign is null in practice). This body uses the
    statutory header instead of _identification-fields. The exceptions
    payload field has no statutory home on this form (reservations belong
    in the unpaid-sum blank), so it is intentionally not rendered.

    Statutory text verified against the Wyoming Legislature's official Title
    29 statutes PDF (wyoleg.gov, fetched July 2026) and FindLaw (current
    through Jan. 1, 2024): identical. No amendments 2019–2026.
--}}
<div class="waiver-notice" style="text-transform: none;">
    Note to lien claimant: Signing this form has legal implications. If you have any questions regarding
    how to complete this form or whether it has been properly completed, you should consult an attorney.
</div>

<div class="waiver-title">Lien Waiver</div>
<div class="waiver-statute">Wyoming Statutes § 29-10-101(b)</div>

<table class="waiver-fields">
    <tr>
        <td class="label">TO:</td>
        <td class="value">{{ $waiver['customer']['company'] ?? ($waiver['customer']['name'] ?? ($waiver['owner']['company'] ?? ($waiver['owner']['name'] ?? ''))) }}&nbsp;</td>
    </tr>
    <tr>
        <td class="label">PROJECT:</td>
        <td class="value">{{ collect([$waiver['project']['name'] ?? null, $waiver['project']['address_line'] ?? null])->filter()->implode(', ') }}&nbsp;</td>
    </tr>
    <tr>
        <td class="label">FROM:</td>
        <td class="value">{{ $waiver['claimant']['company'] ?? ($waiver['claimant']['name'] ?? '') }}&nbsp;</td>
    </tr>
    <tr>
        <td class="label">DATE:</td>
        <td class="value">{{ $waiver['date'] ?? '' }}&nbsp;</td>
    </tr>
    <tr>
        <td class="label">PAYMENT:</td>
        <td class="value">${{ $waiver['amount'] ?? '' }}&nbsp;</td>
    </tr>
</table>

<div class="waiver-body">
    <p>
        In consideration of the PAYMENT received to date, the undersigned does hereby waive, release, and
        relinquish any and all claim and/or right of lien against the project and the real property
        improvements thereto for labor and/or materials furnished for use in construction of the project;
        provided however, the undersigned reserves all claims and/or rights of lien as to monies withheld as
        retainage in the amount of $<span class="fill">&nbsp;</span>, and any labor and/or materials
        hereafter furnished for which payment has not yet been made. The undersigned has not been paid the
        sum of $<span class="fill">&nbsp;</span> for work performed and/or materials provided under contract
        on this project and retains the right to file a lien against the property and pursue any and all
        actions to recover the full amount due, including any and all equitable claims. The undersigned
        acknowledges receipt of payment for work performed or materials provided and acknowledges that this
        waiver may be relied upon by the owner even if the undersigned accepts payment in uncertified funds
        and such payment is subsequently dishonored or revoked, in which case this lien waiver shall remain
        in full force and effect. The foregoing waiver shall not apply, however, if payment tendered by the
        owner is dishonored or revoked.
    </p>
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p style="font-size: 8pt; color: #333; margin: 2px 0 0 0;">subcontractor/materialman/employee</p>

<div class="execution-block">
    <table style="border-collapse: collapse; margin: 0 0 8px 0;">
        <tr>
            <td style="padding-right: 8px;">STATE OF <span class="fill">&nbsp;</span></td>
            <td>)</td>
            <td style="padding-left: 4px;">&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>)</td>
            <td style="padding-left: 4px;">ss.</td>
        </tr>
        <tr>
            <td>COUNTY OF <span class="fill">&nbsp;</span></td>
            <td>)</td>
            <td>&nbsp;</td>
        </tr>
    </table>
    <p>
        This instrument was acknowledged before me on this <span class="fill" style="min-width: 40px;">&nbsp;</span>
        day of <span class="fill" style="min-width: 110px;">&nbsp;</span>, 20<span class="fill" style="min-width: 30px;">&nbsp;</span>,
        by <span class="fill">{{ $waiver['signer']['name'] ?? '' }}</span> (name of person) as lien claimant
        or <span class="fill">{{ $waiver['signer']['title'] ?? '' }}</span> (title, position or type of
        authority granted by lien claimant) of
        <span class="fill-wide">{{ $waiver['claimant']['company'] ?? '' }}</span> (lien claimant).
    </p>
    <p>
        IN WITNESS THEREOF, I have hereunto set my hand and affixed my official seal on the day and year
        last above written.
    </p>
    <table class="sig-table" style="margin-top: 8px;">
        <tr>
            <td style="width: 55%;">
                <div class="sig-line">&nbsp;</div>
                <div class="sig-caption">Notarial officer</div>
            </td>
            <td style="width: 45%;">
                <div class="sig-line">&nbsp;</div>
                <div class="sig-caption">My Commission Expires:</div>
            </td>
        </tr>
    </table>
    <p style="margin: 10px 0 0 0;">Seal:</p>
</div>

<p class="waiver-foot">
    Statutory lien waiver form under Wyo. Stat. § 29-10-101(b): Wyoming prescribes this single form,
    completed in substantially the statutory form, for all payments; retainage and any sum not yet paid are
    reserved in the blanks above, and the notarial acknowledgment is part of the statutory form. Form
    LW-WY-LW v{{ $waiver['form']['template_version'] }}.
</p>
