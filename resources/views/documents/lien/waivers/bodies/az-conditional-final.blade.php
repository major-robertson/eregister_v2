{{--
    Arizona statutory form: Conditional Waiver and Release on Final Payment,
    A.R.S. § 33-1008(D)(3). Statutory text reproduced verbatim (substantial
    compliance required) with only the blanks bound. Note the statute's exact
    wording differences from the progress form: "the person should verify"
    (not "that person"), and the warranty says "pay in full all his laborers"
    (not "all of his"). Do NOT add a notary block or extra terms.
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">Arizona Revised Statutes § 33-1008(D)(3)</div>

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
        rule or statute related to claim or payment rights for persons in the undersigned's position, the
        undersigned has on the job of
        <span class="fill-wide">{{ $waiver['owner']['company'] ?? ($waiver['owner']['name'] ?? '') }}</span>
        <span style="font-size: 8pt; color: #444;">(owner)</span>
        located at
        <span class="fill-wide">{{ $waiver['project']['address_line'] ?? '' }}</span>
        <span style="font-size: 8pt; color: #444;">(job description)</span>.
    </p>
    <p>
        This release covers the final payment to the undersigned for all labor, services, equipment or
        materials furnished to the jobsite or to
        <span class="fill-wide">{{ $waiver['customer']['company'] ?? ($waiver['customer']['name'] ?? '') }}</span>
        <span style="font-size: 8pt; color: #444;">(person with whom undersigned contracted)</span>,
        except for disputed claims in the amount of
        $<span class="fill">{{ $waiver['exceptions'] ?? '' }}</span>. Before any recipient of this document
        relies on it, the person should verify evidence of payment to the undersigned.
    </p>
    <p>
        The undersigned warrants that he either has already paid or will use the monies he receives from
        this final payment to promptly pay in full all his laborers, subcontractors, materialmen and
        suppliers for all work, materials, equipment or services provided for or to the above referenced
        project up to the date of this waiver.
    </p>
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p class="waiver-foot">
    Statutory form under A.R.S. § 33-1008(D)(3) (substantial compliance). Notarization and witnessing are
    not required. This conditional release binds the claimant only on evidence of payment
    (A.R.S. § 33-1008(A)). Form LW-AZ-CF v{{ $waiver['form']['template_version'] }}.
</p>
