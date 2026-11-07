<?php

return [
    /*
    |--------------------------------------------------------------------------
    | KasOrg demo account passwords
    |--------------------------------------------------------------------------
    |
    | These values intentionally have no fallback. Local and testing
    | environments must provide distinct values for the fixed demo accounts.
    | UserSeeder rejects production before reading these demo credentials.
    |
    */
    'passwords' => [
        'admin' => env('KASORG_DEMO_ADMIN_PASSWORD'),
        'anggota' => env('KASORG_DEMO_ANGGOTA_PASSWORD'),
    ],
];
