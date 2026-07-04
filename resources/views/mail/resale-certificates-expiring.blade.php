<x-mail::message>
Hi there,

Some resale certificates for **{{ $businessName }}** are approaching their expiration date.
Expired certificates can leave purchases exposed to sales tax — renewing takes about a minute.

@if ($urgent->isNotEmpty())
## Expiring within 30 days

<x-mail::table>
| Certificate | Vendor | Expires |
|:------------|:-------|:--------|
@foreach ($urgent as $certificate)
| {{ $certificate->displayName() }} | {{ $certificate->vendor_snapshot['legal_name'] ?? '—' }} | {{ $certificate->expiration_date->format('M j, Y') }} |
@endforeach
</x-mail::table>
@endif

@if ($warning->isNotEmpty())
## Expiring within 60 days

<x-mail::table>
| Certificate | Vendor | Expires |
|:------------|:-------|:--------|
@foreach ($warning as $certificate)
| {{ $certificate->displayName() }} | {{ $certificate->vendor_snapshot['legal_name'] ?? '—' }} | {{ $certificate->expiration_date->format('M j, Y') }} |
@endforeach
</x-mail::table>
@endif

@if ($notice->isNotEmpty())
## Expiring within 90 days

<x-mail::table>
| Certificate | Vendor | Expires |
|:------------|:-------|:--------|
@foreach ($notice as $certificate)
| {{ $certificate->displayName() }} | {{ $certificate->vendor_snapshot['legal_name'] ?? '—' }} | {{ $certificate->expiration_date->format('M j, Y') }} |
@endforeach
</x-mail::table>
@endif

<x-mail::button :url="$dashboardUrl">
Review &amp; Renew Certificates
</x-mail::button>

Thanks,<br>
Major<br>
eRegister
</x-mail::message>
