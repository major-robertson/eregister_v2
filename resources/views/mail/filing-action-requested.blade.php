<x-mail::message>
Hi {{ $userName }},

{{ $senderLine }}

<x-mail::quote :text="$requestText" />

@if($attachmentName)
We attached {{ $attachmentName }} to this email.
@endif

@if($projectName)
**Project:** {{ $projectName }}
@endif

<x-mail::button :url="$ctaUrl">
{{ $ctaLabel }}
</x-mail::button>

If you have a question, just reply to this email.

Thanks,<br>
The eRegister Team
</x-mail::message>
