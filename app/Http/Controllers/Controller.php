<?php

/**
 * ==============================================================================================
 * SISTEM INFORMASI MANAJEMEN FASILITAS & SARANA PRASARANA KAMPUS (CAVA)
 * ==============================================================================================
 * Berkas          : app/Http/Controllers/Controller.php
 * Komponen        : Controller (Base Controller / Kelas Dasar Pengontrol HTTP)
 * Layer Arsitektur: HTTP Controller Layer (MVC Architecture)
 * ==============================================================================================
 * PERAN & FUNGSI UTAMA:
 * 1. Bertindak sebagai kelas dasar abstrak (abstract base controller) untuk seluruh controller
 *    di dalam aplikasi CAVA (Public, User, Petugas, dan Admin).
 * 2. Menyediakan pondasi modular untuk berbagi traits, middleware dasar, atau helper method
 *    yang berlaku secara universal ke seluruh controller turunan.
 * 3. Mengikuti arsitektur lean controller Laravel 11 di mana fitur-fitur yang tidak digunakan
 *    tidak lagi di-load secara default untuk meningkatkan performa bootstrap dan respon HTTP.
 * ==============================================================================================
 */

// ==============================================================================================
// 1. DEKLARASI NAMESPACE (PHP NATIVE)
// ==============================================================================================
// SYNTAX     : namespace App\Http\Controllers;
// UNTUK APA  : Mengelompokkan class ini ke dalam hierarki namespace virtual 'App\Http\Controllers'
//              sesuai konvensi standar PSR-4 Composer.
// CARA KERJA : Mencegah benturan nama dengan controller dari vendor paket eksternal. Composer
//              memetakan namespace ini ke folder fisik `app/Http/Controllers/`.
namespace App\Http\Controllers;

// ==============================================================================================
// 2. DEKLARASI KELAS ABSTRAK (PHP OOP 'abstract class')
// ==============================================================================================
// SYNTAX     : abstract class Controller
// UNTUK APA  : Menandai bahwa kelas ini adalah kelas cetak biru abstrak yang TIDAK BOLEH
//              diinstansiasi secara langsung (`new Controller()`), melainkan harus diwarisi (extends)
//              oleh kelas-kelas controller konkret lainnya.
// CARA KERJA : Jika ada kode yang mencoba menginstansiasi class abstrak secara langsung, PHP engine
//              akan melempar Fatal Error (`Cannot instantiate abstract class`).
abstract class Controller
{
    // Wadah penempatan logika bersama / shared helper methods untuk controller turunan.
}
