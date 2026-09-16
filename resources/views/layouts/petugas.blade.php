{{-- 
  NAMA FILE      : petugas.blade.php
  FUNGSIONALITAS : Kerangka Layout Utama (Master) untuk Halaman Petugas Sarpras
  DESKRIPSI      : Menampilkan bingkai kerja operasional petugas sarpras, konteks shift piket, deteksi bentrok real-time, dan sidebar antrean verifikasi dengan desain Modern Campus Minimalist.
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
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col">
    {{-- 1. Universal Top Header CAVA (Tinggi 64px) --}}
    <x-cava.header 
        :showProfile="true" 
        userName="{{ auth()->user()->name ?? 'Bambang Setyawan' }}"
        userRole="Petugas Sarpras Zona A"
        userIdentifier="NIP. {{ auth()->user()->nip ?? '197804122005011002' }}"
        title="CAVA Operasional" 
        subtitle="Sistem Verifikasi & Pemeliharaan Sarpras"
    />

    {{-- 2. Sidebar Navigasi Kiri (Role: Petugas) --}}
    <x-cava.sidebar role="petugas" :active="$active" />

    {{-- 3. Area Konten Utama Halaman (Offset pl-64 untuk Sidebar & Header 64px) --}}
    <div class="pl-64 flex-1 flex flex-col pt-16">
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-6">
            {{-- Context Officer Info Bar (Sleek Single Strip) --}}
            <section class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-[22px]">shield_person</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-slate-900">Bambang Setyawan</span>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Zona Gedung A Aktif
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
                            <span>NIP. 197804122005011002</span>
                            <span>•</span>
                            <span class="text-slate-600 font-medium">Shift Pagi (07.00 - 15.00 WIB)</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="hidden md:flex items-center gap-1.5 text-xs text-emerald-700 font-medium bg-emerald-50/80 px-2.5 py-1 rounded-lg border border-emerald-200/60">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Validasi Anti-Bentrok: Aktif</span>
                    </span>
                    <button type="button" onclick="location.reload()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-xs font-medium text-slate-700 hover:bg-slate-100 transition shadow-xs">
                        <span class="material-symbols-outlined text-[16px]">refresh</span>
                        <span>Sinkron Data</span>
                    </button>
                </div>
            </section>

            {{ $slot ?? '' }}
            @yield('content')
        </main>

        {{-- Footer Minimalis --}}
        <footer class="px-6 py-4 bg-white border-t border-slate-200 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2 mt-auto">
            <span>&copy; {{ date('Y') }} CAVA - Pusat Kendali Petugas Sarpras Kampus.</span>
            <span class="text-slate-400">Modul Operasional Aktif</span>
        </footer>
    </div>

    {{-- Role Switcher Floating Widget (Testing Helper) --}}
    <x-cava.role-switcher activeRole="petugas" />
</body>
</html>
