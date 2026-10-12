{{-- 
  ====================================================================================================
  NAMA FILE      : app.blade.php
  LOKASI         : resources/views/layouts/app.blade.php
  FUNGSIONALITAS : Kerangka Master Layout Utama Antarmuka Portal Pengguna (Mahasiswa / Dosen)
  MODUL CAVA     : 9. Portal Pengguna (Sivitas): Dasbor Pribadi & Ringkasan Tiket Aktif
  ====================================================================================================

  DESKRIPSI ARSITEKTUR & PERAN SISTEM:
  1. Master Layout Terotentikasi Sivitas:
     - Berkas ini merupakan kerangka dasar (shell/scaffolding) untuk seluruh antarmuka yang diakses
       oleh pengguna dengan peran Sivitas Akademika (`role:pengguna`), mencakup mahasiswa dan dosen.
     - Didesain dengan prinsip Campus Modern Minimalist yang mengedepankan efisiensi visual, hierarki
       informasi yang bersih, keterbacaan tinggi, dan kinerja rendering optimal.
  
  2. Integrasi Komponen Blade & Pola Dual-Support:
     - Mendukung dua pola rendering Blade secara simultan:
       a. Slot Injection (`{{ $slot }}`): Digunakan saat dipanggil sebagai Blade Component (`<x-app-layout>`).
       b. Section Yielding (`@yield('content')`): Digunakan jika diwarisi via `@extends('layouts.app')`.
  
  3. Desain Responsif & State Management (Alpine.js):
     - `sidebarOpen`: Mengelola status visibilitas navigasi samping secara adaptif berdasarkan
       lebar viewport browser pengguna (`window.innerWidth >= 768px`).
     - `isLoaded`: Mencegah Cumulative Layout Shift (CLS) dan animasi transisi liar saat halaman
       sedang dimuat pertama kali melalui `$nextTick`.
  
  4. Komponen Modular Terpadu:
     - `<x-cava.header>`: Menyajikan header universal tetap di bagian atas (tinggi 64px) dengan info profil pengguna.
     - `<x-cava.sidebar>`: Menyajikan panel navigasi samping khusus portal sivitas (`role="user"`).
  ====================================================================================================
--}}

{{-- Direktif @props: Mendefinisikan properti default yang diterima dari pemanggil komponen Blade --}}
@props([
    // Judul halaman bawaan jika pemanggil tidak mendefinisikannya secara spesifik
    'title' => 'CAVA - Portal Mahasiswa & Dosen',
    // Penanda rute menu aktif pada sidebar navigasi (default: 'dashboard')
    'active' => 'dashboard'
])

