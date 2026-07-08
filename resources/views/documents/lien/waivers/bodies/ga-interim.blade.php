{{--
    Georgia statutory form: Waiver and Release of Lien and Payment Bond
    Rights Upon Interim Payment, O.C.G.A. § 44-14-366(d) (SB 315, eff.
    1/1/2021; text verified against the enrolled bill). "Substantially
    follow" standard, and the whole waiver must be in at least 12 point font
    (boldface capitals no longer required), so every statutory passage is
    set at 12pt, the shell's maximum type size. Executed under hand and seal
    before a witness (no notary), which is why e-sign is disabled for GA.
    The NOTICE paragraph is mandatory: omitting it renders the form
    unenforceable and invalid. Per § 44-14-366(g) the waiver conclusively
    becomes effective on the earliest of actual receipt of the funds, a
    separate written acknowledgment of payment in full, or 90 days after
    execution unless an Affidavit of Nonpayment is timely filed.
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">State of Georgia: Statutory form, O.C.G.A. &sect; 44-14-366(d)</div>

<div class="waiver-body" style="font-size: 12pt; line-height: 1.4;">
    <p style="text-align: left;">
        STATE OF GEORGIA<br>
        COUNTY OF <span class="fill">{{ $waiver['project']['county'] ?? '' }}</span>
    </p>
    <p>
        THE UNDERSIGNED MECHANIC AND/OR MATERIALMAN HAS BEEN EMPLOYED BY
        <span class="fill">{{ $waiver['customer']['company'] ?? ($waiver['customer']['name'] ?? '') }}</span>
        (NAME OF CONTRACTOR) TO FURNISH <span class="fill-wide">&nbsp;</span>
        (DESCRIBE MATERIALS AND/OR LABOR) FOR THE CONSTRUCTION OF IMPROVEMENTS KNOWN AS
        <span class="fill">{{ $waiver['project']['name'] ?? '' }}</span>
        (TITLE OF THE PROJECT OR BUILDING) WHICH IS LOCATED IN THE CITY OF
        <span class="fill">{{ $waiver['project']['city'] ?? '' }}</span>, COUNTY OF
        <span class="fill">{{ $waiver['project']['county'] ?? '' }}</span>, AND IS OWNED BY
        <span class="fill">{{ $waiver['owner']['company'] ?? ($waiver['owner']['name'] ?? '') }}</span>
        (NAME OF OWNER) AND MORE PARTICULARLY DESCRIBED AS FOLLOWS:
    </p>
    @php
        $propertyLines = array_values(array_filter([
            $waiver['project']['address_line'] ?? null,
            $waiver['project']['legal_description'] ?? null,
        ]));
    @endphp
    @for ($i = 0; $i < 3; $i++)
        <div style="border-bottom: 1px solid #000; min-height: 19px; margin-bottom: 4px;">{{ $propertyLines[$i] ?? '' }}&nbsp;</div>
    @endfor
    <p>
        (DESCRIBE THE PROPERTY UPON WHICH THE IMPROVEMENTS WERE MADE BY USING EITHER A METES AND
        BOUNDS DESCRIPTION, THE LAND LOT DISTRICT, BLOCK AND LOT NUMBER, OR STREET ADDRESS OF THE
        PROJECT.)
    </p>
    <p>
        UPON THE RECEIPT OF THE SUM OF $<span class="fill">{{ $waiver['amount'] ?? '' }}</span>, THE
        MECHANIC AND/OR MATERIALMAN WAIVES AND RELEASES ANY AND ALL LIENS OR CLAIMS OF LIENS IT HAS
        UPON THE FOREGOING DESCRIBED PROPERTY OR ANY RIGHTS AGAINST ANY LABOR AND/OR MATERIAL BOND
        THROUGH THE DATE OF <span class="fill">{{ $waiver['through_date'] ?? '' }}</span> (DATE) AND
        EXCEPTING THOSE RIGHTS AND LIENS THAT THE MECHANIC AND/OR MATERIALMAN MIGHT HAVE IN ANY
        RETAINED AMOUNTS, ON ACCOUNT OF LABOR OR MATERIALS, OR BOTH, FURNISHED BY THE UNDERSIGNED TO
        OR ON ACCOUNT OF SAID CONTRACTOR FOR SAID BUILDING OR PREMISES.
    </p>
    <p>
        GIVEN UNDER HAND AND SEAL THIS <span class="fill" style="min-width: 60px;">&nbsp;</span> DAY OF
        <span class="fill">&nbsp;</span>, <span class="fill" style="min-width: 60px;">&nbsp;</span>.
    </p>
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])
<div class="sig-caption">(SEAL): the claimant's signature above is given under hand and seal.</div>

@include('documents.lien.waivers._witness-line')

<table class="sig-table">
    <tr>
        <td style="width: 100%;">
            <div class="sig-line">&nbsp;</div>
            <div class="sig-caption">(Address)</div>
        </td>
    </tr>
</table>

<div class="waiver-notice" style="margin-top: 14px; page-break-inside: avoid;">
    NOTICE: WHEN YOU EXECUTE AND SUBMIT THIS DOCUMENT, YOU SHALL BE CONCLUSIVELY DEEMED TO HAVE
    WAIVED AND RELEASED ANY AND ALL LIENS AND CLAIMS OF LIENS UPON THE FOREGOING DESCRIBED PROPERTY
    AND ANY RIGHTS REGARDING ANY LABOR OR MATERIAL BOND REGARDING THE SAID PROPERTY TO THE EXTENT
    (AND ONLY TO THE EXTENT) SET FORTH ABOVE, EVEN IF YOU HAVE NOT ACTUALLY RECEIVED SUCH PAYMENT,
    90 DAYS AFTER THE DATE STATED ABOVE UNLESS YOU FILE AN AFFIDAVIT OF NONPAYMENT PRIOR TO THE
    EXPIRATION OF SUCH 90 DAY PERIOD. THE FAILURE TO INCLUDE THIS NOTICE LANGUAGE ON THE FORM SHALL
    RENDER THE FORM UNENFORCEABLE AND INVALID AS A WAIVER AND RELEASE UNDER O.C.G.A. &sect; 44-14-366.
</div>

<p class="waiver-foot">
    Statutory form per O.C.G.A. &sect; 44-14-366(d), substantially following the prescribed language in
    at least 12-point font as required. Waivers and releases under this Code section are limited to lien
    and labor or material bond rights and do not affect other rights or remedies of the claimant
    (&sect; 44-14-366(a)). Effective per &sect; 44-14-366(g) upon the earliest of actual receipt of the
    funds, a separate written acknowledgment of payment in full, or 90 days after execution unless an
    Affidavit of Nonpayment is timely filed. Form LW-GA-IP v{{ $waiver['form']['template_version'] }}.
</p>
