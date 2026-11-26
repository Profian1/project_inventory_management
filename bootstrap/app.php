<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| Suppress PHP 8.5 deprecation notices
|--------------------------------------------------------------------------
| Laravel 11 references PDO::MYSQL_ATTR_SSL_CA which PHP 8.5 deprecates.
| We keep the constant (for compatibility with the PHP 8.2/8.3 runtimes on
| InfinityFree/Render) and simply silence the deprecation notice here.
*/
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register the 'admin' middleware alias for role-based route protection.
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
