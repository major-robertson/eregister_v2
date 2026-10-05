{{-- The same note as mail.websites-intro (the plain-text part), word for
     word. It is HTML for one reason: links can be words instead of long
     URLs. No layout, logo or button, so it still reads like a note the owner
     typed. --}}
<!DOCTYPE html>
<html lang="en">
<body>
<p>Hi {{ $firstName }},</p>

<p>Major here from eRegister.</p>

<p>Our sister company, Happy Websites, is building free sites for a few of our customers this month. I'd like {{ $businessName }} to be one of them.</p>

<p>They'll build the whole thing and run it for a month, free. If you like it after that, it's ${{ $price }} a month to keep it. No setup fee, no contract. If you don't, you walk away and owe nothing.</p>

<p>Some of their work: <a href="{{ $workUrl }}">{{ $workLabel }}</a></p>

<p>I can hold a spot for you through {{ $deadline }}. If you want it, just reply "yes" and I'll make the intro.</p>

<p>Major<br>
eRegister</p>

@include('mail.partials.broadcast-footer')
</body>
</html>
