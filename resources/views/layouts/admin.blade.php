{{-- 
  ==============================================================================================
  MASTER LAYOUT: KONSOL TATA KELOLA SUPER ADMINISTRATOR (ADMIN LAYOUT SHELL)
  ==============================================================================================
  Berkas: resources/views/layouts/admin.blade.php
  
  PERAN & FUNGSI UTAMA:
  1. Menjadi kerangka induk (Master Template) untuk seluruh antarmuka Konsol Administrator Biro Sarpras
     (Dasbor Eksekutif, Manajemen Akun Sivitas, Master Data Fasilitas, dan Rekapitulasi Laporan).
  2. Mengimplementasikan Apple-inspired Bento UI Design System: tipografi berkelas (Inter & Outfit),
     palet warna abu netral (#F5F5F7, #1D1D1F), border halus, dan micro-animation transisi yang mulus.
  3. Mengelola state global sidebar navigasi responsif via Alpine.js (`sidebarOpen`, `isLoaded`).
  4. Mendukung dua paradigma Blade: injeksi komponen (`{{ $slot }}`) via `<x-admin-layout>`
     serta warisan template klasik (`@yield('content')`).
  ==============================================================================================
--}}

@props([
    'title' => 'CAVA - Konsol Tata Kelola & Administrasi Sistem', // Judul halaman dinamis pada tag <title>
    'active' => 'dashboard'                                       // Pengenal rute aktif untuk penanda sidebar
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    {{-- Meta Tag Standar Web & Keamanan --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Token CSRF (Cross-Site Request Forgery) untuk otentikasi request fetch/AJAX JavaScript: --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} | CAVA</title>
    <link rel="icon" type="image/webp" href="{{ asset('assets/images/logo.webp') }}">

    {{-- Tipografi Google Fonts: Inter (UI Body), Outfit (Heading Display), JetBrains Mono & Ikon Material Symbols --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    {{-- Asset Bundler Vite: Mengompilasi Tailwind CSS dan Modul JavaScript Aplikasi --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js CDN: Cadangan pustaka reaktivitas UI sisi klien --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        {{-- Mencegah kedipan elemen Alpine sebelum JavaScript selesai dimuat (Flash of Unstyled Content / FOUC) --}}
        [x-cloak] { display: none !important; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .font-sans { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .font-display { font-family: 'Outfit', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
{{-- 
    x-data: Mendeklarasikan state reaktif Alpine.js:
    - sidebarOpen: Otomatis bernilai true pada layar desktop (>= 768px), false pada mobile.
    - isLoaded: Flag transisi untuk mencegah animasi CSS berkedip saat pertama kali load.
--}}
<body x-data="{ sidebarOpen: window.innerWidth >= 768, isLoaded: false }" x-init="$nextTick(() => { isLoaded = true })" class="bg-[#F5F5F7] font-sans text-[#1D1D1F] antialiased min-h-screen flex flex-col selection:bg-[#1D1D1F] selection:text-white">

    {{-- -------------------------------------------------------------------------------------- --}}
    {{-- 1. UNIVERSAL TOP HEADER CAVA (Fixed Top Bar, Tinggi 64px)                              --}}
    {{-- -------------------------------------------------------------------------------------- --}}
    {{-- Menampilkan logo institusi, tombol toggle hamburger sidebar, breadcrumb, dan profil admin --}}
    <x-cava.header 
        :showProfile="true" 
        userName="{{ auth()->user()->name ?? 'Administrator Rektorat' }}"
        userRole="Super Admin Biro TIK"
        userIdentifier="Biro TIK & Rektorat"
        title="CAVA Admin" 
        subtitle="Campus Venue Access"
        pageName="{{ $title }}"
    />

    {{-- -------------------------------------------------------------------------------------- --}}
    {{-- 2. SIDEBAR NAVIGASI KIRI (Fixed Left Rail)                                            --}}
    {{-- -------------------------------------------------------------------------------------- --}}
    {{-- Menyediakan menu navigasi: Dasbor, Manajemen Akun Sivitas, Master Fasilitas, Ekspor Laporan --}}
    <x-cava.sidebar role="admin" :active="$active" />

    {{-- -------------------------------------------------------------------------------------- --}}
    {{-- 3. AREA KONTEN UTAMA & CONTAINER RESPONTIF                                             --}}
    {{-- -------------------------------------------------------------------------------------- --}}
    {{-- Memberikan offset padding kiri (md:pl-64) saat sidebar terbuka agar konten tidak tertutup sidebar --}}
    <div :class="{'md:pl-64': sidebarOpen, 'transition-all duration-300': isLoaded}" class="flex-1 flex flex-col min-h-screen pt-16">

        <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 py-8">
            {{-- 
                Suntikan Konten Dinamis Halaman:
                - {{ $slot ?? '' }} : Menangkap markup jika dipanggil via <x-admin-layout>
                - @yield('content') : Menangkap markup jika dipanggil via @extends('layouts.admin')
            --}}
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- 4. FOOTER ADMINISTRASI RESMI                                                       --}}
        {{-- ---------------------------------------------------------------------------------- --}}
        <footer class="px-6 py-4 bg-white border-t border-slate-200 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2 mt-auto">
            <span>&copy; {{ date('Y') }} CAVA - Konsol Super Administrator Universitas.</span>
            <span class="text-slate-400">Hak Akses Tingkat Tinggi Terenkripsi</span>
        </footer>
    </div>

</body>
</html>

