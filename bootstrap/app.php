<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

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
            'api.token'       => \App\Http\Middleware\ValidateApiToken::class,
        ]);
        $middleware->redirectGuestsTo('/admin/login');
        $middleware->validateCsrfTokens(except: [
            'asistencia/*',
            'iclock/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Reporte diario de personal para líderes – todos los días a las 8:00 AM
        $schedule->command('reporte:lideres')
            ->dailyAt('08:00')
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/reporte-lideres.log'));

        // Programación de turno – una entrada por cada combinación (dia, hora) distinta en tbl_empresas
        try {
            $configs = \Illuminate\Support\Facades\DB::table('tbl_empresas')
                ->where('is_active', true)
                ->whereNotNull('hora_ejecucion_turno')
                ->whereNotNull('dia_ejecucion_turno')
                ->select('dia_ejecucion_turno', 'hora_ejecucion_turno')
                ->distinct()
                ->get();

            foreach ($configs as $config) {
                $hora = substr($config->hora_ejecucion_turno, 0, 5);
                $dia  = (int) $config->dia_ejecucion_turno;

                $schedule->command("turno:aplicar --dia={$dia} --hora={$hora}")
                    ->dailyAt($hora)
                    ->days([$dia])
                    ->withoutOverlapping()
                    ->appendOutputTo(storage_path('logs/turno-aplicar.log'));
            }
        } catch (\Throwable $e) {
            // BD no disponible al cargar el scheduler
        }
    })
    ->create();
