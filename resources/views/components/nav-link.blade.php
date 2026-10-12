{{-- 
  ========================================================================================
  SISTEM INFORMASI MANAJEMEN FASILITAS & SARANA PRASARANA KAMPUS (CAVA)
  ========================================================================================
  NAMA FILE       : nav-link.blade.php
  LOKASI FILE     : resources/views/components/nav-link.blade.php
  DESKRIPSI       : Komponen Tautan Navigasi Horizontal Desktop (Desktop Navigation Link)
  ARSITEKTUR      : Blade View Component (UI Shared Component Laravel Breeze)
  AUTHOR          : Tim Pengembang CAVA & Sivitas Akademika
  
  FITUR UTAMA:
  1. Indikasi Status Aktif (Active State Indicator): Menampilkan garis bawah (bottom border) 
     berwarna indigo yang mencolok saat rute yang diakses sedang aktif.
  2. Transisi Visual Mulus: Menggunakan kelas transisi Tailwind (`transition duration-150 ease-in-out`) 
     untuk pergerakan warna hover dan fokus yang halus.
  3. Desain Responsif Desktop: Dirancang khusus untuk bilah navigasi mendatar 
     (`inline-flex items-center`) dengan penyesuaian padding vertikal dan horizontal.
  4. Penggabungan Atribut Dinamis ($attributes->merge): Mengizinkan penambahan atribut HTML 
     tambahan seperti `href`, `target`, atau atribut aksesibilitas `aria-*`.
  ========================================================================================
--}}

{{-- 
  Deklarasi Props Komponen:
  - `active`: Boolean yang menunjukkan apakah tautan saat ini sedang aktif dipilih pengguna.
--}}
@props(['active'])

@php
// Penentuan kelas gaya visual berdasarkan status aktif (Active State vs Inactive State):
$classes = ($active ?? false)
            // Gaya saat tautan berstatus AKTIF:
            // - Border bawah 2px berwarna indigo (`border-b-2 border-indigo-400 dark:border-indigo-600`)
            // - Warna teks tegas gelap/terang (`text-gray-900 dark:text-gray-100`)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
            // Gaya saat tautan berstatus TIDAK AKTIF (Default/Idle):
            // - Border bawah transparan (`border-transparent`)
            // - Warna teks abu-abu redup (`text-gray-500 dark:text-gray-400`)
            // - Efek perubahan warna teks dan border saat kursor diarahkan (hover)
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out';
@endphp

{{-- 
  Tag Anchor <a> Tautan Navigasi:
  - `$attributes->merge(['class' => $classes])`: Menggabungkan konfigurasi kelas CSS dengan atribut tautan (misal: href).
  - `{{ $slot }}`: Menampilkan teks label tautan yang dioper ke dalam komponen.
--}}
<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
