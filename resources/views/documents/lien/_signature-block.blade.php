{{--
    Electronic signature block stamped onto a signed demand letter.
    DOMPDF: plain HTML + inline CSS. The adopted (typed) name is rendered in an
    italic serif clearly labeled as an electronic signature — ESIGN does not
    mandate any particular font, and DOMPDF won't fetch remote script fonts.

    Vars: $signature[name|signed_at_eastern|signed_at_utc|signature_id]
--}}
<div class="esign-signature">
    <div class="esign-signature-label">Electronically signed by:</div>
    <div class="esign-signature-name">{{ $signature['name'] }}</div>
    <div class="esign-signature-meta">
        {{ $signature['name'] }} — electronically signed on {{ $signature['signed_at_eastern'] }}
        ({{ $signature['signed_at_utc'] }})<br>
        Signature ID: {{ $signature['signature_id'] }}
    </div>
</div>
