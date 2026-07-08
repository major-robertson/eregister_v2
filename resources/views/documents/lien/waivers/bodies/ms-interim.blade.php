{{--
    Mississippi statutory form: Interim Waiver and Release Upon Payment,
    Miss. Code Ann. § 85-7-433(1). A waiver requested in exchange for or to
    induce any payment other than final payment "shall substantially follow"
    this form (§ 85-7-419(2)). The statute prints the form entirely in capital
    letters; statutory text is reproduced verbatim (in caps) with blanks bound
    to payload fields. Statutory element order preserved: body, signature,
    notary jurat, then the mandatory 60-day NOTICE, which must appear on the
    face of the form or the waiver is "unenforceable and invalid" under
    § 85-7-419. Excepts retained amounts; releases lien AND labor/material
    bond rights through the stated date. Must be sworn before a notary
    (e-sign disabled for MS).
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">State of Mississippi: Statutory form, Miss. Code Ann. &sect; 85-7-433(1)</div>

<div class="waiver-body">
    <p style="margin-bottom: 12px;">
        STATE OF MISSISSIPPI<br>
        COUNTY OF <span class="fill">{{ $waiver['project']['county'] ?? '' }}</span>
    </p>
    <p>
        THE UNDERSIGNED MECHANIC AND/OR MATERIALMAN HAS BEEN EMPLOYED BY
        <span class="fill">{{ $waiver['customer']['company'] ?? ($waiver['customer']['name'] ?? '') }}</span>
        (NAME OF CONTRACTOR) TO FURNISH
        <span class="fill-wide">&nbsp;</span> (DESCRIBE MATERIALS AND/OR LABOR)
        FOR THE CONSTRUCTION OF IMPROVEMENTS KNOWN AS
        <span class="fill">{{ $waiver['project']['name'] ?? '' }}</span>
        (TITLE OF THE PROJECT OR BUILDING) WHICH IS LOCATED IN THE CITY OF
        <span class="fill">{{ $waiver['project']['city'] ?? '' }}</span>, COUNTY OF
        <span class="fill">{{ $waiver['project']['county'] ?? '' }}</span>,
        AND IS OWNED BY
        <span class="fill">{{ $waiver['owner']['company'] ?? ($waiver['owner']['name'] ?? '') }}</span>
        (NAME OF OWNER) AND MORE PARTICULARLY DESCRIBED AS FOLLOWS:
    </p>
    <p>
        <span class="fill-wide">{{ $waiver['project']['legal_description'] ?? ($waiver['project']['address_line'] ?? '') }}</span><br>
        (DESCRIBE THE PROPERTY UPON WHICH THE IMPROVEMENTS WERE MADE BY USING EITHER A METES AND BOUNDS
        DESCRIPTION, THE LAND LOT DISTRICT, BLOCK AND LOT NUMBER, OR STREET ADDRESS OF THE PROJECT.)
    </p>
    <p>
        UPON THE RECEIPT OF THE SUM OF $<span class="fill">{{ $waiver['amount'] ?? '' }}</span>, THE MECHANIC
        AND/OR MATERIALMAN WAIVES AND RELEASES ANY AND ALL LIENS OR CLAIMS OF LIENS IT HAS UPON THE FOREGOING
        DESCRIBED PROPERTY OR ANY RIGHTS AGAINST ANY LABOR AND/OR MATERIAL BOND THROUGH THE DATE OF
        <span class="fill">{{ $waiver['through_date'] ?? '' }}</span> (DATE) AND EXCEPTING THOSE RIGHTS AND
        LIENS THAT THE MECHANIC AND/OR MATERIALMAN MIGHT HAVE IN ANY RETAINED AMOUNTS, ON ACCOUNT OF LABOR OR
        MATERIALS, OR BOTH, FURNISHED BY THE UNDERSIGNED TO OR ON ACCOUNT OF SAID CONTRACTOR FOR SAID BUILDING
        OR PREMISES.
    </p>
    @if (!empty($waiver['exceptions']))
        <p><strong>ADDITIONAL EXCEPTIONS RESERVED BY THE UNDERSIGNED:</strong> {{ $waiver['exceptions'] }}</p>
    @endif
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<div class="execution-block">
    <p style="margin: 0 0 8px 0;">
        SWORN TO AND SUBSCRIBED BEFORE ME, THIS THE
        <span class="fill" style="min-width: 45px;">&nbsp;</span> DAY OF
        <span class="fill">&nbsp;</span>, 20<span class="fill" style="min-width: 35px;">&nbsp;</span>.
    </p>
    <table class="sig-table" style="margin-top: 8px;">
        <tr>
            <td style="width: 55%;">
                <div class="sig-line">&nbsp;</div>
                <div class="sig-caption">NOTARY PUBLIC</div>
            </td>
            <td style="width: 45%;">&nbsp;</td>
        </tr>
    </table>
</div>

<div class="waiver-notice" style="margin-top: 16px; margin-bottom: 0;">
    NOTICE: WHEN YOU EXECUTE AND SUBMIT THIS DOCUMENT, YOU SHALL BE CONCLUSIVELY DEEMED TO HAVE BEEN PAID IN
    FULL THE AMOUNT STATED ABOVE, EVEN IF YOU HAVE NOT ACTUALLY RECEIVED THE PAYMENT, SIXTY (60) DAYS AFTER
    THE DATE STATED ABOVE UNLESS YOU FILE EITHER AN AFFIDAVIT OF NONPAYMENT OR A CLAIM OF LIEN BEFORE THE
    EXPIRATION OF THE SIXTY-DAY PERIOD. THE FAILURE TO INCLUDE THIS NOTICE LANGUAGE ON THE FACE OF THE FORM
    SHALL RENDER THE FORM UNENFORCEABLE AND INVALID AS A WAIVER AND RELEASE UNDER SECTION 85-7-419,
    MISSISSIPPI CODE OF 1972.
</div>

<p class="waiver-foot">
    Statutory form per Miss. Code Ann. &sect; 85-7-433(1); a waiver executed in exchange for or to induce any
    payment other than final payment must substantially follow this form (&sect; 85-7-419(2)). The waiver is
    binding subject only to payment in full, and the amount is conclusively deemed paid sixty (60) days after
    the date of execution unless an Affidavit of Nonpayment (&sect; 85-7-433(3)) is filed before the period
    expires (&sect; 85-7-419(5)(b)). Form LW-MS-CP v{{ $waiver['form']['template_version'] }}.
</p>
