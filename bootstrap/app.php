<?php
/**
 * NAMA FILE    : app.php
 * FUNGSI       : Berkas bootstrap dan konfigurasi orkestrasi aplikasi Laravel 11
 * DESKRIPSI    : Mengonfigurasi perutean (routing web, console, health check), alias middleware Spatie RBAC, dan penanganan exception API.
 * CARA KERJA   : Membangun instance Application dengan konfigurasi terpusat (ApplicationBuilder) dan mengembalikan kernel aplikasi.
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
