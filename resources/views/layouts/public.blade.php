{{-- 
  NAMA FILE      : public.blade.php
  FUNGSIONALITAS : Kerangka Layout Utama (Master) untuk Halaman Publik / Pengunjung
  DESKRIPSI      : Berisi struktur HTML dasar, Google Fonts (Inter & Material Symbols), Header CAVA, Sidebar Navigasi Publik, dan slot konten utama.
  CARA KERJA     : Bertindak sebagai master layout. Halaman publik menggunakan layout ini via <x-public-layout> atau @extends('layouts.public').
--}}

@props([
    'title' => 'CAVA - Portal Publik Fasilitas Kampus',
    'active' => 'home'
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} | CAVA - Campus Venue Access</title>

    {{-- Tipografi Google Fonts & Ikon Material Symbols --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    {{-- Asset Vite (Tailwind CSS & JavaScript) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js CDN Backup (jika belum dibundle via Vite) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-surface font-sans text-body-md text-on-surface antialiased min-h-screen flex flex-col">
    {{-- 1. Universal Top Header CAVA --}}
    <x-cava.header 
        :showProfile="false" 
        title="CAVA - Campus Venue Access" 
        subtitle="Sistem Otomasi Reservasi Fasilitas Terpadu"
    />

    {{-- 2. Sidebar Navigasi Kiri (Role: Publik) --}}
    <x-cava.sidebar role="public" :active="$active" />

    {{-- 3. Area Konten Utama Halaman (Offset pl-64 untuk Sidebar & Header 100px) --}}
    <div class="pl-64 flex-1 flex flex-col pt-[100px]">
        {{-- Role Switcher Tab (Untuk Pengujian & Navigasi Peran) --}}
        <x-cava.role-switcher activeRole="public" />

        <main class="flex-1 bg-surface px-margin-lg py-margin-md">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        {{-- Footer Minimalis --}}
        <footer class="px-margin-lg py-space-md bg-surface-container-lowest border-t border-outline-variant text-center font-label-sm text-label-sm text-on-surface-variant flex items-center justify-between">
            <span>&copy; {{ date('Y') }} CAVA - Biro Sarana & Prasarana Kampus. Hak Cipta Dilindungi.</span>
            <span class="font-data-mono text-data-mono">UR-01 PRIVACY COMPLIANT</span>
        </footer>
    </div>
</body>
</html>
