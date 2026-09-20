<x-mail::message>
Hi {{ $user->first_name }},

I'm Major. Welcome to eRegister!

Here is the link to make your lien waiver. It takes about 2 minutes. It's free to create and download.

<x-mail::button :url="$resumeUrl">
Create my waiver
</x-mail::button>

Need help? Just reply to this email.

Thanks,<br>
Major<br>
eRegister
</x-mail::message>
