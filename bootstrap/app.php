<?php

use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\DynamicRateLimit;
use App\Http\Middleware\EnforceSessionTimeout;
use App\Http\Middleware\LogAudit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| Bootstrap Aplikasi Laravel 13 - SI-IPULBUAH
|--------------------------------------------------------------------------
| File ini menggantikan app/Http/Kernel.php pada versi Laravel lama.
| PENTING: gabungkan isi ini dengan bootstrap/app.php bawaan
| `composer create-project laravel/laravel` Anda - jangan ditimpa penuh,
| cukup tambahkan bagian ->withMiddleware() dan alias di bawah ini.
*/

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias role & audit-log dipakai di routes/api.php
        $middleware->alias([
            'role' => CheckRole::class,
            'audit' => LogAudit::class,
            'maintenance' => CheckMaintenanceMode::class,
            'session.timeout' => EnforceSessionTimeout::class,
        ]);

        // Terapkan Rate Limiting dinamis (fitur C.13) & Mode Maintenance ke seluruh API
        $middleware->api(append: [
            DynamicRateLimit::class,
            CheckMaintenanceMode::class,
        ]);

        // Sanctum: pastikan cookie/token SPA Vue dikenali stateful
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
