{{-- 
  ========================================================================================
  SISTEM INFORMASI MANAJEMEN FASILITAS & SARANA PRASARANA KAMPUS (CAVA)
  ========================================================================================
  NAMA FILE       : responsive-nav-link.blade.php
  LOKASI FILE     : resources/views/components/responsive-nav-link.blade.php
  DESKRIPSI       : Komponen Tautan Navigasi Drawer Vertikal Mobile (Mobile Navigation Link)
  ARSITEKTUR      : Blade View Component (UI Shared Component Laravel Breeze)
  AUTHOR          : Tim Pengembang CAVA & Sivitas Akademika
  
  FITUR UTAMA:
  1. Tata Letak Blok Penuh (Block Full-Width): Disesuaikan khusus untuk tata letak layar sentuh 
     smartphone (`block w-full`) agar mudah ditekan jari pengguna.
  2. Indikator Garis Samping (Left Border Indicator): Menggunakan garis tebal vertikal di sisi kiri 
     (`border-l-4`) untuk menandai menu aktif secara jelas pada drawer vertikal.
  3. Warna Latar Belakang Terfokus: Memberikan highlight latar belakang lembut berwarna indigo 
     (`bg-indigo-50 dark:bg-indigo-900/50`) ketika item menu sedang aktif dipilih.
  4. Efek Interaktif Responsif: Menyediakan styling hover dan focus yang adaptif terhadap mode gelap.
  ========================================================================================
--}}

{{-- 
  Deklarasi Props Komponen:
  - `active`: Boolean yang menandakan apakah item menu ini adalah halaman yang sedang dibuka.
--}}
@props(['active'])

@php
// Penentuan kelas gaya visual berdasarkan status aktif (Active State vs Inactive State untuk Mobile):
$classes = ($active ?? false)
            // Gaya saat menu berstatus AKTIF:
            // - Border kiri 4px berwarna indigo (`border-l-4 border-indigo-400 dark:border-indigo-600`)
            // - Latar belakang indigo muda transparan (`bg-indigo-50 dark:bg-indigo-900/50`)
            // - Teks berwarna indigo tegas (`text-indigo-700 dark:text-indigo-300`)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 dark:border-indigo-600 text-start text-base font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/50 focus:outline-none focus:text-indigo-800 dark:focus:text-indigo-200 focus:bg-indigo-100 dark:focus:bg-indigo-900 focus:border-indigo-700 dark:focus:border-indigo-300 transition duration-150 ease-in-out'
            // Gaya saat menu berstatus TIDAK AKTIF (Default):
            // - Border kiri transparan (`border-l-4 border-transparent`)
            // - Teks berwarna abu-abu netral (`text-gray-600 dark:text-gray-400`)
            // - Efek hover mengubah warna latar abu-abu terang (`hover:bg-gray-50 dark:hover:bg-gray-700`)
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-700 focus:border-gray-300 dark:focus:border-gray-600 transition duration-150 ease-in-out';
@endphp

{{-- 
  Tag Anchor <a> Tautan Navigasi Responsif Mobile:
  - `$attributes->merge(['class' => $classes])`: Menggabungkan styling kelas dinamis dengan atribut lainnya.
  - `{{ $slot }}`: Menampilkan label teks dari item menu navigasi.
--}}
<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
