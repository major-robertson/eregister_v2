<x-mail::message>
Hi {{ $userName }},

{{ $body }}

@if($projectName)
**Project:** {{ $projectName }}
@endif

<x-mail::button :url="$ctaUrl">
{{ $ctaLabel }}
</x-mail::button>

If you have any questions or need help, just reply to this email.

Thanks,<br>
The eRegister Team
</x-mail::message>
