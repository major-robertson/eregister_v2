<?php

/*
| Facts about the company that marketing pages may state. Keep them true:
| update the review numbers when the Google listing changes, and quote
| reviews word for word (shortening is fine, rewording is not).
*/

return [

    // The business was formed on 2017-10-19. Lien services started much
    // later, so say "in business since 2017", never "liens since 2017".
    'in_business_since' => 2017,

    'google_reviews' => [
        'url' => 'https://maps.app.goo.gl/knY7FTPFt6txVE6PA',
        'rating' => 5.0,
        // Not shown on the site while it is this small (the owner's call, 2026-09-21).
        'count' => 7,
        'checked_on' => '2026-09-21',

        // Excerpts from the public Google reviews, which are about the lien
        // filing service. The owner's first name is left out at his request,
        // by starting the excerpt after it rather than by editing the text.
        'featured' => [
            [
                'name' => 'Floors Kitchen & Bath Direct',
                'text' => "Excellent service! eRegister made filing my mechanic's lien fast, simple, and stress-free. The process was easy to follow, and everything was handled professionally and efficiently. I highly recommend them to any contractor or business that needs reliable lien filing services.",
            ],
            [
                'name' => 'Paulo R.',
                'text' => 'Everything was straightforward, communication was excellent, and the process was completed quickly and professionally. I would definitely recommend eRegister to anyone needing lien services.',
            ],
            [
                'name' => 'Dana V.',
                'text' => 'They made things very easy for me, asked for certain information and took it off of my hands for the most part. I recommend hiring their services.',
            ],
            [
                'name' => 'Mari B.',
                'text' => 'Best services ever! They communicate and get the job done. The only service I will use.',
            ],
        ],
    ],

];
