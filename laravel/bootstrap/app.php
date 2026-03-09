<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminAuth::class,
            'admin.role' => \App\Http\Middleware\AdminRole::class,
            'canonical' => \App\Http\Middleware\CanonicalRedirect::class,
        ]);

        // Apply canonical redirect globally (runs for ALL requests,
        // including non-matching routes, so uppercase → lowercase works)
        $middleware->prepend(\App\Http\Middleware\CanonicalRedirect::class);

        // Query profiling (active when QUERY_LOG_ENABLED=true or APP_DEBUG=true)
        $middleware->web(append: [
            \App\Http\Middleware\QueryProfiler::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
