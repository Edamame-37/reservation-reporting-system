{{-- 
  ==============================================================================
  NAMA FILE      : header.blade.php
  MODUL          : Modul 4 - Portal Publik: Beranda, Katalog Fasilitas, Filter & Matriks Ketersediaan
  KODE FITUR     : PUB-01 (Komponen Navigasi Header Universal CAVA)
  ==============================================================================
  DESKRIPSI:
  Komponen Blade navigasi atas (Universal Top Masthead) berukuran tetap 64px (h-16) dengan Glassmorphism.
  Berfungsi adaptif untuk 2 konteks pengguna:
  1. Portal Publik ($role === 'public'):
     Menampilkan logo CAVA, menu navigasi desktop terpusat (Beranda, Katalog, Matriks, Informasi),
     menu drawer lipat untuk perangkat seluler, serta tombol login SSO.
  2. Panel Internal ($role !== 'public', yaitu admin, petugas, atau sivitas):
     Menyesuaikan margin kiri secara responsif terhadap status sidebar (md:left-64),
     menampilkan tombol toggle sidebar (hamburger), judul halaman aktif ($pageName),
     serta kartu profil pengguna aktif (nama, role, NIP/NIM, avatar) yang terhubung ke profil.

  CARA PENGGUNAAN:
  <x-cava.header role="public" active="catalog" :showProfile="false" />
  <x-cava.header role="user" pageName="Dashboard Pengguna" :userName="auth()->user()->name" />
  ==============================================================================
--}}

{{-- Inisialisasi Properti Komponen Blade (@props) dengan Nilai Default --}}
@props([
    'showProfile'    => true,                          // Menentukan apakah menu profil pengguna ditampilkan
    'userName'       => 'Dr. Ir. Hendra Prasetyo',    // Nama pengguna untuk kartu profil
    'userRole'       => 'Petugas Sarpras Zona A',      // Jabatan / peran pengguna
    'userIdentifier' => 'NIP 198402112009121003',     // Nomor identitas resmi (NIM / NIP)
    'title'          => 'CAVA',                       // Akronim sistem
    'subtitle'       => 'Campus Venue Access',        // Nama panjang sistem
    'pageName'       => '',                           // Judul halaman aktif untuk panel internal
    'role'           => 'user',                       // Peran tampilan: 'public', 'user', 'petugas', atau 'admin'
    'active'         => ''                            // Kunci menu yang sedang aktif ('home', 'catalog', 'availability', 'informasi')
])

