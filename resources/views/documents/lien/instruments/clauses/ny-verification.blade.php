{{--
    N.Y. Lien Law § 9, last paragraph: "The notice must be verified by the
    lienor or his agent, to the effect that the statements therein contained
    are true to his knowledge except as to the matters therein stated to be
    alleged on information and belief, and that as to those matters he
    believes it to be true." The paragraph below keeps the statute's words in
    the first person (verified against nysenate.gov on 2026-09-30). It
    replaces the generic sworn statement (execution.statement is false); the
    notary's jurat follows the signature. The venue is where the oath is
    taken, often outside New York, so the state is left blank, as it is in
    the jurat. Never edit the wording without re-checking the statute.
--}}
@php
    $lienor = $doc['parties']['claimant']['display_name'] ?? null;
    $signer = $doc['signer'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
<div class="keep" style="margin-top: 10pt;">
    <p class="center"><strong>VERIFICATION</strong></p>
    <table style="border-collapse: collapse; margin: 0 0 6pt 0;">
        <tr>
            <td class="venue-lines" style="padding: 0 8pt 0 0; vertical-align: middle;">STATE OF <span class="fill fill-mid">&nbsp;</span><br>COUNTY OF <span class="fill fill-mid">&nbsp;</span></td>
            <td style="padding: 0; vertical-align: middle;">ss.:</td>
        </tr>
    </table>
    <p>I, {!! $blank($signer['name'], 'fill fill-wide') !!}, being duly sworn, depose and say that I am the {!! $blank($signer['title'], 'fill fill-mid') !!} of {!! $blank($lienor, 'fill fill-wide') !!}, the lienor named in the foregoing notice of lien; that I have read the notice of lien and know its contents; and that the statements therein contained are true to my knowledge except as to the matters therein stated to be alleged on information and belief, and that as to those matters I believe it to be true.</p>
</div>
