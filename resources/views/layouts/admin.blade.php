{{-- 
  NAMA FILE      : admin.blade.php
  FUNGSIONALITAS : Kerangka Layout Utama (Master) untuk Konsol Tata Kelola Super Admin
  DESKRIPSI      : Menampilkan masthead konsol tata kelola sentral, profil Super Admin TIK/Rektorat, sidebar master data, user management, dan ekspor laporan.
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

    <title>{{ $title }} | CAVA Administration Console</title>

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
    {{-- 1. Universal Top Header CAVA --}}
    <x-cava.header 
        :showProfile="true" 
        userName="{{ auth()->user()->name ?? 'Administrator Rektorat' }}"
        userRole="Super Admin Biro TIK"
        userIdentifier="TIK-ROOT#SYSADMIN-9901"
        title="CAVA - Konsol Tata Kelola Admin" 
        subtitle="Pengendalian Otorisasi Sentral & Analitik Kampus"
    />

    {{-- 2. Sidebar Navigasi Kiri (Role: Admin) --}}
    <x-cava.sidebar role="admin" :active="$active" />

    {{-- 3. Area Konten Utama Halaman (Offset pl-64 untuk Sidebar & Header 100px) --}}
    <div class="pl-64 flex-1 flex flex-col pt-[100px]">
        {{-- Role Switcher Tab (Untuk Pengujian & Navigasi Peran) --}}
        <x-cava.role-switcher activeRole="admin" />

        <main class="flex-1 bg-surface px-margin-lg py-margin-md flex flex-col gap-space-xl">
            {{-- GOVERNANCE CONTEXT BAR --}}
            <section class="flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md p-space-lg rounded-xl bg-surface-container-lowest shadow-sm">
                <div class="flex flex-col gap-space-xs">
                    <div class="flex items-center gap-space-sm flex-wrap">
                        <span class="font-data-mono text-data-mono uppercase tracking-wider text-primary px-space-sm py-0.5 rounded bg-surface-container-highest font-bold">NODE-ROOT: REKTORAT-TIK-01</span>
                        <span class="flex items-center gap-1 font-label-sm text-label-sm text-secondary bg-surface-container-low px-space-sm py-0.5 rounded font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>Sistem Online & Optimal
                        </span>
                        <span class="font-data-mono text-data-mono text-on-surface-variant text-[11px]">UPTIME: 99.98%</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-primary tracking-tight">CAVA - Konsol Tata Kelola & Administrasi Sistem</h1>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Pengendalian Otorisasi Sentral, Manajemen Basis Data Inventaris Ruang, dan Analitik Pelaporan Statuter Kampus</p>
                </div>
                <div class="flex items-center gap-space-md bg-surface-container-low p-space-sm rounded-xl">
                    <div class="w-10 h-10 rounded-lg bg-primary-container text-on-primary flex items-center justify-center shadow-sm">
                        <span class="material-symbols-outlined text-[22px]">admin_panel_settings</span>
                    </div>
                    <div class="flex flex-col text-left pr-space-md">
                        <span class="font-label-lg text-label-lg text-primary leading-tight font-semibold">Super Admin (Biro TIK)</span>
                        <span class="font-data-mono text-data-mono text-on-surface-variant text-[11px]">SYSADMIN-9901</span>
                    </div>
                </div>
            </section>

            {{ $slot ?? '' }}
            @yield('content')
        </main>

        {{-- Footer Minimalis --}}
        <footer class="px-margin-lg py-space-md bg-surface-container-lowest border-t border-outline-variant text-center font-label-sm text-label-sm text-on-surface-variant flex items-center justify-between">
            <span>&copy; {{ date('Y') }} CAVA - Konsol Pusat Super Administrator. Modul UR13 • UR14 • UR15 • UR16 • UR17.</span>
            <span class="font-data-mono text-data-mono">AUDIT LOG ENCRYPTED</span>
        </footer>
    </div>
</body>
</html>
