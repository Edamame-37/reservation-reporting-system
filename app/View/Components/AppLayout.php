<?php

/**
 * ====================================================================================================
 * NAMA FILE    : AppLayout.php
 * LOKASI       : app/View/Components/AppLayout.php
 * FUNGSI       : Blade Component Class Controller untuk Master Layout Pengguna Authenticated (Sivitas Mahasiswa/Dosen)
 * MODUL CAVA   : 9. Portal Pengguna (Sivitas): Dasbor Pribadi & Ringkasan Tiket Aktif
 * ====================================================================================================
 * 
 * ARSITEKTUR & CARA KERJA SISTEM:
 * 1. Blade Component Resolution:
 *    - Ketika file tampilan pengguna (seperti `resources/views/user/dashboard.blade.php`, `reservation-form`, dll.)
 *      menggunakan custom tag `<x-app-layout>`, compiler Laravel Blade secara otomatis memetakan tag tersebut
 *      ke class komponen ini: `App\View\Components\AppLayout`.
 * 2. Slot Data Injection:
 *    - Seluruh konten HTML yang dibungkus di dalam tag `<x-app-layout> ... </x-app-layout>` akan dikompilasi
 *      dan diinjeksikan secara otomatis ke dalam variabel `$slot` di dalam view `resources/views/layouts/app.blade.php`.
 * 3. Integrasi Layout & Pengalaman Pengguna (UX):
 *    - View yang dihasilkan (`layouts.app`) menyediakan struktur terpadu:
 *      * Global HTML Document metadata (charset, viewport, csrf-token).
 *      * Vite Asset Bundling (CSS & JS Tailwind / Alpine.js).
 *      * Sidebar Navigasi Kiri Multi-Role (`<x-cava.sidebar active="dashboard" />`).
 *      * Header Atas Terpadu (`<x-cava.header />`).
 *      * Area Konten Dinamis Utama (Main Dynamic Viewport).
 *      * Global Notification Toast container.
 * 
 * ====================================================================================================
 */

namespace App\View\Components;

// Mengimpor Base Class Component dari core framework Laravel untuk mendefinisikan custom Blade component.
use Illuminate\View\Component;

// Mengimpor interface View contract untuk type-hinting return value method render().
use Illuminate\View\View;

/**
 * Class AppLayout
 * 
 * Bertindak sebagai backend controller penghubung untuk tag komponen <x-app-layout>.
 */
class AppLayout extends Component
{
    /**
     * Menghasilkan view Blade yang merepresentasikan master layout aplikasi pengguna.
     * 
     * CARA KERJA & LOGIKA:
     * - Method ini dieksekusi oleh Blade compiler saat view yang menggunakan <x-app-layout> dirender.
     * - Mengembalikan instance Illuminate\View\View yang mengarah ke template 'resources/views/layouts/app.blade.php'.
     * - Variabel publik apa pun yang didefinisikan pada properti class ini (jika ada) akan otomatis
     *   diekstrak ke dalam scope variabel view tersebut.
     * 
     * @return \Illuminate\View\View Objek tampilan master layout pengguna (layouts.app)
     */
    public function render(): View
    {
        // Memanggil helper global view() untuk merender template Blade 'layouts/app.blade.php'
        return view('layouts.app');
    }
}
