<x-mail::message>
Hi {{ $userName }},

@if ($step === 1)
@if ($isUnsigned)
Your {{ $kindLabel ? $kindLabel.' waiver' : 'waiver' }}{{ $projectName ? " for **{$projectName}**" : '' }} is saved and ready. You can download the PDF any time, free.

@if ($isCollect)
Instead of emailing a PDF back and forth, send it to {{ $counterparty ?: 'the signer' }} for e-signature. They sign from their phone with no account, we remind them until it's done, and the signed copy lands in your project.
@else
If you'd rather not print, sign and scan it, sign it right on the site. It takes about a minute, {{ $emailsCounterparty ? 'we email the signed copy to '.$counterparty.' for you, and it' : 'and the signed copy' }} stays stored with the project.
@endif

<x-mail::button :url="$waiverUrl">
{{ $isCollect ? 'Send it for signature' : 'Sign & send my waiver' }}
</x-mail::button>

Signing on the site is Lien Waiver Pro, {{ $proMonthly }} a month per person, cancel anytime. If the PDF is all you need, you're set.
@else
Your waiver{{ $projectName ? " for **{$projectName}**" : '' }} is on file with the project, so it's there whenever someone asks for it.

Next time you can skip the printer: sign on the site in about a minute and we email the signed copy for you. That's Lien Waiver Pro, {{ $proMonthly }} a month per person, cancel anytime.

<x-mail::button :url="$waiverUrl">
View my waiver
</x-mail::button>
@endif
@elseif ($step === 2)
A fair question before you e-sign a lien waiver: does it hold up?

Yes. Under the federal ESIGN Act and the Uniform Electronic Transactions Act, a signature can't be denied legal effect just because it's electronic, and {{ $stateName === 'lien' ? 'your state' : $stateName }} is one of the states where we offer e-signature for waivers. (In the few states that require a notary or a witness, we don't offer it at all.)

Every waiver signed on eRegister also gets a certificate of completion: who signed, their verified email, the time and IP address of each step, and a SHA-256 fingerprint of the exact document. If anyone questions the waiver later, that page answers it.

<x-mail::button :url="$isUnsigned ? $waiverUrl : $newWaiverUrl">
{{ $isUnsigned ? ($isCollect ? 'Send my waiver for signature' : 'Sign & send my waiver') : 'Create my next waiver' }}
</x-mail::button>

E-signature is part of Lien Waiver Pro, {{ $proMonthly }} a month per person, cancel anytime.
@elseif ($step === 3)
A lien waiver goes with a payment that arrives. If one{{ $projectName ? " on **{$projectName}**" : '' }} ever doesn't, your leverage is your lien rights, and lien rights run on deadlines.

@if ($deadlines !== [])
Based on the dates on your project, here is what's coming:

@foreach ($deadlines as $deadline)
- **{{ $deadline['name'] }}:** due {{ $deadline['due'] }}{{ $deadline['left'] ? ' ('.$deadline['left'].')' : '' }}
@endforeach

We prepare and send these for a flat fee per filing, and tracking the deadlines on your jobs is free.

<x-mail::button :url="$projectUrl">
See my deadlines
</x-mail::button>
@else
Which notice and lien deadlines apply{{ $stateName === 'lien' ? '' : ' in '.$stateName }} depends on your dates on the job. Add your first and last day to your project and we'll calculate your deadlines and show them on the project, free.

When you need one sent, we prepare preliminary notices, notices of intent and mechanics liens for a flat fee per filing.

<x-mail::button :url="$projectUrl">
Add my job dates
</x-mail::button>
@endif

Deadlines are calculated from the dates you enter, so double-check them against your own records.
@else
Pay apps tend to come around monthly. When the next one is due{{ $projectName ? " on **{$projectName}**" : '' }}, the waiver takes about two minutes: your project and contacts are already saved.

<x-mail::button :url="$newWaiverUrl">
Create my next waiver
</x-mail::button>

And if printing, signing and scanning is getting old, Lien Waiver Pro lets you sign on the site and send in one step. {{ $proMonthly }} a month per person, cancel anytime.
@endif

Questions? Just reply to this email.

Thanks,<br>
Major<br>
eRegister

@include('mail.partials.marketing-footer')
</x-mail::message>
