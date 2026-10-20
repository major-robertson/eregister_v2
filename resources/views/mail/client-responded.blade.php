<x-mail::message>
# Customer answered a request

{{ $customerName }} answered our request on their {{ $documentName }}. The filing is now in Needs Review.

**Business:** {{ $businessName }}

@if($projectName)
**Project:** {{ $projectName }}
@endif

**Customer:** {{ $customerEmail ? "{$customerName} ({$customerEmail})" : $customerName }}

**Filing:** {{ $filingPublicId }}

@if($requestText)
**We asked:**

<x-mail::quote :text="$requestText" />
@endif

**They answered:**

@if($answerText)
<x-mail::quote :text="$answerText" />
@else
No text, just a file.
@endif

@if($fileName)
**File:** {{ $fileName }} (download it from the filing page)
@endif

<x-mail::button :url="$adminUrl">
Open filing
</x-mail::button>

Thanks,<br>
eRegister
</x-mail::message>
