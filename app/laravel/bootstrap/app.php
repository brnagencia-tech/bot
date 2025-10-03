<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant.set' => App\Http\Middleware\SetTenantContext::class,
            'role.global' => App\Http\Middleware\EnsureGlobalRole::class,
            'role.tenant' => App\Http\Middleware\EnsureTenantRole::class,
        ]);

        // Ensure tenant context is available on web requests after auth
        $middleware->appendToGroup('web', [
            App\Http\Middleware\SetTenantContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
