<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Optional product features
    |--------------------------------------------------------------------------
    |
    | Every feature below can be switched off without touching code. Disabled
    | features drop their routes (404), hide their UI and stop their emails.
    |
    */

    'email_verification' => (bool) env('FEATURE_EMAIL_VERIFICATION', true),

    'two_factor' => (bool) env('FEATURE_TWO_FACTOR', true),

    'teams' => (bool) env('FEATURE_TEAMS', true),

];
