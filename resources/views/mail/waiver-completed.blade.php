<x-mail::message>
# Lien waiver signed ✓

**{{ $signerName }}** has signed the **{{ $formTitle }}** for
**{{ $projectName }}**@if ($amount) covering a payment of **{{ $amount }}**@endif.

Signed {{ $signedAt }}.

The signed waiver (including its Certificate of Completion with the full audit trail) is
attached to this email for your records.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
