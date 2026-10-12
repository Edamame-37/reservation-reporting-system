{{-- 
====================================================================================================
SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
MODUL 13: OPERASIONAL PETUGAS SARPRAS: DASBOR MONITORING ANTREAN CERDAS (PTG-01 / US-8)
BERKAS: resources/views/layouts/petugas.blade.php
====================================================================================================

DESKRIPSI BERKAS:
Kerangka Tata Letak Induk (Master Layout) yang membungkus seluruh antarmuka operasional staf UPT
Sarana dan Prasarana Kampus (Role: 'petugas'). Menyediakan struktur terpadu mencakup navigasi header
universal CAVA, sidebar khusus operasional sarpras, area konten responsif, dan footer sistem.

PERAN ARSITEKTURAL DALAM SISTEM CAVA:
1. Master Shell untuk Portal Petugas:
   Menyediakan bingkai kerja terpadu bagi modul:
   - Dasbor Monitoring Antrean Cerdas (PTG-01)
   - Matriks Jadwal 30 Menit Operasional (PTG-02 / US-9)
   - Lembar Kerja Manajemen Persetujuan, Penolakan, & Pembatalan Darurat (PTG-02 & PTG-03)
   - Manajemen Tiket Kerusakan, Resolusi & Mode Perbaikan (PTG-04 & PTG-05)
2. Manajemen Properti Komponen (@props):
   Menerima parameter judul halaman (`$title`) dan penanda rute navigasi aktif (`$active`)
   untuk sinkronisasi highlight menu sidebar dan judul tab browser.
3. Integrasi Desain Sistem Modern Campus Minimalist:
   Menggabungkan Google Fonts Inter, Google Material Symbols Outlined, bundel aset Tailwind CSS via Vite,
   serta reaktivitas Alpine.js untuk kontrol buka-tutup drawer sidebar pada layar mobile maupun desktop.
====================================================================================================
--}}

{{--
  PENJELASAN DIREKTIF @props:
  - 'title'  : Judul spesifik halaman yang ditampilkan pada tab browser dan header (default: 'CAVA - Pusat Operasional & Sarpras Kampus').
  - 'active' : Identifier menu navigasi yang sedang aktif ('dashboard', 'reservations', 'matrix', 'reports') untuk pewarnaan aktif di sidebar.
--}}
@props([
    'title' => 'CAVA - Pusat Operasional & Sarpras Kampus',
    'active' => 'dashboard'
])

<!DOCTYPE html>
{{-- 
  Syntax: lang="{{ str_replace('_', '-', app()->getLocale()) }}"
  Menetapkan atribut bahasa HTML secara dinamis sesuai locale aplikasi Laravel (misal 'id' atau 'en-US').
--}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    {{-- Pengaturan Karakter Encoding UTF-8 Standar Web Modern --}}
    <meta charset="utf-8">
    {{-- Viewport Responsif untuk Skalabilitas Layar Mobile, Tablet, dan Desktop --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Token CSRF (Cross-Site Request Forgery) untuk Perlindungan Keamanan Permintaan AJAX/Fetch --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Judul Halaman Dinamis Berdasarkan Prop $title --}}
    <title>{{ $title }} | CAVA</title>
    {{-- Favicon Logo Resmi Kampus --}}
    <link rel="icon" type="image/webp" href="{{ asset('assets/images/logo.webp') }}">

    {{-- Preconnect & Pengunduhan Tipografi Google Fonts (Inter) & Ikon Material Symbols --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    {{-- 
      Direktif Vite: Memuat Bundel CSS & JS Terkompilasi
      - 'resources/css/app.css' : Gaya utilitas Tailwind CSS, variabel tema warna CAVA, dan styling custom.
      - 'resources/js/app.js'   : Logika JavaScript klien dan inisialisasi framework Alpine.js.
    --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js CDN Backup: Menjamin interaktivitas tetap berjalan jika bundel lokal mengalami kendala muat --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Kustomisasi CSS In-page: x-cloak untuk mencegah Flash of Unstyled Content (FOUC) saat Alpine dimuat --}}
    <style>
        [x-cloak] { display: none !important; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
{{--
  ELEMEN BODY DENGAN STATE ALPIN.JS:
  - x-data="{ sidebarOpen: window.innerWidth >= 768, isLoaded: false }":
    Mengelola visibilitas sidebar (otomatis terbuka di layar desktop >= 768px) dan penanda kesiapan DOM.
  - x-init="$nextTick(() => { isLoaded = true })":
    Menyalakan flag `isLoaded` setelah siklus render awal selesai untuk mencegah animasi transisi yang canggung saat first load.
--}}
<body x-data="{ sidebarOpen: window.innerWidth >= 768, isLoaded: false }" x-init="$nextTick(() => { isLoaded = true })" class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col">

    {{-- 
      1. UNIVERSAL TOP HEADER CAVA (Tinggi 64px):
      Menampilkan identitas pengguna operasional (nama staf, peran zona penugasan, NIP)
      dan tombol toggle drawer navigasi untuk kenyamanan aksesibilitas.
    --}}
    <x-cava.header 
        :showProfile="true" 
        userName="{{ auth()->user()->name ?? 'Bambang Setyawan' }}"
        userRole="Petugas Sarpras Zona A"
        userIdentifier="NIP. {{ auth()->user()->nip ?? '197804122005011002' }}"
        title="CAVA Operasional" 
        subtitle="Campus Venue Access"
        pageName="{{ $title }}"
    />

    {{-- 
      2. SIDEBAR NAVIGASI KIRI OPERASIONAL SARPRAS:
      Memuat menu navigasi khusus peran 'petugas': Dasbor, Antrean Reservasi, Matriks Jadwal, dan Tiket Kerusakan.
    --}}
    <x-cava.sidebar role="petugas" :active="$active" />

    {{-- 
      3. AREA KONTEN UTAMA HALAMAN:
      - ':class="{'md:pl-64': sidebarOpen, 'transition-all duration-300': isLoaded}"':
        Menggeser area konten ke kanan sebesar 256px (pl-64) saat sidebar terbuka di layar desktop.
      - 'pt-16': Memberikan bantalan atas sebesar 64px agar tidak tertutup header universal yang fixed di atas.
    --}}
    <div :class="{'md:pl-64': sidebarOpen, 'transition-all duration-300': isLoaded}" class="flex-1 flex flex-col min-h-screen pt-16">

        {{-- 
          Wadah Konten Maksimum Lebar (max-w-[1600px]):
          Menjaga agar antarmuka lembar kerja matriks dan tabel tetap terpusat dan ergonomis pada layar ultra-wide.
        --}}
        <main class="flex-1 w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-6">

            {{-- 
              INJEKSI KONTEN ANAK:
              - {{ $slot ?? '' }}   : Menampung konten jika template dipanggil via Blade Component `<x-petugas-layout>`.
              - @yield('content')    : Menampung konten cadangan jika halaman menggunakan warisan konvensional `@extends('layouts.petugas')`.
            --}}
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        {{-- 
          FOOTER MINIMALIS OPERASIONAL:
          Menampilkan hak cipta tahun berjalan secara dinamis (`date('Y')`) dan label status modul operasional aktif.
        --}}
        <footer class="px-6 py-4 bg-white border-t border-slate-200 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2 mt-auto">
            <span>&copy; {{ date('Y') }} CAVA - Pusat Kendali Petugas Sarpras Kampus.</span>
            <span class="text-slate-400">Modul Operasional Aktif</span>
        </footer>
    </div>

</body>
</html>
