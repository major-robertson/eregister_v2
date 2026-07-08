<x-mail::message>
# Verify your email to continue

@if ($signerName)
Hi {{ $signerName }},
@endif

Use this code to verify your email address and review **{{ $documentTitle }}** waiting for your signature:

<x-mail::panel>
<div style="text-align: center; font-size: 28px; font-weight: bold; letter-spacing: 6px;">{{ $code }}</div>
</x-mail::panel>

This code expires in {{ $ttlMinutes }} minutes. If you weren't expecting this email, you can safely ignore it.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
