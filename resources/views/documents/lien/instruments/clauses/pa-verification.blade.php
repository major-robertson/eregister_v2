{{--
    Pennsylvania verification for filings with the prothonotary (the
    mechanics' lien claim and its satisfaction). Pa.R.C.P. 76 defines
    "verified" as "supported by oath or affirmation or made subject to the
    penalties of 18 Pa.C.S. § 4904 relating to unsworn falsification to
    authorities"; the last sentence keeps that reference verbatim, and the
    first states the facts are true upon the signer's knowledge, information
    and belief, the standard of Pa.R.C.P. 1024(a). No notary: the state file
    sets execution.statement false, so the shared execution block prints only
    the signature lines under this paragraph. Checked against
    pacodeandbulletin.gov (Rules 76 and 1024) and palegis.us (18 Pa.C.S.
    § 4904) on 2026-09-30; never edit without re-checking them.
--}}
@php
    $form = $doc['form'];
    $signer = $doc['signer'];
    $claimantName = $doc['parties']['claimant']['display_name'] ?? null;
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
<div class="keep" style="margin-top: 12pt;">
    <p class="center"><strong>VERIFICATION</strong></p>
    <p>I, {!! $blank($signer['name'], 'fill fill-wide') !!}, {!! $blank($signer['title'], 'fill fill-mid') !!} of {!! $blank($claimantName, 'fill fill-wide') !!}, verify that the statements made in the foregoing {{ $form['title'] }} are true and correct to the best of my knowledge, information and belief. I understand that false statements herein are made subject to the penalties of 18 Pa.C.S. § 4904 relating to unsworn falsification to authorities.</p>
</div>
