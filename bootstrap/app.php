<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'           => \App\Http\Middleware\AdminMiddleware::class,
            'admin.tenant'    => \App\Http\Middleware\AdminTenantMiddleware::class,
            'role'            => \App\Http\Middleware\RoleMiddleware::class,
            'tenancy'         => \App\Http\Middleware\InitializeTenancyByToken::class,
            'tenancy.session' => \App\Http\Middleware\InitializeTenancyBySession::class,
            'superadmin'      => \App\Http\Middleware\SuperAdminMiddleware::class,
            'agent.token'     => \App\Http\Middleware\ValidateAgentToken::class,
        ]);
        $middleware->redirectGuestsTo('/admin/login');
        $middleware->validateCsrfTokens(except: [
            'asistencia/*',
            'iclock/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
