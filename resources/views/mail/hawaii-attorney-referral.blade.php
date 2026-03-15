<x-mail::message>
# Hawaii Attorney Referral Request

A user has requested to be connected with a Hawaii attorney for mechanics lien filing.

**Name:** {{ $firstName }} {{ $lastName }}

**Business:** {{ $businessName }}

**Email:** {{ $userEmail }}

@if($phone)
**Phone:** {{ $phone }}
@endif

Thanks,<br>
eRegister
</x-mail::message>