{{-- 
  BILAH HEADER TETAP (Fixed Header 64px / h-16):
  - Menggunakan Tailwind CSS fixed top-0 right-0 z-40.
  - Alpine.js :class binding: Jika sidebar panel internal terbuka di layar desktop (md:),
    lebar header bergeser ke kiri sebesar 64 unit (md:left-64) agar tidak menutupi sidebar.
--}}
<header :class="{'md:left-64': sidebarOpen && '{{ $role }}' !== 'public', 'left-0': !sidebarOpen || '{{ $role }}' === 'public'}" class="fixed top-0 right-0 z-40 bg-white/95 backdrop-blur-sm border-b border-slate-200/80 h-16 transition-all duration-300">
    <div class="h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between">
        
        {{-- SISI KIRI: BRAND IDENTITAS & TOMBOL MENU --}}
        <div class="flex items-center gap-2 sm:gap-3">
            @if($role !== 'public')
                {{-- Tombol Toggle Sidebar untuk Pengguna Internal --}}
                <button type="button" @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900 rounded-lg p-1 transition-colors cursor-pointer" title="Buka/Tutup Sidebar">
                    <span class="material-symbols-outlined text-[24px]">menu</span>
                </button>
                @if($pageName)
                    <div class="hidden sm:block ml-1 sm:ml-2 border-l border-slate-200 pl-3 sm:pl-4 py-1">
                        <h1 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-800">{!! $pageName !!}</h1>
                    </div>
                @endif
            @else
                {{-- Tombol Hamburger Khusus Tampilan Ponsel (Mobile) pada Portal Publik --}}
                <button type="button" @click="sidebarOpen = !sidebarOpen" class="md:hidden text-slate-500 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900 rounded-lg p-1 transition-colors cursor-pointer" title="Menu Navigasi">
                    <span class="material-symbols-outlined text-[24px]">menu</span>
                </button>
                
                {{-- Logo & Identitas CAVA untuk Pengunjung Publik --}}
                <a href="{{ url('/') }}" class="flex items-center gap-3 group ml-1 sm:ml-2">
                    <img src="{{ asset('assets/images/logo.webp') }}" alt="CAVA Logo" class="h-9 w-auto">
                    <div class="flex flex-col hidden sm:flex">
                        <span class="font-bold text-slate-900 text-sm sm:text-base leading-tight tracking-tight group-hover:text-blue-900 transition-colors">{{ $title }}</span>
                        <span class="text-[10px] sm:text-xs text-slate-500 font-medium">{{ $subtitle }}</span>
                    </div>
                </a>
            @endif
        </div>

        {{-- SISI TENGAH: TAUTAN MENU NAVIGASI PUBLIK (Desktop) --}}
        @if($role === 'public')
        <nav class="hidden md:flex items-center gap-1 absolute left-1/2 transform -translate-x-1/2">
            <a href="{{ url('/') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 {{ $active == 'home' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Beranda</a>
            <a href="{{ url('/public/catalog') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 {{ $active == 'catalog' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Katalog Fasilitas</a>
            <a href="{{ url('/public/availability') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 {{ $active == 'availability' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Matriks Jadwal</a>
            <a href="{{ route('public.information') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 {{ $active == 'informasi' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Pusat Informasi</a>
        </nav>
        @endif

        {{-- SISI KANAN: STATUS AKUN, NOTIFIKASI & PROFIL / TOMBOL LOGIN --}}
        <div class="flex items-center gap-3 sm:gap-4">
            @if($showProfile)
                {{-- Kartu Profil Pengguna Terautentikasi (Klik menuju Pengaturan Profil) --}}
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 pl-2 sm:pl-3 py-1 pr-1 border-l border-slate-200 hover:bg-slate-50 rounded-xl transition cursor-pointer group" title="Pengaturan Profil Akun">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-sm font-semibold text-slate-800 leading-tight group-hover:text-blue-900 transition-colors">{{ $userName }}</span>
                        <span class="text-[11px] text-slate-500 font-mono">{{ $userIdentifier }}</span>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-sm shadow-sm group-hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined text-[18px]">person</span>
                    </div>
                </a>
            @else
                {{-- Tombol Masuk SSO Kampus untuk Pengunjung Publik --}}
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-slate-900 text-white text-xs sm:text-sm font-medium hover:bg-slate-800 transition shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">login</span>
                        <span>Masuk SSO</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- MENU DROPDOWN KHUSUS TAMPILAN MOBILE (Ponsel) --}}
    @if($role === 'public')
    <div x-show="sidebarOpen" x-transition.opacity style="display: none;" class="md:hidden absolute top-16 left-0 right-0 bg-white border-b border-slate-200/80 shadow-lg px-4 py-4 flex flex-col gap-2 z-40">
        <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ $active == 'home' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            <span class="material-symbols-outlined text-[18px]">home</span> Beranda
        </a>
        <a href="{{ url('/public/catalog') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ $active == 'catalog' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            <span class="material-symbols-outlined text-[18px]">search</span> Katalog Fasilitas
        </a>
        <a href="{{ url('/public/availability') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ $active == 'availability' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            <span class="material-symbols-outlined text-[18px]">calendar_month</span> Matriks Jadwal
        </a>
        <a href="{{ route('public.information') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ $active == 'informasi' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            <span class="material-symbols-outlined text-[18px]">info</span> Pusat Informasi
        </a>
    </div>
    @endif
</header>
