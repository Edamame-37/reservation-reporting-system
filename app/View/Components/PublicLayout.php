<?php
/**
 * ==============================================================================
 * NAMA FILE    : PublicLayout.php
 * MODUL        : Modul 4 - Portal Publik: Beranda, Katalog Fasilitas, Filter & Matriks Ketersediaan
 * KODE FITUR   : PUB-01 & PUB-02 (Arsitektur Layout & Komponen Blade)
 * ==============================================================================
 * DESKRIPSI:
 * Class komponen Blade (Blade Component Class) yang bertindak sebagai pembungkus utama
 * (layout wrapper) untuk seluruh halaman publik CAVA.
 *
 * CARA KERJA & INTEGRASI:
 * 1. Ketika sebuah view (seperti home.blade.php atau catalog.blade.php) menggunakan sintaks:
 *    <x-public-layout> ... </x-public-layout>
 * 2. Laravel Blade Engine secara otomatis mencari class di namespace App\View\Components\PublicLayout.
 * 3. Class ini mengeksekusi method render() yang mengembalikan view template layouts/public.blade.php.
 * 4. Konten HTML di antara tag pembuka dan penutup <x-public-layout> disuntikkan ke variabel
 *    default Blade bernama $slot pada master layout.
 * ==============================================================================
 */

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class PublicLayout extends Component
{
    /**
     * Membuat instance komponen layout baru.
     * Konstruktor dapat menerima props bila diperlukan (misal: title kustom atau meta tags),
     * namun pada implementasi publik CAVA, konfigurasi judul bersifat dinamis via slot.
     */
    public function __construct()
    {
        // Inisialisasi properti komponen (jika ada)
    }

    /**
     * Merender template tampilan Blade yang merepresentasikan komponen layout ini.
     *
     * @return \Illuminate\View\View File resources/views/layouts/public.blade.php
     *
     * CARA KERJA SYNTAX:
     * - return view('layouts.public'): Menginstruksikan view factory Laravel untuk
     *   memuat file master layout publik dan mengompilasinya bersama konten anak ($slot).
     */
    public function render(): View
    {
        return view('layouts.public');
    }
}
