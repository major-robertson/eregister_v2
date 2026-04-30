<x-mail::message>
# {{ $stateName }} Attorney Referral Request

A user has requested to be connected with a {{ $stateName }} attorney for mechanics lien filing.

**Name:** {{ $firstName }} {{ $lastName }}

**Business:** {{ $businessName }}

**Email:** {{ $userEmail }}

@if($phone)
**Phone:** {{ $phone }}
@endif

**State:** {{ $stateName }} ({{ $stateCode }})

Thanks,<br>
eRegister
</x-mail::message>
