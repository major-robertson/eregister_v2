{{--
    The first line of both Nevada recordable forms: "Assessor's Parcel
    Numbers" heads the notice of lien form in NRS 108.226(5) and the
    discharge form in NRS 108.2437(1), and NRS 111.312(1)(b) bars the
    recorder from recording a notice of lien without the assessor's parcel
    number "at the top left corner of the first page of the document".
    Printed through clauses.notice_box, the only slot above the title, so it
    is the first line of text at the left, directly under the recorder's
    space. Verified against leg.state.nv.us on 2026-09-30. Never edit the
    wording without re-checking the statute.
--}}
@php
    $apn = $doc['project']['apn'];
@endphp
<p style="text-align: left; margin: 0 0 8pt 0;"><strong>Assessor's Parcel Numbers:</strong> @if ($apn){{ $apn }}@else<span class="fill fill-wide">&nbsp;</span>@endif</p>
