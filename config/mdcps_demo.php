<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MDCPS Demo Sandbox Credentials
    |--------------------------------------------------------------------------
    |
    | Hard-coded credentials for the isolated, front-end-only Miami-Dade
    | school website demo CMS. There is no database or real auth system;
    | these gate the /mdcps-demo/admin area via a simple session flag so the
    | sandbox can be shared with reviewers.
    |
    */

    'username' => env('MDCPS_DEMO_USER', 'clerk'),

    'password' => env('MDCPS_DEMO_PASSWORD', 'everglades2026'),
];
