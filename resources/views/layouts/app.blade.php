{{-- 
  NAMA FILE      : app.blade.php
  FUNGSIONALITAS : Kerangka Layout Utama (Master) untuk Portal Pengguna (Mahasiswa / Dosen)
  DESKRIPSI      : Menyediakan bingkai antarmuka pengguna terotentikasi, profil mahasiswa, navigasi modul reservasi dan pelaporan mandiri.
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
<body class="bg-surface font-sans text-body-md text-on-surface antialiased min-h-screen flex flex-col">
    {{-- 1. Universal Top Header CAVA dengan Profil Pengguna --}}
    <x-cava.header 
        :showProfile="true" 
        userName="{{ auth()->user()->name ?? 'Dimas Pratama' }}"
        userRole="Mahasiswa TI"
        userIdentifier="NIM: {{ auth()->user()->nim ?? '2110512044' }}"
        title="CAVA Student Portal" 
        subtitle="Sistem Reservasi Ruang & Pelaporan Mandiri"
    />

    {{-- 2. Sidebar Navigasi Kiri (Role: User) --}}
    <x-cava.sidebar role="user" :active="$active" />

    {{-- 3. Area Konten Utama Halaman (Offset pl-64 untuk Sidebar & Header 100px) --}}
    <div class="pl-64 flex-1 flex flex-col pt-[100px]">
        {{-- Role Switcher Tab (Untuk Pengujian & Navigasi Peran) --}}
        <x-cava.role-switcher activeRole="user" />

        <main class="flex-1 bg-surface px-margin-lg py-margin-md">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        {{-- Footer Minimalis --}}
        <footer class="px-margin-lg py-space-md bg-surface-container-lowest border-t border-outline-variant text-center font-label-sm text-label-sm text-on-surface-variant flex items-center justify-between">
            <span>&copy; {{ date('Y') }} CAVA - Biro Sarana & Prasarana Kampus. Terintegrasi SIM-SARPRAS.</span>
            <span class="font-data-mono text-data-mono">UR03 • UR04 • UR05 • UR06 • UR07</span>
        </footer>
    </div>
</body>
</html>
