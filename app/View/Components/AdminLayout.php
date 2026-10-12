<?php

/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 6: KONSOL ADMIN - VERIFIKASI AKUN PENDING SIVITAS & MANAJEMEN PENGGUNA INTERNAL (ADM-01 & ADM-02)
 * BERKAS: app/View/Components/AdminLayout.php
 * ====================================================================================================
 * 
 * DESKRIPSI BERKAS:
 * Berkas ini adalah PHP Component Class untuk komponen Blade `<x-admin-layout>`.
 * Berfungsi sebagai fondasi tata letak (master layout wrapper) untuk seluruh halaman panel Super Admin,
 * termasuk Konsol Manajemen Pengguna (ADM-01/02), Dasbor Eksekutif (ADM-04), dan Master Fasilitas (ADM-03).
 * 
 * PERAN & ARSITEKTUR DALAM SISTEM CAVA:
 * 1. Abstraksi Layout Khusus Admin: Menghubungkan tag komponen Blade deklaratif `<x-admin-layout>`
 *    dengan template view `resources/views/layouts/admin.blade.php`.
 * 2. Konsistensi Antarmuka Bento UI: Memastikan navigasi sidebar terpadu, header profil, notifikasi flash,
 *    dan tema visual seragam di seluruh modul administrasi.
 * 3. Injeksi Konten Dinamis: Konten unik setiap halaman admin disuntikkan secara otomatis melalui
 *    variabel slot `$slot` bawaan Blade component compiler.
 * ====================================================================================================
 */

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Class AdminLayout
 * 
 * Mewakili representasi backend komponen Blade layout admin.
 */
class AdminLayout extends Component
{
    /**
     * Mengembalikan instance tampilan Blade yang merepresentasikan layout admin.
     * 
     * CARA KERJA & ALUR:
     * 1. Ketika parser Blade mendeteksi tag pembuka `<x-admin-layout>` pada berkas view
     *    (seperti `resources/views/admin/user-management.blade.php`), Laravel secara otomatis
     *    menginstansiasi class ini dan memanggil method `render()`.
     * 2. Method `render()` mengembalikan view helper `view('layouts.admin')`.
     * 3. Seluruh blok konten HTML yang berada di antara `<x-admin-layout>` dan `</x-admin-layout>`
     *    akan disuntikkan ke dalam placeholder variabel `{{ $slot }}` pada berkas layout master.
     * 
     * SYNTAX:
     * - `view('layouts.admin')`: Fungsi helper Laravel untuk memuat berkas template Blade dari
     *   path `resources/views/layouts/admin.blade.php`.
     *
     * @return View
     */
    public function render(): View
    {
        return view('layouts.admin');
    }
}
