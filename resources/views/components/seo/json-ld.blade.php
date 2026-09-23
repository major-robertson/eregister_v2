@props(['data'])
{{-- One JSON-LD block. Slashes stay unescaped so URLs read naturally; "<" is
     escaped so a string value can never close the script tag early. --}}
<script type="application/ld+json">{!! json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
