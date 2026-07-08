{{--
    Arizona statutory form: Unconditional Waiver and Release on Final
    Payment, A.R.S. § 33-1008(D)(4). Statutory text reproduced verbatim
    (substantial compliance required) with only the blanks bound. The NOTICE
    is mandatory and must appear "in type at least as large as the largest
    type otherwise on the document"; the shell caps all text at 12pt and the
    .waiver-notice box renders 12pt bold caps, satisfying the rule. Note the
    statute's exact wording: "waive and release any right to mechanic's lien"
    and a warranty with no "up to the date of this waiver" tail. Do NOT add a
    notary block or extra terms.
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">Arizona Revised Statutes § 33-1008(D)(4)</div>

<div class="waiver-notice">
    NOTICE: THIS DOCUMENT WAIVES RIGHTS UNCONDITIONALLY AND STATES THAT YOU HAVE BEEN PAID FOR GIVING UP
    THOSE RIGHTS. THIS DOCUMENT IS ENFORCEABLE AGAINST YOU IF YOU SIGN IT, EVEN IF YOU HAVE NOT BEEN PAID.
    IF YOU HAVE NOT BEEN PAID, USE A CONDITIONAL RELEASE FORM.
</div>

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
        The undersigned has been paid in full for all labor, services, equipment or material furnished to
        the jobsite or to
        <span class="fill-wide">{{ $waiver['customer']['company'] ?? ($waiver['customer']['name'] ?? '') }}</span>
        <span style="font-size: 8pt; color: #444;">(person with whom undersigned contracted)</span>,
        on the job of
        <span class="fill-wide">{{ $waiver['owner']['company'] ?? ($waiver['owner']['name'] ?? '') }}</span>
        <span style="font-size: 8pt; color: #444;">(owner)</span>
        located at
        <span class="fill-wide">{{ $waiver['project']['address_line'] ?? '' }}</span>
        <span style="font-size: 8pt; color: #444;">(job description)</span>
        and does hereby waive and release any right to mechanic's lien, any state or federal statutory bond
        right, any private bond right, any claim for payment and any rights under any similar ordinance,
        rule or statute related to claim or payment rights for persons in the undersigned's position,
        except for disputed claims for extra work in the amount of
        $<span class="fill">{{ $waiver['exceptions'] ?? '' }}</span>.
    </p>
    <p>
        The undersigned warrants that he either has already paid or will use the monies he receives from
        this final payment to promptly pay in full all of his laborers, subcontractors, materialmen and
        suppliers for all work, materials, equipment or services provided for or to the above referenced
        project.
    </p>
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p class="waiver-foot">
    Statutory form under A.R.S. § 33-1008(D)(4) (substantial compliance). Notarization and witnessing are
    not required. Form LW-AZ-UF v{{ $waiver['form']['template_version'] }}.
</p>
