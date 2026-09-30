{{--
    The affidavit and jurat of the Ala. Code § 35-11-213 form, printed after
    the claimant's signature (clauses.after_execution). The affiant (the
    signer) swears before a notary public "in and for the county of ___,
    State of ___" wherever the affiant signs (§ 35-11-214 lets an officer
    outside Alabama give the oath), signs as Affiant, and the notary
    completes the jurat and signs under seal (§ 36-20-72). Verbatim from
    alison.legislature.state.al.us, verified 2026-09-30, except that the
    form's "19__" year blank prints as "20__". Never edit the wording without
    re-checking the statute.
--}}
@php
    $affiant = $doc['signer']['name'];
@endphp
<div class="notary">
    <p>Before me, <span class="fill fill-wide">&nbsp;</span>, a notary public in and for the county of <span class="fill fill-mid">&nbsp;</span>, State of <span class="fill fill-mid">&nbsp;</span>, personally appeared {!! $affiant !== null ? e($affiant) : '<span class="fill fill-wide">&nbsp;</span>' !!}, who being duly sworn, doth depose and say: That he has personal knowledge of the facts set forth in the foregoing statement of lien, and that the same are true and correct to the best of his knowledge and belief.</p>

    <table class="sig-table">
        <tr>
            <td style="width: 58%;">
                <div class="sig-line">&nbsp;</div>
                <div class="sig-caption">Affiant</div>
            </td>
            <td style="width: 42%;">
                <div class="sig-line">{!! $affiant !== null ? e($affiant) : '&nbsp;' !!}</div>
                <div class="sig-caption">Printed name</div>
            </td>
        </tr>
    </table>

    <p style="margin-top: 10pt;">Subscribed and sworn to before me on this the <span class="fill fill-short">&nbsp;</span> day of <span class="fill fill-mid">&nbsp;</span>, 20<span class="fill fill-short">&nbsp;</span>, by said affiant.</p>

    @include('documents.lien._parts.notary-lines', ['signatureCaption' => 'Notary Public'])
</div>
