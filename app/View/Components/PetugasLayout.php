<?php
/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 13: OPERASIONAL PETUGAS SARPRAS: DASBOR MONITORING ANTREAN CERDAS (PTG-01 / US-8)
 * BERKAS: app/View/Components/PetugasLayout.php
 * ====================================================================================================
 *
 * DESKRIPSI BERKAS:
 * Kelas Komponen Blade (Blade Component Class) yang bertindak sebagai jembatan kontrol perenderan
 * untuk kerangka tata letak utama (Master Layout) operasional Petugas Sarpras (`layouts.petugas`).
 *
 * PERAN ARSITEKTURAL DALAM SISTEM CAVA:
 * 1. Penyedia Tag Kustom Komponen Blade:
 *    Mengizinkan view Blade di folder `resources/views/petugas/` untuk memanggil layout dengan
 *    sintaks modern deklaratif `<x-petugas-layout title="..." active="..."> ... </x-petugas-layout>`.
 * 2. Konsistensi Antarmuka Pengguna Petugas:
 *    Memastikan seluruh fitur operasional staf (dasbor, lembar antrean, matriks jadwal, dan
 *    manajemen tiket kerusakan) dibungkus oleh navigasi, header, dan sidebar petugas yang seragam.
 * 3. Enkapsulasi & Pemisahan Tanggung Jawab:
 *    Memisahkan logika instansiasi komponen dari representasi visual Blade template sehingga
 *    mempermudah penambahan data global layout (misalnya badge notifikasi shift piket di masa depan).
 * ====================================================================================================
 */

namespace App\View\Components;

// Import Base Class Component dari subsistem View Laravel
use Illuminate\View\Component;
// Import antarmuka View Contract untuk type-hinting method render()
use Illuminate\View\View;

/**
 * Class PetugasLayout
 *
 * Komponen pembungkus master layout untuk seluruh modul operasional Petugas Sarpras.
 */
class PetugasLayout extends Component
{
    /**
     * METHOD: render()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Mengembalikan view template Blade yang merepresentasikan komponen layout ini.
     *
     * PARAMETER:
     * Tidak memerlukan parameter langsung; atribut HTML/props yang dikirimkan ke tag `<x-petugas-layout>`
     * (seperti `title` dan `active`) akan secara otomatis diteruskan ke template view target.
     *
     * RETURN VALUE:
     * \Illuminate\View\View : Mengembalikan objek View yang merujuk pada berkas `resources/views/layouts/petugas.blade.php`.
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. Laravel mengevaluasi tag `<x-petugas-layout>` saat mem-parsing halaman view petugas.
     * 2. Instance kelas `PetugasLayout` dibuat dan method `render()` dieksekusi secara otomatis.
     * 3. Helper `view('layouts.petugas')` memuat file tata letak master `resources/views/layouts/petugas.blade.php`.
     * 4. Slot konten utama (`$slot` atau `@yield('content')`) diisi dengan elemen HTML anak yang ada di dalam tag komponen.
     */
    public function render(): View
    {
        // Syntax: view('layouts.petugas')
        // Memanggil template layout master petugas sarpras kampus
        return view('layouts.petugas');
    }
}
