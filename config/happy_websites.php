<?php

/*
| Happy Websites is our sister company: done-for-you websites for small
| businesses. The portal's Websites page offers it to customers, and its
| button sends a one-click request for a free mockup to their inbox.
|
| The prices and promises on that page repeat happywebsites.com/pricing.
| Change them here when they change there.
*/

return [

    'url' => 'https://happywebsites.com',

    // Where customers' requests and replies go: the "Make my free mockup"
    // request from the portal, and replies to the intro emails below. A comma
    // separated list, so more than one inbox can get them.
    'lead_email' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('HAPPY_WEBSITES_LEAD_EMAIL', 'hello@happywebsites.com'))
    ))),

    // Monthly prices in dollars: a one-page site, and a 5 to 7 page site.
    'price_one_page' => 99,
    'price_multi_page' => 199,

    // Sites they built, shown on the page. The images are copies of their
    // portfolio shots (public/img/websites), and each links to its case study.
    'examples' => [
        ['title' => 'Painting company', 'image' => 'img/websites/painting-company.webp', 'path' => '/work/painting-company'],
        ['title' => 'Contracting firm', 'image' => 'img/websites/contracting-firm.webp', 'path' => '/work/contracting-firm'],
        ['title' => 'Gym', 'image' => 'img/websites/gym.webp', 'path' => '/work/gym-website'],
    ],

    /*
    | The intro emails (email:send-websites-intro). The owner invites a
    | customer to a free site with the first month free and holds a spot
    | through a date. One reminder goes out 3 days before that date. A person
    | is invited once, ever.
    |
    | This is a different, time-limited offer from the portal page's free
    | mockup. Both stay true as long as the page never promises the free site.
    */
    'intro' => [

        // Off until the owner turns it on. --dry-run and --test-to work either way.
        'enabled' => (bool) env('HAPPY_WEBSITES_INTRO_ENABLED', false),

        // New invitations per run. Every "yes" is a whole site built for free
        // ("for a few of our customers this month"), so keep this to what the
        // Happy Websites team can build for.
        'daily_cap' => (int) env('HAPPY_WEBSITES_INTRO_DAILY_CAP', 10),

        // Invite a customer this many days after they sign up...
        'delay_days' => 14,

        // ...and nobody who signed up longer ago than this.
        'max_age_months' => 18,

        // Leave alone anyone we emailed in the last few days. They are picked
        // up on a later run.
        'quiet_days' => 3,

        'from_name' => 'Major from eRegister',

        // Postmark's inbound address for this server (inbound needs their Pro
        // plan). When set, it joins Reply-To so the app hears about a reply
        // and the reminder skips that person. Replies still go straight to
        // the inboxes in lead_email.
        'inbound_address' => env('POSTMARK_INBOUND_ADDRESS'),

    ],

];
