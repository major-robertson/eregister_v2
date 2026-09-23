<x-mail::message>
Hi {{ $userName }},

@if ($step === 1)
You signed up for {{ $certificate }} but haven't made one yet.

It takes a few minutes. You enter your business details once. We fill in the official form and add your signature. You get a PDF to send your vendor.

It costs {{ $generatorPrice }} a year. That covers as many certificates as you need, for every state you buy in.
@elseif ($step === 2)
You can send your vendor a resale certificate today.

We fill in the official {{ $stateName ? $stateName.' ' : '' }}form for you and add your signature. It takes a few minutes.

When the next vendor asks, your details are already saved.
@else
This is my last reminder about your resale certificate.

If you still need one, your account is ready. It costs {{ $generatorPrice }} a year for as many certificates as you need.

Don't have a sales tax permit yet? You need one before you can use a resale certificate. We can register you for {{ $permitPrice }} per state. [Register for a permit]({{ $registrationUrl }})

If you already took care of it, you can ignore this email.
@endif

<x-mail::button :url="$resumeUrl">
Make my certificate
</x-mail::button>

Questions? Reply to this email and I'll help.

Thanks,<br>
Major<br>
eRegister

@include('mail.partials.marketing-footer')
</x-mail::message>
