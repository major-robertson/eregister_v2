{{--
    The suggested notarial of the N.J.S.A. 2A:44A-8 lien claim form,
    verbatim from lis.njleg.state.nj.us (verified 2026-09-30): the corporate
    or limited liability version for an entity claimant (the signer is
    "duly sworn/affirmed" as to authority and acknowledges the claim), the
    individual version otherwise. It follows the jurat of the shell's
    execution block because N.J.S.A. 2A:44A-6(a)(1) wants the claim "signed,
    acknowledged and verified by oath". Two deviations from the printed
    form: the venue lines are blank, since the claimant signs before a
    notary wherever it is and the form's "STATE OF NEW JERSEY" would then be
    false; and the notary lines add the printed name and commission
    expiration that N.J.S.A. 46:26A-3(a)(4) and 52:7-19(a) require. Never
    edit the wording without re-checking the statute.
--}}
@php
    $claimant = $doc['parties']['claimant'];
    $company = $claimant['company'] ?? null;
    $isEntity = $company !== null && $company !== ($claimant['name'] ?? null);
    $appeared = $doc['signer']['name'] ?? ($isEntity ? null : ($claimant['name'] ?? null));
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
<div class="notary">
    @if ($isEntity)
        <p><strong>SUGGESTED NOTARIAL FOR CORPORATE OR LIMITED LIABILITY CLAIMANT:</strong></p>
        <div class="venue-lines">STATE OF <span class="fill fill-mid">&nbsp;</span><br>COUNTY OF <span class="fill fill-mid">&nbsp;</span> ss:</div>
        <p style="text-align: left;">On this <span class="fill fill-short">&nbsp;</span> day of <span class="fill fill-mid">&nbsp;</span> 20<span class="fill fill-short">&nbsp;</span>, before me, the subscriber, personally appeared {!! $blank($appeared, 'fill fill-wide') !!} who, I am satisfied is the Secretary (or other officer/manager/agent) of the Corporation (partnership or limited liability company) named herein and who by me duly sworn/affirmed, asserted authority to act on behalf of the Corporation (partnership or limited liability company) and who, by virtue of its Bylaws, or Resolution of its Board of Directors (or partnership or operating agreement) executed the within instrument on its behalf, and thereupon acknowledged that claimant signed, sealed and delivered same as claimant's act and deed, for the purposes herein expressed.</p>
    @else
        <p><strong>SUGGESTED NOTARIAL FOR INDIVIDUAL CLAIMANT:</strong></p>
        <div class="venue-lines">STATE OF <span class="fill fill-mid">&nbsp;</span><br>COUNTY OF <span class="fill fill-mid">&nbsp;</span> ss:</div>
        <p style="text-align: left;">On this <span class="fill fill-short">&nbsp;</span> day of <span class="fill fill-mid">&nbsp;</span> 20<span class="fill fill-short">&nbsp;</span>, before me, the subscriber, personally appeared {!! $blank($appeared, 'fill fill-wide') !!} who, I am satisfied, is/are the person(s) named in and who executed the within instrument, and thereupon acknowledged that claimant(s) signed, sealed and delivered the same as claimant's (s') act and deed, for the purposes therein expressed.</p>
    @endif
    @include('documents.lien._parts.notary-lines', ['signatureCaption' => 'NOTARY PUBLIC'])
</div>
