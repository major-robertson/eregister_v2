{{-- Footer for marketing email: the opt-out link and, once MAIL_POSTAL_ADDRESS
     is set, the sender's postal address (both required on commercial email). --}}
<div style="margin-top: 32px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af;">
<a href="{{ $preferencesUrl }}" style="color: #9ca3af;">Manage email preferences or unsubscribe</a>
@if (filled(config('mail.postal_address')))
<br>{{ config('app.name') }}, {{ config('mail.postal_address') }}
@endif
</div>
