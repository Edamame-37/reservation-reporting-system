{{-- 
  NAMA FILE      : petugas.blade.php
  FUNGSIONALITAS : Kerangka Layout Utama (Master) untuk Halaman Petugas Sarpras
  DESKRIPSI      : Menampilkan dasbor operasional petugas, banner konteks zona sarpras aktif, sidebar antrean verifikasi & approval, dan manajemen tiket.
  CARA KERJA     : Bertindak sebagai master layout. Halaman petugas menggunakan layout ini via <x-petugas-layout> atau @extends('layouts.petugas').
--}}

@props([
    'title' => 'CAVA - Pusat Operasional & Sarpras Kampus',
    'active' => 'dashboard'
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} | CAVA Officer Workspace</title>

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
        userName="{{ auth()->user()->name ?? 'Bambang Setyawan' }}"
        userRole="Petugas Sarpras Zona A"
        userIdentifier="NIP. {{ auth()->user()->nip ?? '197804122005011002' }}"
        title="CAVA - Pusat Operasional Sarpras" 
        subtitle="Sistem Verifikasi Reservasi & Pemeliharaan Fasilitas"
    />

    {{-- 2. Sidebar Navigasi Kiri (Role: Petugas) --}}
    <x-cava.sidebar role="petugas" :active="$active" />

    {{-- 3. Area Konten Utama Halaman (Offset pl-64 untuk Sidebar & Header 100px) --}}
    <div class="pl-64 flex-1 flex flex-col pt-[100px]">
        {{-- Role Switcher Tab (Untuk Pengujian & Navigasi Peran) --}}
        <x-cava.role-switcher activeRole="petugas" />

        <main class="flex-1 bg-surface px-margin-lg py-margin-md flex flex-col gap-space-lg">
            {{-- SUB-HEADER CONTEXT / OFFICER INFO BANNER --}}
            <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg">
                <div class="flex flex-wrap items-center justify-between gap-space-md">
                    <div class="flex items-center gap-space-lg">
                        <div class="w-12 h-12 rounded-xl bg-primary flex items-center justify-center text-on-primary shadow-sm">
                            <span class="material-symbols-outlined text-[28px]">shield_person</span>
                        </div>
                        <div class="flex flex-col">
                            <div class="flex items-center gap-space-sm">
                                <span class="font-headline-md text-headline-md text-on-surface tracking-tight">CAVA - Pusat Operasional & Sarpras Kampus</span>
                                <span class="px-space-sm py-0.5 rounded-full font-label-sm text-label-sm bg-secondary-fixed text-on-secondary-fixed font-semibold flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse"></span>
                                    ZONA-A AKTIF
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-space-md gap-y-1 mt-1 text-on-surface-variant font-body-sm text-body-sm">
                                <span class="flex items-center gap-1 font-label-md text-label-md text-primary font-semibold">
                                    <span class="material-symbols-outlined text-[16px]">badge</span>
                                    Pak Bambang S. (Petugas Sarpras Zona Gedung A)
                                </span>
                                <span>•</span>
                                <span class="font-data-mono text-data-mono bg-surface-container px-space-sm py-0.5 rounded text-on-surface">
                                    NIP. 197804122005011002
                                </span>
                                <span>•</span>
                                <span class="flex items-center gap-1 text-secondary font-medium">
                                    <span class="material-symbols-outlined text-[16px]">schedule</span>
                                    Shift Pagi: 07.00 - 15.00 WIB
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-space-md">
                        <div class="hidden xl:flex flex-col items-end text-right">
                            <span class="font-label-sm text-label-sm uppercase text-on-surface-variant font-data-mono text-data-mono">Status Sistem Otomasi</span>
                            <span class="font-label-md text-label-md text-secondary flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-secondary"></span>
                                Pendeteksi Bentrok: Realtime Active
                            </span>
                        </div>
                        <div class="h-8 w-[1px] bg-surface-container-high hidden xl:block"></div>
                        <button class="px-space-md py-space-sm rounded-lg bg-surface-container-high text-on-surface hover:bg-surface-container-highest transition-colors font-label-md text-label-md flex items-center gap-space-xs" type="button" onclick="location.reload()">
                            <span class="material-symbols-outlined text-[16px]">refresh</span>
                            Sinkron Data
                        </button>
                    </div>
                </div>
            </section>

            {{ $slot ?? '' }}
            @yield('content')
        </main>

        {{-- Footer Minimalis --}}
        <footer class="px-margin-lg py-space-md bg-surface-container-lowest border-t border-outline-variant text-center font-label-sm text-label-sm text-on-surface-variant flex items-center justify-between">
            <span>&copy; {{ date('Y') }} CAVA - Pusat Kendali Petugas Sarpras. Modul UR08 • UR09 • UR10 • UR11 • UR12.</span>
            <span class="font-data-mono text-data-mono">AUTOMATED CONCURRENCY ENGINE READY</span>
        </footer>
    </div>
</body>
</html>
