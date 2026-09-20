<x-mail::message>
Hi {{ $user->first_name }},

My name is Major. Welcome to eRegister! You came in for a lien waiver, so here is your link back to it whenever you're ready. It takes about two minutes, and creating and downloading the PDF is free.

<x-mail::button :url="$resumeUrl">
Create my waiver
</x-mail::button>

If you have any questions or get stuck anywhere, just reply to this email. I'm happy to help.

Thanks,<br>
Major<br>
eRegister
</x-mail::message>
