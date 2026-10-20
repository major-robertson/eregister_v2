<x-mail::message>
@if($mode === 'uploaded_document')
# Customer uploaded a document

{{ $customerName }} uploaded their signed document for their {{ $documentName }}. The filing is now in Needs Review.
@elseif($mode === 'draft_approved')
# Draft approved

{{ $customerName }} approved {{ $draftVersion ? "version {$draftVersion} of " : '' }}the draft of their {{ $documentName }}. The filing is now in {{ $statusLabel }}.
@if($needsSignature)

It needs their signature next. Send the signature request from the filing page.
@endif
@elseif($mode === 'draft_changes_requested')
# Changes requested

{{ $customerName }} asked for changes to {{ $draftVersion ? "version {$draftVersion} of " : '' }}the draft of their {{ $documentName }}. The filing is now in {{ $statusLabel }}.
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

@if($draftFileName)
**Draft:** {{ $draftVersion ? "v{$draftVersion}, " : '' }}{{ $draftFileName }}
@endif

@if($requestText && in_array($mode, ['answered', 'uploaded_document'], true))
**We asked:**

<x-mail::quote :text="$requestText" />
@endif

@if($mode === 'answered')
**They answered:**

@if($answerText)
<x-mail::quote :text="$answerText" />
@else
No text, just a file.
@endif
@elseif($mode === 'draft_changes_requested' && $answerText)
**They asked for:**

<x-mail::quote :text="$answerText" />
@endif

@if($fileName)
**File:** {{ $fileName }}{{ $fileSize ? " ({$fileSize})" : '' }} (download it from the filing page)
@endif

<x-mail::button :url="$adminUrl">
Open filing
</x-mail::button>

Thanks,<br>
eRegister
</x-mail::message>
