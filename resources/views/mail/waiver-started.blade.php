<x-mail::message>
Hi {{ $userName }},

@if ($step === 1)
You started a {{ $stateName ? $stateName.' ' : '' }}lien waiver on eRegister and didn't get to the finish. It takes about two minutes from here: the jobsite, who it's going to, the amount, and you have a PDF with the correct {{ $stateName ? $stateName.' ' : '' }}form.

Creating and downloading it is free.
@else
One last note about that {{ $stateName ? $stateName.' ' : '' }}lien waiver. If you got it handled another way, ignore me.

If not, your link is below. Pick the project, fill in the amount, download the PDF. Free, no card.
@endif

<x-mail::button :url="$resumeUrl">
Finish my waiver
</x-mail::button>

If something got in the way, just reply to this email and tell me where you got stuck. I'm happy to help.

Thanks,<br>
Major<br>
eRegister

@include('mail.partials.marketing-footer')
</x-mail::message>
