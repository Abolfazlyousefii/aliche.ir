<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\ConvertJalaliDates;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        /*
        |--------------------------------------------------------------------------
        | Web Middleware
        |--------------------------------------------------------------------------
        */

        $middleware->web(append: [
            ConvertJalaliDates::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Middleware Aliases
        |--------------------------------------------------------------------------
        */

        $middleware->alias([
            'permission' => CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();

/*
|--------------------------------------------------------------------------
| Public Path
|--------------------------------------------------------------------------
|
| روی هاست public_html استفاده می‌شود
| روی لوکال اگر public_html وجود نداشته باشد همان public باقی می‌ماند
|
*/

if (is_dir(base_path('public_html'))) {
    $app->usePublicPath(base_path('public_html'));
}

return $app;