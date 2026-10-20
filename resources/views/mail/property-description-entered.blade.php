<x-mail::message>
# Customer entered the property

{{ $customerName }} entered the legal description or parcel number on their {{ $documentName }}. The filing is now in {{ $statusLabel }}.

**Business:** {{ $businessName }}

@if($projectName)
**Project:** {{ $projectName }}
@endif

**Customer:** {{ $customerEmail ? "{$customerName} ({$customerEmail})" : $customerName }}

**Filing:** {{ $filingPublicId }}

@if($legalDescription)
**Legal description:**

<x-mail::quote :text="$legalDescription" />
@endif

@if($apn)
**Parcel number:**

<x-mail::quote :text="$apn" />
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
