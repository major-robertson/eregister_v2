<x-mail::message>
Hi {{ $userName }},

@if ($step === 1)
You started a {{ $stateName ? $stateName.' ' : '' }}lien waiver but didn't finish it.

It takes about 2 minutes. Enter the job address, who the waiver is for, and the amount. Then download your PDF.

It's free.
@else
Do you still need a {{ $stateName ? $stateName.' ' : '' }}lien waiver?

If so, here is your link. It takes about 2 minutes and it's free. No credit card.

If you already took care of it, you can ignore this email.
@endif

<x-mail::button :url="$resumeUrl">
Finish my waiver
</x-mail::button>

Stuck on something? Reply to this email and I'll help.

Thanks,<br>
Major<br>
eRegister

@include('mail.partials.marketing-footer')
</x-mail::message>
