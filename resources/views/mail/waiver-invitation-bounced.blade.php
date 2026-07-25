<x-mail::message>
# Your signature request couldn't be delivered

The invitation for the **{{ $formTitle }}** on **{{ $projectName }}** bounced —
**{{ $signerEmail }}** isn't accepting email, so the signer never received it
and reminders have been paused.

The address may have a typo, or the mailbox may no longer exist. To get the
waiver signed:

1. Open the waiver and void the current signature request
2. Correct the counterparty's email address
3. Send it for signature again

<x-mail::button :url="$ctaUrl">
Open the waiver
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
