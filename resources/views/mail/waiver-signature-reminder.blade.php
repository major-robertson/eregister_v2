<x-mail::message>
# Still waiting on your signature

Hi {{ $signerName }},

Just a reminder, {{ $requesterName }} sent you a **{{ $formTitle }}** for
**{{ $projectName }}** {{ $daysWaiting }} {{ $daysWaiting === 1 ? 'day' : 'days' }} ago,
and it's still waiting for your signature.

<x-mail::button :url="$ctaUrl">
Review &amp; Sign
</x-mail::button>

Signing takes about a minute, no account or password needed.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
