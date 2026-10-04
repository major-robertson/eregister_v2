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

    // Where the "Make my free mockup" request goes.
    'lead_email' => env('HAPPY_WEBSITES_LEAD_EMAIL', 'hello@happywebsites.com'),

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

];
