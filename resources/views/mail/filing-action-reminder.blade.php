<x-mail::message>
Hi {{ $userName }},

{{ $body }}

@if($projectName)
**Project:** {{ $projectName }}
@endif

If you have any questions or need help, just reply to this email.

Thanks,<br>
The eRegister Team
</x-mail::message>
