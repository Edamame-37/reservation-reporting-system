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
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col">
    {{-- 1. Universal Top Header CAVA (Tinggi 64px) --}}
    <x-cava.header 
        :showProfile="true" 
        userName="{{ auth()->user()->name ?? 'Administrator Rektorat' }}"
        userRole="Super Admin Biro TIK"
        userIdentifier="Biro TIK & Rektorat"
        title="CAVA Admin" 
        subtitle="Konsol Tata Kelola & Analitik Statuter Kampus"
    />

    {{-- 2. Sidebar Navigasi Kiri (Role: Admin) --}}
    <x-cava.sidebar role="admin" :active="$active" />

    {{-- 3. Area Konten Utama Halaman (Offset pl-64 untuk Sidebar & Header 64px) --}}
    <div class="pl-64 flex-1 flex flex-col pt-16">
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-6">
            {{-- Governance Context Bar --}}
            <section class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-[22px]">admin_panel_settings</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-sm font-bold text-slate-900">Konsol Tata Kelola Biro TIK & Rektorat</h1>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Sistem Beroperasi Normal
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Otorisasi sentral akun sivitas, inventaris ruang akademik, dan analitik statuter universitas.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="font-mono bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200 text-slate-700">Tahun Akademik 2024/2025</span>
                </div>
            </section>

            {{ $slot ?? '' }}
            @yield('content')
        </main>

        {{-- Footer Minimalis --}}
        <footer class="px-6 py-4 bg-white border-t border-slate-200 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2 mt-auto">
            <span>&copy; {{ date('Y') }} CAVA - Konsol Super Administrator Universitas.</span>
            <span class="text-slate-400">Hak Akses Tingkat Tinggi Terenkripsi</span>
        </footer>
    </div>

    {{-- Role Switcher Floating Widget (Testing Helper) --}}
    <x-cava.role-switcher activeRole="admin" />
</body>
</html>
