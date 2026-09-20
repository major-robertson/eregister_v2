<x-mail::message>
Hi {{ $userName }},

@if ($step === 1)
@if ($isUnsigned)
Your waiver{{ $projectName ? " for **{$projectName}**" : '' }} is ready.

You can download the PDF anytime. That's free.

@if ($isCollect)
Want to skip the back-and-forth? Send it to {{ $counterparty ?: 'the signer' }} to sign online. They can sign from their phone. No account needed. We remind them until it's signed.
@else
Want to skip the printer? You can sign it online in about a minute. {{ $emailsCounterparty ? "We'll email the signed copy to {$counterparty} for you." : "We'll save the signed copy to your project." }}
@endif

<x-mail::button :url="$waiverUrl">
{{ $isCollect ? 'Send it for signature' : 'Sign my waiver online' }}
</x-mail::button>

{{ $isCollect ? 'Sending for signature' : 'Signing online' }} is part of Lien Waiver Pro. It's {{ $proMonthly }} a month per person. Cancel anytime.
@else
Your waiver{{ $projectName ? " for **{$projectName}**" : '' }} is saved to your project.

Next time, you can skip the printer. Sign online in about a minute and we'll email the signed copy for you.

<x-mail::button :url="$waiverUrl">
View my waiver
</x-mail::button>

Signing online is part of Lien Waiver Pro. It's {{ $proMonthly }} a month per person. Cancel anytime.
@endif
@elseif ($step === 2)
Is an e-signed lien waiver legal?

Yes. Federal law (the ESIGN Act) and state e-signature laws say an electronic signature is as valid as one in ink. We offer e-signing for waivers in {{ $stateName === 'lien' ? 'your state' : $stateName }}.

Every waiver signed on eRegister comes with a signing record. It shows who signed, their verified email, and the date and time. It also helps prove the document wasn't changed later.

<x-mail::button :url="$isUnsigned ? $waiverUrl : $newWaiverUrl">
{{ $isUnsigned ? ($isCollect ? 'Send my waiver for signature' : 'Sign my waiver online') : 'Create my next waiver' }}
</x-mail::button>

E-signing is part of Lien Waiver Pro. It's {{ $proMonthly }} a month per person. Cancel anytime.
@elseif ($step === 3)
What if you don't get paid{{ $projectName ? " on **{$projectName}**" : '' }}?

Your lien rights protect you. But they have deadlines. If you miss one, you can lose the right to file a lien.

@if ($deadlines !== [])
Here is what's coming up, based on the dates on your project:

@foreach ($deadlines as $deadline)
- **{{ $deadline['name'] }}:** due {{ $deadline['due'] }}{{ $deadline['left'] ? ' ('.$deadline['left'].')' : '' }}
@endforeach

We can prepare and send it for you for a flat fee. Tracking your deadlines is free.

<x-mail::button :url="$projectUrl">
See my deadlines
</x-mail::button>

We calculate these from the dates you entered, so please double-check them.
@else
Add your first and last day on the job to your project. We'll calculate your {{ $stateName === 'lien' ? '' : $stateName.' ' }}deadlines for free.

When you need a notice or a lien sent, we can do that for a flat fee.

<x-mail::button :url="$projectUrl">
Add my job dates
</x-mail::button>
@endif
@else
Is another payment coming up{{ $projectName ? " on **{$projectName}**" : '' }}?

Your next waiver takes about 2 minutes. Your project and contacts are already saved.

<x-mail::button :url="$newWaiverUrl">
Create my next waiver
</x-mail::button>

Tired of printing and scanning? With Lien Waiver Pro you can sign online and send it in one step. It's {{ $proMonthly }} a month per person. Cancel anytime.
@endif

Questions? Just reply to this email.

Thanks,<br>
Major<br>
eRegister

@include('mail.partials.marketing-footer')
</x-mail::message>
