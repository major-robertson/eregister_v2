<x-mail::message>
# {{ $requesterName }} requests your signature

Hi {{ $signerName }},

{{ $requesterName }} has sent you a **{{ $formTitle }}** to review and sign for
**{{ $projectName }}**@if ($amount) covering a payment of **{{ $amount }}**@endif.

<x-mail::button :url="$ctaUrl">
Review &amp; Sign
</x-mail::button>

@if ($isGuest)
No account or password is needed: we'll verify your email with a one-time code, you review the
waiver, and you sign right in your browser. Both parties receive the signed copy.
@else
Log in with your existing account to review and sign. Both parties receive the signed copy.
@endif

If you weren't expecting this request, you can ignore this email or reply to let us know.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