<!DOCTYPE html>
{{-- Tag html dengan lokalisasi aplikasi Laravel dan kelas utilitas h-full untuk layout tinggi penuh --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    {{-- Meta Charset UTF-8: Standar encoding karakter internasional untuk rendering teks tanpa anomali --}}
    <meta charset="utf-8">

    {{-- Meta Viewport: Memastikan antarmuka mobile responsive dan berskala 1:1 terhadap layar gawai --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Meta CSRF Token: Diperlukan oleh pustaka JavaScript / AJAX (Axios/Fetch) untuk keamanan permohonan HTTP POST/PUT/DELETE --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Judul Halaman Dinamis: Menggabungkan nilai prop $title dengan brand CAVA --}}
    <title>{{ $title }} | CAVA</title>

    {{-- Favicon Aplikasi: Ikon resmi format WebP berbobot ringan di folder public/assets/images --}}
    <link rel="icon" type="image/webp" href="{{ asset('assets/images/logo.webp') }}">

    {{-- Tipografi Google Fonts: Menghubungkan font keluarga Inter untuk estetika tipografi bersih modern --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    {{-- Direktif @vite: Kompilasi bundle CSS (Tailwind) dan JavaScript aplikasi secara modular via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js CDN Backup: Menjamin fungsionalitas reaktivitas front-end tetap berjalan jika asset bundle lokal tertunda --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Gaya CSS Inline Kustom: Pengendalian FOUC (Flash of Unstyled Content) & Konfigurasi Google Material Symbols --}}
    <style>
        /* Mencegah elemen Alpine.js berkedip sebelum script selesai dieksekusi di browser */
        [x-cloak] { display: none !important; }
        
        /* Konfigurasi ketebalan dan dimensi baku font icon Material Symbols */
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>

{{-- 
  Elemen <body>:
  - x-data: Mendeklarasikan reactive state Alpine.js (sidebarOpen & isLoaded)
  - x-init: Menunggu hingga DOM selesai dirender ($nextTick) sebelum mengaktifkan transisi CSS halus
  - class: Memberikan background netral slate-50, tipografi sans, text slate-800, dan struktur flex vertikal
--}}
<body x-data="{ sidebarOpen: window.innerWidth >= 768, isLoaded: false }" x-init="$nextTick(() => { isLoaded = true })" class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col">
    
    {{-- 
      1. Komponen Universal Top Header CAVA:
      - Menampilkan navbar tetap bagian atas dengan tinggi 64px.
      - Parameter userName & userIdentifier diambil langsung dari session akun autentikasi Auth::user().
      - Jika user belum login (fallback), menggunakan nama representatif sivitas.
    --}}
    <x-cava.header 
        :showProfile="true" 
        userName="{{ auth()->user()->name ?? 'Dimas Pratama' }}"
        userRole="{{ auth()->user()->role === 'pengguna' ? (auth()->user()->department ?? 'Sivitas Akademika') : 'Pengguna Sivitas' }}"
        userIdentifier="NIM/NIP: {{ auth()->user()->identity_number ?? auth()->user()->nim ?? '-' }}"
        title="CAVA Portal" 
        subtitle="Campus Venue Access"
        :pageName="$title"
    />

    {{-- 
      2. Komponen Sidebar Navigasi Kiri (Role: User):
      - Merender menu vertikal khusus mahasiswa dan dosen: Dasbor Saya, Formulir Reservasi, Riwayat, dan Pelaporan Kerusakan.
      - :active="$active" memberikan sorotan warna aktif pada rute yang sedang dibuka.
    --}}
    <x-cava.sidebar role="user" :active="$active" />

    {{-- 
      3. Pembungkus Area Konten Utama Halaman:
      - :class md:pl-64: Memberikan margin offset kiri selebar 256px ketika sidebar dalam status terbuka pada layar desktop (>= md).
      - pt-16: Memberikan offset atas selebar 64px agar konten tidak tertutup oleh navbar header yang melayang (fixed).
    --}}
    <div :class="{'md:pl-64': sidebarOpen, 'transition-all duration-300': isLoaded}" class="flex-1 flex flex-col min-h-screen pt-16">
        
        {{-- Elemen <main>: Penampung konten dinamis yang dibatasi lebar maksimalnya (1600px) untuk keterbacaan prima --}}
        <main class="flex-1 w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{-- Menginjeksikan konten Blade Component jika template dipanggil via <x-app-layout> --}}
            {{ $slot ?? '' }}

            {{-- Menginjeksikan konten jika template diwarisi menggunakan sintaks @extends & @section('content') --}}
            @yield('content')
        </main>

        {{-- 
          Footer Minimalis:
          - Memberikan hak cipta resmi institusi dan penanda status integrasi ke SIM-SARPRAS.
          - mt-auto memastikan footer selalu berada di posisi terbawah layar.
        --}}
        <footer class="px-6 py-4 bg-white border-t border-slate-200 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2 mt-auto">
            <span>&copy; {{ date('Y') }} CAVA - Biro Sarana & Prasarana Kampus. Terintegrasi SIM-SARPRAS.</span>
            <span class="text-slate-400">Portal Pengguna Aktif</span>
        </footer>
    </div>

</body>
</html>
