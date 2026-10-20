<x-mail::message>
Hi {{ $userName }},

We have your document. We'll check it and keep going. If the scan is hard to read we'll ask for a clearer one.

**File:** {{ $fileName }}

**Filing:** {{ $documentName }}

@if($projectName)
**Project:** {{ $projectName }}
@endif

<x-mail::button :url="$filingUrl">
View your filing
</x-mail::button>

If you have a question, just reply to this email.

Thanks,<br>
The eRegister Team
</x-mail::message>
