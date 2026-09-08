<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Blade templates for this application are stored in resources/views.
    | Laravel's file view finder uses this list to resolve view names.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | Compiled Blade templates are written to Laravel's framework cache
    | directory unless a deployment provides an explicit path via the
    | VIEW_COMPILED_PATH environment variable.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

];
