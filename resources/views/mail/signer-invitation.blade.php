<x-mail::message>
Hi {{ $signerName }},

Your {{ $title }} {{ $documentCount > 1 ? 'are' : 'is' }} ready for your electronic signature.

<x-mail::button :url="$ctaUrl">
Review & Sign
</x-mail::button>

You'll be asked to confirm your consent to sign electronically (the first time only), review
{{ $documentCount > 1 ? "all {$documentCount} letters" : 'the letter' }}, and adopt your signature.

If the button above doesn't work, copy and paste this link into your browser:

{{ $ctaUrl }}

Thanks,<br>
Major<br>
eRegister
</x-mail::message>
