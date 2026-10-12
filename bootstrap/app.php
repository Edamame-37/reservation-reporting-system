<?php
/**
 * NAMA FILE    : app.php
 * LOKASI       : bootstrap/app.php
 * FUNGSI       : Berkas Bootstrap Tunggal & Orkestrasi Kernel Aplikasi (Laravel 11 Core)
 * DESKRIPSI    : Titik masuk utama konfigurasi aplikasi di Laravel 11 yang menyatukan
 *                pendaftaran routing, middleware aliases, dan global exception handling
 *                menggunakan class ApplicationBuilder modern (menggantikan Http/Kernel.php
 *                dan Console/Kernel.php pada arsitektur Laravel versi sebelumnya).
 * CARA KERJA   : 1. Menginisialisasi Application instance dengan path root direktori proyek.
 *                2. Mendaftarkan file rute web, console Artisan, dan endpoint health check.
 *                3. Mendefinisikan alias middleware untuk otorisasi Spatie RBAC.
 *                4. Mengonfigurasi penanganan eksepsi agar merender respon JSON jika request API.
 *                5. Memanggil create() untuk mengembalikan instansiasi aplikasi siap pakai ke public/index.php.
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

// =========================================================================================
// 1. INISIALISASI APLIKASI (APPLICATION BUILDER)
// =========================================================================================
// SYNTAX     : Application::configure(basePath: dirname(__DIR__))
// CARA KERJA : Memanggil method statis configure() pada kelas Illuminate\Foundation\Application.
// FITUR PHP  : Menggunakan 'Named Arguments' (fitur PHP 8.0+) yaitu `basePath:`, sehingga
//              argumen diteruskan langsung ke parameter tanpa terikat urutan posisi.
// FUNGSI     : dirname(__DIR__) mendeteksi folder root utama proyek (satu tingkat di atas bootstrap/).
return Application::configure(basePath: dirname(__DIR__))

    // =====================================================================================
    // 2. PENDAFTARAN RUTE SISTEM (ROUTING CONFIGURATION)
    // =====================================================================================
    // SYNTAX     : ->withRouting(...)
    // CARA KERJA : Mendaftarkan jalur berkas rute yang akan dimuat oleh router Laravel.
    //              - web: Memuat rute berbasis web/session browser dari routes/web.php.
    //              - commands: Memuat perintah Artisan CLI dari routes/console.php.
    //              - health: Menyediakan endpoint otomatis '/up' untuk pemantauan liveness server
    //                (mengembalikan HTTP 200 OK jika kernel dan basis data dapat diakses).
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    // =====================================================================================
    // 3. KONFIGURASI ALIAS MIDDLEWARE (SPATIE RBAC INTEGRATION)
    // =====================================================================================
    // SYNTAX     : ->withMiddleware(function (Middleware $middleware): void { ... })
    // CARA KERJA : Closure callback menerima instance Middleware builder.
    //              Method alias() memetakan string pendek (alias) ke Fully Qualified Class Name (FQCN)
    //              middleware Spatie Permission, sehingga dapat dipanggil ringkas pada routing:
    //              Contoh penggunaan: Route::middleware(['auth', 'role:admin'])->group(...);
    // FITUR PHP  : Type-hinting ': void' menandakan closure ini melakukan mutasi konfigurasi tanpa nilai balik.
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            // Alias 'role': Memverifikasi apakah pengguna memiliki role tertentu (admin/petugas/pengguna)
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,

            // Alias 'permission': Memverifikasi izin atomik tertentu jika diterapkan
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,

            // Alias 'role_or_permission': Memeriksa kecocokan salah satu peran atau izin
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })

    // =====================================================================================
    // 4. PENANGANAN EKSEPSI GLOBAL (EXCEPTION HANDLING)
    // =====================================================================================
    // SYNTAX     : ->withExceptions(function (Exceptions $exceptions): void { ... })
    // CARA KERJA : Menangani bagaimana error / exception dirender kembali ke klien.
    // SYNTAX     : $exceptions->shouldRenderJsonWhen(fn (Request $request) => ...)
    // FITUR PHP  : Menggunakan 'Arrow Function' (fn (...) => ...) PHP 7.4+ yang ringkas dengan auto-capture variable.
    // LOGIKA     : Jika URL diawali 'api/*' ATAU header request mengirimkan 'Accept: application/json' (expectsJson),
    //              maka pesan kesalahan tidak akan dirender sebagai halaman HTML error Blade,
    //              melainkan diformat secara otomatis menjadi payload JSON standar API { message: ... }.
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })

    // =====================================================================================
    // 5. PEMBUATAN INSTANCE APLIKASI (BUILD & RETURN)
    // =====================================================================================
    // SYNTAX     : ->create()
    // CARA KERJA : Mengkompilasi seluruh konfigurasi di atas dan mengembalikan instance kelas
    //              Illuminate\Foundation\Application siap pakai yang menjadi jantung eksekusi Laravel.
    ->create();
