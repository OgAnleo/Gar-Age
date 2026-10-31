<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public Demo
    |--------------------------------------------------------------------------
    |
    | On the public demo the demo account's credentials are published, so it
    | cannot change its own credentials or delete its business, and it can be
    | rebuilt every night (POST /cron/demo). Off everywhere else.
    |
    */

    'enabled' => (bool) env('DEMO_MODE', false),

    'email' => 'demo@gar-age.test',

    'password' => 'password',

];
