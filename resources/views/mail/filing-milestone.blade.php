<x-mail::message>
Hi {{ $userName }},

@if($status === 'submitted_for_recording')
{{ $recordingSentence }}

@if($turnaround)
Recording typically takes {{ $turnaround }}.
@endif

We'll email you the recorded copy.
@elseif($status === 'recorded')
{{ $countyName ?: 'The county' }} recorded your {{ $documentName }}.

@if($recordingReference)
**Recording reference:** {{ $recordingReference }}
@endif

@if($recordedCopy)
We attached the recorded copy to this email.
@elseif($hasRecordedCopy)
The recorded copy is too large to email. You can download it from your filing page.
@else
We'll send the recorded copy as soon as the county returns it.
@endif
@elseif($status === 'mailed')
We mailed your {{ $documentName }}.

@if($recipients !== [])
**We mailed it to:**

@foreach($recipients as $recipient)
- {{ $recipient['name'] }}{{ $recipient['address'] ? ', '.$recipient['address'] : '' }}{{ $recipient['delivery'] ? ' (by '.$recipient['delivery'].')' : '' }}@if($recipient['tracking']) · Tracking: [{{ $recipient['tracking'] }}]({{ $recipient['tracking_url'] }})@endif

@endforeach
@endif

@if($trackingNumber)
**Certified mail tracking:** [{{ $trackingNumber }}]({{ $trackingUrl }})
@endif

@if($proofOfService)
We attached the proof of service to this email.
@elseif($hasProofOfService)
The proof of service is too large to email. You can download it from your filing page.
@endif

Certified mail proves we mailed it. It does not prove the other side read it.
@else
Your {{ $documentName }} is complete.

@if($recordedCopy && $proofOfService)
We attached the recorded copy and the proof of service to this email.
@elseif($recordedCopy)
We attached the recorded copy to this email.
@elseif($proofOfService)
We attached the proof of service to this email.
@endif

@if(($hasRecordedCopy && ! $recordedCopy) || ($hasProofOfService && ! $proofOfService))
Some files are too large to email. You can download them from your filing page.
@endif

Your filing page shows the timeline and the documents.
@endif

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
