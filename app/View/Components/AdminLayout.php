<?php

/**
 * ==============================================================================================
 * CLASS KOMPONEN BLADE: MASTER LAYOUT KONSOL ADMIN (ADMIN LAYOUT COMPONENT)
 * ==============================================================================================
 * Berkas: app/View/Components/AdminLayout.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Berfungsi sebagai backend class pengendali untuk tag komponen Blade kustom `<x-admin-layout>`.
 * 2. Membungkus halaman-halaman konsol Administrator (Dashboard, User Management, Facility Master,
 *    dan Rekapitulasi Laporan) ke dalam kerangka tata letak yang konsisten (Bento UI standard).
 * 3. Menyediakan mekanisme injeksi konten dinamis melalui variabel bawaan Blade `$slot`.
 * ==============================================================================================
 */

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     * 
     * CARA KERJA & ALUR:
     * - Ketika Blade menemukan tag `<x-admin-layout>` pada file view (seperti `facility-master.blade.php`),
     *   Laravel menginstansiasi class ini dan mengeksekusi method `render()`.
     * - Method ini mengembalikan instance View `resources/views/layouts/admin.blade.php`.
     * - Konten apa pun yang berada di dalam tag `<x-admin-layout>...</x-admin-layout>` akan otomatis
     *   disuntikkan ke dalam placeholder variabel `{{ $slot }}` pada berkas master layout tersebut.
     * 
     * @return View
     */
    public function render(): View
    {
        return view('layouts.admin');
    }
}

