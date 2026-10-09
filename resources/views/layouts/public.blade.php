{{-- 
  NAMA FILE      : public.blade.php
  FUNGSIONALITAS : Kerangka Layout Utama (Master) untuk Halaman Publik / Pengunjung
  DESKRIPSI      : Layout Apple-esque Minimalist (Option 1) dengan Glassmorphism
--}}

@props([
    'title' => 'CAVA - Portal Publik Fasilitas Kampus',
    'active' => 'home'
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} | CAVA</title>
    <link rel="icon" type="image/webp" href="{{ asset('assets/images/logo.webp') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        body { font-family: 'Inter', sans-serif; }
        .glass-panel { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0, 0, 0, 0.05); }
        .hide-scroll::-webkit-scrollbar { display: none; }
    </style>
</head>
<body x-data="{ mobileMenuOpen: false }" class="bg-[#FAFAFC] text-[#1D1D1F] antialiased min-h-screen flex flex-col">

    <nav class="fixed w-full z-40 glass-panel">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center">
            <!-- Kiri: Logo -->
            <div class="flex-1 flex justify-start">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold tracking-tight text-lg hover:opacity-80 transition">
                    <img src="{{ asset('assets/images/logo.webp') }}" alt="Logo CAVA" class="w-8 h-8 object-contain">
                    <span>CAVA.</span>
                </a>
            </div>
            
            <!-- Tengah: Menu Navigasi -->
            <div class="hidden md:flex gap-8 text-sm text-[#86868B] font-medium justify-center">
                <a href="{{ route('home') }}" class="{{ $active === 'home' ? 'text-[#1D1D1F]' : 'hover:text-[#1D1D1F] transition' }}">Beranda</a>
                <a href="{{ route('public.catalog') }}" class="{{ $active === 'catalog' ? 'text-[#1D1D1F]' : 'hover:text-[#1D1D1F] transition' }}">Katalog</a>
                <a href="{{ route('public.availability') }}" class="{{ $active === 'availability' ? 'text-[#1D1D1F]' : 'hover:text-[#1D1D1F] transition' }}">Matriks Jadwal</a>
                <a href="{{ route('public.information') }}" class="{{ $active === 'information' ? 'text-[#1D1D1F]' : 'hover:text-[#1D1D1F] transition' }}">Informasi</a>
            </div>

            <!-- Kanan: Tombol Masuk -->
            <div class="flex-1 flex justify-end">
                <a href="{{ route('login') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Masuk</a>
            </div>
        </div>
    </nav>

    <div class="flex-1 flex flex-col min-h-screen pt-16">
        <main class="flex-1 w-full mx-auto">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
        <footer class="py-6 text-center text-xs text-[#86868B] mt-auto">
            &copy; {{ date('Y') }} CAVA - Biro Sarana & Prasarana Kampus.
        </footer>
    </div>
</body>
</html>
