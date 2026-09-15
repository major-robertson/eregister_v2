<x-mail::message>
Hi {{ $signerName }},

Just a reminder that your electronic signature is still needed on your {{ $title }}.

<x-mail::button :url="$ctaUrl">
Review & Sign
</x-mail::button>

It only takes a few minutes: sign in to your eRegister account, review
{{ $documentCount > 1 ? "all {$documentCount} letters" : 'the letter' }}, and adopt your signature.
@if ($expiresOn)
This signing link is valid through {{ $expiresOn }}.
@endif

If the button above doesn't work, copy and paste this link into your browser:

{{ $ctaUrl }}

Thanks,<br>
Major<br>
eRegister
</x-mail::message>
