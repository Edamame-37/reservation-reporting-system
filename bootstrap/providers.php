<?php
/**
 * NAMA FILE    : providers.php
 * LOKASI       : bootstrap/providers.php
 * FUNGSI       : Registri Sentral Service Provider Aplikasi (Laravel 11 Core)
 * DESKRIPSI    : Menyimpan daftar array seluruh Service Provider kustom yang akan
 *                dimuat dan di-bootstrapping secara otomatis saat kernel Laravel booting.
 *                Pada Laravel 11, file ini menggantikan array 'providers' di config/app.php.
 * CARA KERJA   : Saat siklus hidup (lifecycle) aplikasi dimulai:
 *                1. Framework membaca array ini.
 *                2. Menginstansiasi masing-masing class provider.
 *                3. Menjalankan method register() untuk mendaftarkan binding container/IoC.
 *                4. Menjalankan method boot() setelah semua provider selesai di-register.
 */

use App\Providers\AppServiceProvider;

// =========================================================================================
// REGISTRASI SERVICE PROVIDER APLIKASI
// =========================================================================================
// SYNTAX     : return [ ... ];
// CARA KERJA : Mengembalikan array PHP berisi daftar Class-String (FQCN) Service Provider.
// FITUR PHP  : Sintaks 'Class::class' menghasilkan representasi string nama kelas lengkap
//              secara aman dan dikenali oleh static analyzer (mencegah typo).
return [
    /**
     * AppServiceProvider
     * FUNGSI  : Service Provider utama tingkat aplikasi untuk mengonfigurasi global services,
     *           pengaturan skema database default (misal: Schema::defaultStringLength),
     *           Paginator bootstrap/Tailwind, atau Blade directives kustom.
     */
    AppServiceProvider::class,
];
