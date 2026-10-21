<x-mail::message>
@if($uploadedDocument)
# Customer uploaded a document

{{ $customerName }} uploaded their signed document for their {{ $documentName }}. The filing is now in Needs Review.
@else
# Customer answered a request

{{ $customerName }} answered our request on their {{ $documentName }}. The filing is now in Needs Review.
@endif

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

@unless($uploadedDocument)
**They answered:**

@if($answerText)
<x-mail::quote :text="$answerText" />
@else
No text, just a file.
@endif
@endunless

@if($fileName)
**File:** {{ $fileName }}{{ $fileSize ? " ({$fileSize})" : '' }} (download it from the filing page)
@endif

<x-mail::button :url="$adminUrl">
Open filing
</x-mail::button>

Thanks,<br>
eRegister
</x-mail::message>
