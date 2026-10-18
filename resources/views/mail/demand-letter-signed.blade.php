<x-mail::message>
# Demand letter signed

{{ $businessName }} signed {{ $letterCount === 1 ? 'their demand letter' : "all {$letterCount} demand letters" }}. The filing is ready for review.

**Business:** {{ $businessName }}

**Signed by:** {{ $signerName }}

@if($projectName)
**Project:** {{ $projectName }}
@endif

@if($jobsiteAddress)
**Jobsite:** {{ $jobsiteAddress }}
@endif

**Letters:** {{ $letterCount }}

**Signed at:** {{ $signedAt }}

@if($filingPublicId)
**Filing:** {{ $filingPublicId }}
@endif

@if($adminUrl)
<x-mail::button :url="$adminUrl">
Open filing
</x-mail::button>
@endif

Thanks,<br>
eRegister
</x-mail::message>
