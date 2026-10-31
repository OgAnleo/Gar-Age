<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public Demo
    |--------------------------------------------------------------------------
    |
    | On the public demo the credentials of the demo owner and employee are
    | published, so they cannot change them, delete the business or its team,
    | and the demo is rebuilt every night (POST /cron/demo). Off elsewhere.
    |
    */

    'enabled' => (bool) env('DEMO_MODE', false),

    'email' => 'demo@gar-age.test',

    'staff_email' => 'empleado@gar-age.test',

    'password' => 'password',

];
