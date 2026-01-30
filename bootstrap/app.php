<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'payment/aamarpay/callback',
            'payment/aamarpay/cancel',
        ]);

        // Don't encrypt cookies for payment callbacks to maintain session
        $middleware->encryptCookies(except: [
            'payment/aamarpay/callback',
            'payment/aamarpay/cancel',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
