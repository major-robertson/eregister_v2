<x-mail::message>
Hi {{ $userName }},

@if($step === 1)
Thank you for your order. We are ready to prepare your {{ $stateNames }} sales tax registration, but we still need your answers.

The questions take about 10 minutes. You can save and come back any time.
@elseif($step === 2)
Your {{ $stateNames }} sales tax registration is still waiting for your answers.

It takes about 10 minutes. If a question stops you, reply to this email and we will help.
@else
We cannot file your {{ $stateNames }} sales tax registration until the questions are done.

If you would rather answer them over the phone, reply to this email and we will call you.
@endif

@if($rush)
You chose rush processing. The 2 business days start once we have your answers.
@endif

<x-mail::button :url="$resumeUrl">
Finish the questions
</x-mail::button>

Our fee is refunded in full until we file with the state.

Thanks,<br>
Major<br>
eRegister
</x-mail::message>
