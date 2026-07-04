{{--
    Electronic signature block stamped onto a signed demand letter.
    DOMPDF: plain HTML + inline CSS. When the signer adopted a visual
    signature (drawn or typed-in-font) its PNG is embedded as a data URI;
    otherwise the adopted name renders in an italic serif clearly labeled as
    an electronic signature — ESIGN does not mandate any particular font,
    and DOMPDF won't fetch remote script fonts.

    Vars: $signature[name|image|signed_at_eastern|signed_at_utc|signature_id]
--}}
<div class="esign-signature">
    <div class="esign-signature-label">Electronically signed by:</div>
    @if (! empty($signature['image']))
        <img src="{{ $signature['image'] }}" alt="Signature of {{ $signature['name'] }}" style="height: 40px;" />
    @else
        <div class="esign-signature-name">{{ $signature['name'] }}</div>
    @endif
    <div class="esign-signature-meta">
        {{ $signature['name'] }} — electronically signed on {{ $signature['signed_at_eastern'] }}
        ({{ $signature['signed_at_utc'] }})<br>
        Signature ID: {{ $signature['signature_id'] }}
    </div>
</div>
