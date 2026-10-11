{{-- 
  NAMA FILE      : admin.blade.php
  FUNGSIONALITAS : Kerangka Layout Utama (Master) untuk Konsol Tata Kelola Super Admin
  DESKRIPSI      : Menampilkan masthead konsol tata kelola sentral, profil Administrator TIK/Rektorat, sidebar master data, user management, dan ekspor laporan dengan desain Modern Campus Minimalist.
  CARA KERJA     : Bertindak sebagai master layout. Halaman admin menggunakan layout ini via <x-admin-layout> atau @extends('layouts.admin').
--}}

@props([
    'title' => 'CAVA - Konsol Tata Kelola & Administrasi Sistem',
    'active' => 'dashboard'
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} | CAVA</title>
    <link rel="icon" type="image/webp" href="{{ asset('assets/images/logo.webp') }}">

    {{-- Tipografi Google Fonts: Inter, Outfit (Display), JetBrains Mono & Ikon Material Symbols --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    {{-- Asset Vite (Tailwind CSS & JavaScript) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js CDN Backup --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .font-sans { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .font-display { font-family: 'Outfit', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body x-data="{ sidebarOpen: window.innerWidth >= 768, isLoaded: false }" x-init="$nextTick(() => { isLoaded = true })" class="bg-[#F5F5F7] font-sans text-[#1D1D1F] antialiased min-h-screen flex flex-col selection:bg-[#1D1D1F] selection:text-white">
    {{-- 1. Universal Top Header CAVA (Tinggi 64px) --}}
    <x-cava.header 
        :showProfile="true" 
        userName="{{ auth()->user()->name ?? 'Administrator Rektorat' }}"
        userRole="Super Admin Biro TIK"
        userIdentifier="Biro TIK & Rektorat"
        title="CAVA Admin" 
        subtitle="Campus Venue Access"
        pageName="{{ $title }}"
    />

    {{-- 2. Sidebar Navigasi Kiri (Role: Admin) --}}
    <x-cava.sidebar role="admin" :active="$active" />

    {{-- 3. Area Konten Utama Halaman (Offset md:pl-64 untuk Sidebar) --}}
    <div :class="{'md:pl-64': sidebarOpen, 'transition-all duration-300': isLoaded}" class="flex-1 flex flex-col min-h-screen pt-16">

        <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 py-8">
            {{-- 
              Area Konten Utama:
              Diselaraskan 100% dengan Opsi 1 (max-w-7xl mx-auto px-4 sm:px-6 py-8)
            --}}

            {{ $slot ?? '' }}
            @yield('content')
        </main>

        {{-- Footer Minimalis --}}
        <footer class="px-6 py-4 bg-white border-t border-slate-200 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2 mt-auto">
            <span>&copy; {{ date('Y') }} CAVA - Konsol Super Administrator Universitas.</span>
            <span class="text-slate-400">Hak Akses Tingkat Tinggi Terenkripsi</span>
        </footer>
    </div>


</body>
</html>
