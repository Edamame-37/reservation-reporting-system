{{-- 
  ========================================================================================
  SISTEM INFORMASI MANAJEMEN FASILITAS & SARANA PRASARANA KAMPUS (CAVA)
  ========================================================================================
  NAMA FILE       : dropdown-link.blade.php
  LOKASI FILE     : resources/views/components/dropdown-link.blade.php
  DESKRIPSI       : Komponen Tautan Item Menu Dropdown (Dropdown Item Link)
  ARSITEKTUR      : Blade View Component (UI Shared Component Laravel Breeze)
  AUTHOR          : Tim Pengembang CAVA & Sivitas Akademika
  
  FITUR UTAMA:
  1. Penggabungan Atribut Cerdas ($attributes->merge): Mengizinkan pengoperan atribut HTML 
     tambahan seperti `href`, `onclick`, `target`, `id` secara dinamis tanpa merusak 
     styling dasar.
  2. Desain Interaktif Terpadu: Menyediakan styling block penuh (`block w-full`) dengan efek 
     hover dan focus yang serasi pada mode terang maupun mode gelap (dark mode).
  3. Aksesibilitas Terjamin: Dilengkapi dengan pengaturan `focus:outline-none` yang diimbangi 
     perubahan warna latar agar navigasi via keyboard tetap intuitif.
  ========================================================================================
--}}

{{-- 
  Tag Anchor <a> Tautan Dropdown:
  - `$attributes->merge(['class' => '...'])`: Menggabungkan styling Tailwind default dengan 
    setiap kelas atau atribut khusus yang diteruskan saat pemanggilan komponen.
  - `block w-full`: Memastikan area klik mencakup seluruh lebar baris dropdown.
  - `px-4 py-2`: Memberikan padding horizontal dan vertikal yang nyaman untuk sentuhan/klik.
  - `text-start text-sm`: Mengatur perataan teks ke awal (ramah LTR/RTL) dengan ukuran 14px.
  - `hover:bg-gray-100 dark:hover:bg-gray-800`: Memberikan umpan balik visual saat kursor diarahkan ke menu.
  - `transition duration-150 ease-in-out`: Menghasilkan efek peralihan warna yang lembut.
  - `{{ $slot }}`: Menampilkan teks label atau ikon yang disematkan di dalam komponen.
--}}
<a {{ $attributes->merge(['class' => 'block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out']) }}>{{ $slot }}</a>
