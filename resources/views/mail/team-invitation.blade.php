<x-mail::message>
Hi there,

{{ $inviterName }} invited you to join **{{ $businessName }}** on eRegister as {{ $roleLabel }}.

<x-mail::button :url="$ctaUrl">
Accept Invitation
</x-mail::button>

If you don't have an eRegister account yet, you'll be asked to create one first — the business is already set up, so you'll be working alongside your team right away.

If the button above doesn't work, copy and paste this link into your browser:

{{ $ctaUrl }}

This invitation expires in 7 days. If you weren't expecting it, you can safely ignore this email.

Thanks,<br>
Major<br>
eRegister
</x-mail::message>
