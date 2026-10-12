<?php
/**
 * ==============================================================================
 * NAMA FILE    : GuestLayout.php
 * MODUL        : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
 * KODE FITUR   : AUTH-01 & AUTH-02 (Class Komponen Pembungkus Guest Layout)
 * ==============================================================================
 * DESKRIPSI:
 * Class komponen Blade (Blade Component Class) yang merepresentasikan layout khusus
 * untuk seluruh halaman otentikasi pengunjung / tamu (guest):
 * - Halaman Login (`auth/login.blade.php`)
 * - Halaman Registrasi Mandiri (`auth/register.blade.php`)
 * - Halaman Lupa Sandi (`auth/forgot-password.blade.php`)
 * - Halaman Reset Sandi (`auth/reset-password.blade.php`)
 * - Halaman Konfirmasi Sandi (`auth/confirm-password.blade.php`)
 * - Halaman Verifikasi Email (`auth/verify-email.blade.php`)
 *
 * CARA KERJA & INTEGRASI:
 * Saat template Blade menggunakan sintaks `<x-guest-layout>`, Laravel memetakan tag tersebut
 * ke class ini dan memanggil method `render()` untuk memuat `resources/views/layouts/guest.blade.php`.
 * Konten halaman anak disuntikkan ke variabel `$slot` pada layout tersebut.
 * ==============================================================================
 */

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * Membuat instance komponen layout baru.
     */
    public function __construct()
    {
        // Konstruktor komponen (dapat menerima atribut jika diperlukan)
    }

    /**
     * Merender template tampilan Blade yang merepresentasikan pembungkus layout guest.
     *
     * @return \Illuminate\View\View File resources/views/layouts/guest.blade.php
     *
     * CARA KERJA & SYNTAX:
     * - return view('layouts.guest'): Menginstruksikan view factory Laravel untuk
     *   mengompilasi master layout tamu bersama konten anak ($slot).
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
