<x-mail::message>
Hi {{ $userName }},

@if ($step === 1)
You signed up to register for sales tax{{ $stateName ? ' in '.$stateName : '' }}, but you haven't ordered yet.

Here's how it works. You pick your state and pay {{ $price }} per state. Then you answer our questions. They take about 10 minutes. We prepare your registration and file it with the state.
@elseif ($step === 2)
You don't have to use the state website to get your {{ $permit }}.

You answer our questions. We fill in the registration and file it with {{ $agency ? 'the '.$agency : 'the state' }} for you.

It costs {{ $price }} per state. Our fee is refunded in full until we file with the state.
@else
This is my last reminder about your {{ $permit }}.

If you still need it, your account is ready. Pick your state, and we'll take it from there.

If you already took care of it, you can ignore this email.
@endif

<x-mail::button :url="$resumeUrl">
Start my registration
</x-mail::button>

Questions? Reply to this email and I'll help.

Thanks,<br>
Major<br>
eRegister

@include('mail.partials.marketing-footer')
</x-mail::message>
