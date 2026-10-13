<?php

/**
 * ==============================================================================================
 * SISTEM INFORMASI MANAJEMEN FASILITAS & SARANA PRASARANA KAMPUS (CAVA)
 * ==============================================================================================
 * Berkas          : app/Providers/AppServiceProvider.php
 * Komponen        : AppServiceProvider (Pusat Bootstrapping Layanan Aplikasi)
 * Layer Arsitektur: Service Provider / Framework Core Bootstrapping
 * ==============================================================================================
 * PERAN & FUNGSI UTAMA:
 * 1. Berfungsi sebagai titik sentral konfigurasi bootstrap aplikasi tingkat global pada Laravel.
 * 2. Mendaftarkan binding service container (IoC) dan menginisialisasi pustaka pihak ketiga.
 * 3. Menetapkan styling pagination default aplikasi menggunakan Tailwind CSS agar seluruh tautan
 *    paginasi tabel (daftar fasilitas, riwayat reservasi, antrean aduan) tampil seragam dan responsif.
 * ==============================================================================================
 */

// ==============================================================================================
// 1. DEKLARASI NAMESPACE (PHP NATIVE)
// ==============================================================================================
// SYNTAX     : namespace App\Providers;
// UNTUK APA  : Mengelompokkan class ini ke dalam namespace 'App\Providers' sesuai konvensi PSR-4.
// CARA KERJA : Composer memetakan namespace ini ke folder fisik `app/Providers/` sehingga file
//              dapat di-load secara otomatis tanpa require manual.
namespace App\Providers;

// ==============================================================================================
// 2. IMPORT NAMESPACE & DEPENDENCIES (PHP NATIVE 'use')
// ==============================================================================================
// SYNTAX     : use Illuminate\Support\ServiceProvider;
// UNTUK APA  : Mengimpor class dasar ServiceProvider bawaan Laravel agar dapat diwarisi.
// CARA KERJA : Diuraikan saat kompilasi file untuk mengaitkan class abstrak `ServiceProvider`.
use Illuminate\Support\ServiceProvider;

// ==============================================================================================
// 3. DEKLARASI CLASS & INHERITANCE (PHP NATIVE 'class ... extends ...')
// ==============================================================================================
// SYNTAX     : class AppServiceProvider extends ServiceProvider
// UNTUK APA  : Mendefinisikan class penyedia layanan aplikasi yang mewarisi siklus hidup Service Provider Laravel.
// CARA KERJA : Framework Laravel secara otomatis memanggil method `register()` terlebih dahulu untuk
//              seluruh provider, baru kemudian mengeksekusi method `boot()` saat container siap.
class AppServiceProvider extends ServiceProvider
{
    // ==========================================================================================
    // 4. REGISTRASI SERVICE CONTAINER (LARAVEL SERVICE PROVIDER LIFECYCLE)
    // ==========================================================================================
    // SYNTAX     : public function register(): void
    // UNTUK APA  : Mendaftarkan binding ke dalam Laravel Inversion of Control (IoC) Container.
    // CARA KERJA : Dieksekusi sangat awal sebelum seluruh komponen framework di-bootstrapping.
    //              Hanya digunakan untuk binding kelas/interface, dilarang mengeksekusi logika bisnis
    //              atau event listener di dalam method ini untuk mencegah race condition dependencies.
    // FITUR PHP  : Type-hint `: void` (PHP 7.1+) menegaskan bahwa method tidak mengembalikan nilai.
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Tempat mendaftarkan binding IoC container kustom jika diperlukan di masa depan.
    }

    // ==========================================================================================
    // 5. BOOTSTRAPPING LAYANAN APLIKASI (LARAVEL BOOT METHOD)
    // ==========================================================================================
    // SYNTAX     : public function boot(): void
    // UNTUK APA  : Menjalankan konfigurasi bootstrapping setelah seluruh service provider terdaftar.
    // CARA KERJA : Pada tahap ini, seluruh container bindings, routing, dan events sudah selesai dimuat,
    //              sehingga aman untuk memanggil facade, model events, view composers, atau paginator.
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ======================================================================================
        // 6. KONFIGURASI ENGINE PAGINASI BLADE (LARAVEL PAGINATOR HELPER)
        // ======================================================================================
        // SYNTAX     : \Illuminate\Pagination\Paginator::useTailwind();
        // UNTUK APA  : Menginstruksikan Paginator Laravel agar merender template Tailwind CSS
        //              saat method `$collection->links()` dipanggil di berkas Blade view.
        // CARA KERJA : Memanggil method statis `useTailwind()` pada class Paginator, menetapkan
        //              view 'pagination::tailwind' sebagai default renderer.
        \Illuminate\Pagination\Paginator::useTailwind();
    }
}
