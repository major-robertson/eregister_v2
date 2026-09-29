{{--
    Proof of service affidavit printed with the instrument where the statute
    makes it part of the claim (California, Cal. Civ. Code § 8416(a)(7):
    service on the owner by registered, certified or first-class mail with a
    certificate of mailing, § 8416(c)). The server completes it by hand
    after mailing.
--}}
@php
    $owner = $doc['parties']['owner'];
    $stateName = $doc['form']['state_name'];
@endphp
<div class="service-affidavit">
    <p class="center"><strong>PROOF OF SERVICE AFFIDAVIT</strong></p>
    <p>I, <span class="fill fill-wide">&nbsp;</span>, declare that I am over the age of eighteen years and not a party to this claim. On <span class="fill fill-mid">&nbsp;</span>, I served a copy of the foregoing {{ $doc['form']['title'] }}, including the Notice of Mechanics Lien, on the owner or reputed owner of the property described in it, @if ($owner && $owner['display_name']){{ $owner['display_name'] }}@else<span class="fill fill-wide">&nbsp;</span>@endif, at @if ($owner && $owner['address_line']){{ $owner['address_line'] }}@else<span class="fill fill-wide">&nbsp;</span>@endif, by:</p>
    <p><span class="box"></span> registered mail &nbsp; <span class="box"></span> certified mail &nbsp; <span class="box"></span> first-class mail, evidenced by a certificate of mailing, postage prepaid, addressed to the owner or reputed owner at the owner's or reputed owner's residence or place of business address or at the address shown by the building permit on file with the authority issuing a building permit for the work.</p>
    <p>I declare under penalty of perjury under the laws of the State of {{ $stateName }} that the foregoing is true and correct.</p>
    <p>Executed on <span class="fill fill-mid">&nbsp;</span>, at <span class="fill fill-wide">&nbsp;</span>.</p>
    <table class="sig-table">
        <tr>
            <td style="width: 58%;"><div class="sig-line">&nbsp;</div><div class="sig-caption">Signature of person serving</div></td>
            <td style="width: 42%;"><div class="sig-line">&nbsp;</div><div class="sig-caption">Printed name</div></td>
        </tr>
    </table>
</div>
