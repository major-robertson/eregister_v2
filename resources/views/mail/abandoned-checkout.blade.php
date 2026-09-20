<x-mail::message>
Hi {{ $userName }},

@if($step === 1)
I noticed you started an order{{ $projectName ? " for **{$projectName}**" : '' }} but didn't finish. Want me to help you complete it?

If you ran into any issues or have questions, just reply to this email — I'm happy to help.
@elseif($step === 2)
Just a quick follow-up — your order{{ $projectName ? " for **{$projectName}**" : '' }} is still waiting for you.

If something came up or you need any help, feel free to reach out. I'm here to make this as easy as possible.
@else
This is my last reminder — your order{{ $projectName ? " for **{$projectName}**" : '' }} is still incomplete.

If you'd like to finish it, just click the button below. If you have any questions at all, reply to this email and I'll get back to you right away.
@endif

@if($resumeUrl)
<x-mail::button :url="$resumeUrl">
Continue Your Order
</x-mail::button>
@endif

Thanks,<br>
Major<br>
eRegister

@include('mail.partials.marketing-footer')
</x-mail::message>
