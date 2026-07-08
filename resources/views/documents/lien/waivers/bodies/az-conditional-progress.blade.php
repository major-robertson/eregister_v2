{{--
    Arizona statutory form: Conditional Waiver and Release on Progress
    Payment, A.R.S. § 33-1008(D)(1). Statutory text reproduced verbatim
    (substantial compliance required) with only the blanks bound to payload
    fields. Do NOT add a notary block, extra conditions, or scope language;
    deviations risk making the waiver unenforceable.
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">Arizona Revised Statutes § 33-1008(D)(1)</div>

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
        On receipt by the undersigned of a check from
        <span class="fill">{{ $waiver['check_maker'] ?? '' }}</span>
        <span style="font-size: 8pt; color: #444;">(maker of check)</span>
        in the sum of $<span class="fill">{{ $waiver['amount'] ?? '' }}</span>
        <span style="font-size: 8pt; color: #444;">(amount of check)</span>
        payable to
        <span class="fill-wide">{{ $waiver['claimant']['company'] ?? '' }}</span>
        <span style="font-size: 8pt; color: #444;">(payee or payees of check)</span>
        and when the check has been properly endorsed and has been paid by the bank on which it is drawn,
        this document becomes effective to release any mechanic's lien, any state or federal statutory bond
        right, any private bond right, any claim for payment and any rights under any similar ordinance,
        rule or statute related to claim or payment rights for persons in the undersigned's position that
        the undersigned has on the job of
        <span class="fill-wide">{{ $waiver['owner']['company'] ?? ($waiver['owner']['name'] ?? '') }}</span>
        <span style="font-size: 8pt; color: #444;">(owner)</span>
        located at
        <span class="fill-wide">{{ $waiver['project']['address_line'] ?? '' }}</span>
        <span style="font-size: 8pt; color: #444;">(job description)</span>
        to the following extent. This release covers a progress payment for all labor, services, equipment
        or materials furnished to the jobsite or to
        <span class="fill-wide">{{ $waiver['customer']['company'] ?? ($waiver['customer']['name'] ?? '') }}</span>
        <span style="font-size: 8pt; color: #444;">(person with whom undersigned contracted)</span>,
        through <span class="fill">{{ $waiver['through_date'] ?? '' }}</span>
        <span style="font-size: 8pt; color: #444;">(date)</span>
        only and does not cover any retention, pending modifications and changes or items furnished after
        that date. Before any recipient of this document relies on it, that person should verify evidence
        of payment to the undersigned.
    </p>
    <p>
        The undersigned warrants that he either has already paid or will use the monies he receives from
        this progress payment to promptly pay in full all of his laborers, subcontractors, materialmen and
        suppliers for all work, materials, equipment or services provided for or to the above referenced
        project up to the date of this waiver.
    </p>
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p class="waiver-foot">
    Statutory form under A.R.S. § 33-1008(D)(1) (substantial compliance). Notarization and witnessing are
    not required. This conditional release binds the claimant only on evidence of payment
    (A.R.S. § 33-1008(A)). Form LW-AZ-CP v{{ $waiver['form']['template_version'] }}.
</p>
