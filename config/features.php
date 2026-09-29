<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public complaints (ثبت و پیگیری شکایت)
    |--------------------------------------------------------------------------
    |
    | When false, every public complaint entry point is hidden (menus, guild
    | pages, contact page, home fallbacks) and the /complaints/* routes show a
    | "temporarily disabled" page. The admin panel keeps full access to
    | complaints that were already registered.
    |
    | Re-enable by setting COMPLAINTS_ENABLED=true in .env and then running
    | `php artisan optimize:clear`.
    |
    */
    'complaints_enabled' => (bool) env('COMPLAINTS_ENABLED', false),
];
