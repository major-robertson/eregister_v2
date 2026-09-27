<?php

/*
| The review request email (email:send-review-requests): a short plain-text
| note from the owner asking for a Google review. It goes out 2 days after a
| customer's lien is recorded or a state approves their sales tax
| registration, and only once per person, ever.
*/

return [

    // Opens the "write a review" box on the Google listing.
    'url' => 'https://g.page/r/CTSM6sX9m7MNEAI/review',

    // The day this started (Eastern). Anyone who paid for anything before it
    // is a past client and is never asked: the owner reaches them himself.
    // Only liens recorded and states approved from this day on count.
    'starts_at' => '2026-09-27',

    // How long after the lien is recorded or the state approves.
    'delay_days' => 2,

];
