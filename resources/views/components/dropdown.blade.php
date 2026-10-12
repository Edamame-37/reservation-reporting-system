{{-- 
  ========================================================================================
  SISTEM INFORMASI MANAJEMEN FASILITAS & SARANA PRASARANA KAMPUS (CAVA)
  ========================================================================================
  NAMA FILE       : dropdown.blade.php
  LOKASI FILE     : resources/views/components/dropdown.blade.php
  DESKRIPSI       : Komponen Blade Reusable untuk Menu Dropdown (Pop-over Menu)
  ARSITEKTUR      : Blade View Component (UI Shared Component Laravel Breeze)
  AUTHOR          : Tim Pengembang CAVA & Sivitas Akademika
  
  FITUR UTAMA:
  1. Kontrol Posisi Fleksibel: Mendukung perataan teks/menu ke sisi kiri ('left'), 
     atas ('top'), maupun kanan/akhir ('right'/default) yang ramah LTR dan RTL.
  2. Dimensi Lebar Dinamis: Memungkinkan kustomisasi prop `width` (misal: '48' -> 'w-48') 
     sesuai kebutuhan tata letak antarmuka.
  3. Integrasi Alpine.js & Transisi Halus: Membuka dan menutup menu secara mulus dengan 
     animasi skala (scale) dan opasitas (opacity) menggunakan direktif transisi bawaan Alpine.js.
  4. Deteksi Klik di Luar Kontainer: Menutup pop-over secara otomatis saat pengguna mengklik 
     di luar area menu (`@click.outside="open = false"`).
  ========================================================================================
--}}

{{-- 
  Deklarasi Props Komponen:
  - `align`: Posisi penyelarasan dropdown ('right', 'left', 'top' - default: 'right').
  - `width`: Lebar menu dropdown (default: '48' yang dikonversi menjadi 'w-48').
  - `contentClasses`: Kelas styling Tailwind tambahan untuk pembungkus isi konten menu.
--}}
@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white dark:bg-gray-700'])

@php
// Logika PHP Match Expression untuk menentukan titik asal transformasi animasi dan perataan posisi:
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

// Penyesuaian kelas lebar kontainer berdasarkan nilai prop `width`:
$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

{{-- 
  Kontainer Dropdown Utama:
  - `class="relative"`: Menetapkan konteks posisi relatif untuk penempatan popover absolut di dalamnya.
  - `x-data="{ open: false }"`: Menginisialisasi state lokal boolean `open` untuk mengontrol visibilitas menu.
  - `@click.outside="open = false"`: Event handler Alpine.js yang otomatis menutup dropdown jika klik terjadi di luar elemen.
  - `@close.stop="open = false"`: Menangani event 'close' khusus dan menghentikan propagasi event.
--}}
<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    {{-- Elemen Pemicu (Trigger): Menjalankan pembalikan state `open` saat diklik --}}
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    {{-- 
      Kontainer Menu Dropdown (Popover):
      - `x-show="open"`: Menampilkan atau menyembunyikan elemen berdasarkan nilai boolean `open`.
      - Transisi Masuk (Enter): Memudar dari transparan (opacity-0) dan sedikit mengecil (scale-95) ke ukuran normal dalam 200ms.
      - Transisi Keluar (Leave): Menghilang dengan cepat dalam 75ms.
      - `@click="open = false"`: Menutup dropdown seketika setelah salah satu item menu diklik.
    --}}
    <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute z-50 mt-2 {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }}"
            style="display: none;"
            @click="open = false">
        {{-- Pembungkus Isi Konten dengan Bayangan Halus dan Garis Tepi Transparan (Ring) --}}
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
