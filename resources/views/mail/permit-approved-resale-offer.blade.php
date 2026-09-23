<x-mail::message>
Hi {{ $userName }},

Good news: {{ $stateName }} has approved your sales tax registration.

Here is what usually comes next. When you buy inventory to resell, your vendors will ask you for a resale certificate. It is the form that lets you buy without paying sales tax, and it carries the permit number you just received.

We can generate those certificates for you. We already have your business details from the registration, so it takes a few minutes to set up:

- The official form for every state you buy in
- Your signature applied, ready to email or print
- Vendors saved, expiration dates tracked
- Unlimited certificates for {{ $price }} a year

<x-mail::button :url="$startUrl">
Create my first certificate
</x-mail::button>

If you only need one or two, the blank state forms are free on your dashboard.

Thanks,<br>
Major<br>
eRegister

@include('mail.partials.marketing-footer')
</x-mail::message>
