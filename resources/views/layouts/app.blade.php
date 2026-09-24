{{-- 
  NAMA FILE      : app.blade.php
  FUNGSIONALITAS : Kerangka Layout Utama (Master) untuk Portal Pengguna (Mahasiswa / Dosen)
  DESKRIPSI      : Menyediakan bingkai antarmuka pengguna terotentikasi, profil mahasiswa, navigasi modul reservasi dan pelaporan mandiri dengan desain Modern Campus Minimalist.
  CARA KERJA     : Bertindak sebagai layout komponen. Halaman mahasiswa/dosen menggunakan layout ini via <x-app-layout> atau @extends('layouts.app').
--}}

@props([
    'title' => 'CAVA - Portal Mahasiswa & Dosen',
    'active' => 'dashboard'
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} | CAVA Student & Faculty Portal</title>

    {{-- Tipografi Google Fonts & Ikon Material Symbols --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    {{-- Asset Vite (Tailwind CSS & JavaScript) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js CDN Backup --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col">
    {{-- 1. Universal Top Header CAVA dengan Profil Pengguna (Tinggi 64px) --}}
    <x-cava.header 
        :showProfile="true" 
        userName="{{ auth()->user()->name ?? 'Dimas Pratama' }}"
        userRole="Mahasiswa TI"
        userIdentifier="NIM: {{ auth()->user()->nim ?? '2110512044' }}"
        title="CAVA Portal" 
        subtitle="Sistem Reservasi Ruang & Pelaporan Mandiri"
    />

    {{-- 2. Sidebar Navigasi Kiri (Role: User) --}}
    <x-cava.sidebar role="user" :active="$active" />

    {{-- 3. Area Konten Utama Halaman (Offset pl-64 untuk Sidebar & Header 64px) --}}
    <div class="pl-64 flex-1 flex flex-col pt-16">
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        {{-- Footer Minimalis --}}
        <footer class="px-6 py-4 bg-white border-t border-slate-200 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2 mt-auto">
            <span>&copy; {{ date('Y') }} CAVA - Biro Sarana & Prasarana Kampus. Terintegrasi SIM-SARPRAS.</span>
            <span class="text-slate-400">Portal Pengguna Aktif</span>
        </footer>
    </div>


</body>
</html>
