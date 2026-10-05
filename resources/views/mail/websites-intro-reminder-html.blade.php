{{-- The same note as mail.websites-intro-reminder (the plain-text part), as
     plain HTML so the unsubscribe link can be a word. --}}
<!DOCTYPE html>
<html lang="en">
<body>
<p>Hi {{ $firstName }},</p>

<p>Quick reminder, I've got your spot with Happy Websites held until {{ $deadline }}. Free site, free first month, only pay if you want to keep it.</p>

<p>Reply "yes" if you want it. If not, no worries.</p>

<p>Major</p>

@include('mail.partials.broadcast-footer')
</body>
</html>
