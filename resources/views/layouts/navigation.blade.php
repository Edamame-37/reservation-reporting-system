{{-- 
  ========================================================================================
  SISTEM INFORMASI MANAJEMEN FASILITAS & SARANA PRASARANA KAMPUS (CAVA)
  ========================================================================================
  NAMA FILE       : navigation.blade.php
  LOKASI FILE     : resources/views/layouts/navigation.blade.php
  DESKRIPSI       : Bilah Navigasi Utama Aplikasi (Primary Header Navigation Bar)
  ARSITEKTUR      : Blade View Component (Laravel Breeze Core Navigation)
  AUTHOR          : Tim Pengembang CAVA & Sivitas Akademika
  
  FITUR UTAMA:
  1. Kontainer Navbar Responsif: Mendukung tata letak layar desktop horizontal (flexbox) 
     dan drawer menu vertikal untuk perangkat mobile (smartphones/tablets).
  2. State Reaktif Alpine.js: Mengatur visibilitas menu drawer mobile melalui variabel 
     lokal `open` tanpa membebani browser dengan JavaScript eksternal yang berat.
  3. Indikator Rute Aktif: Menggunakan helper Laravel `request()->routeIs()` untuk 
     menghighlight navigasi menu yang sedang aktif dikunjungi pengguna.
  4. Slot Dropdown Profil Pengguna: Menampilkan nama akun sivitas yang sedang login 
     (Auth::user()->name), tautan manajemen profil, serta form logout terproteksi CSRF.
  5. Hamburger Button Interaktif: Animasi transisi visual ikon tombol hamburger (garis tiga) 
     menjadi ikon silang (tutup menu/X) saat drawer mobile terbuka.
  ========================================================================================
--}}

{{-- 
  Tag Pembungkus Navigasi Utama:
  - `x-data="{ open: false }"`: Menginisialisasi komponen reaktif Alpine.js dengan state lokal `open` (default: false/tertutup).
  - `class="..."`: Memberikan warna latar putih (mode terang) atau abu-abu gelap (mode gelap), 
    serta garis pembatas bawah (border-b) untuk memisahkan bilah navigasi dengan konten halaman.
--}}
<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Kontainer Pembatas Lebar Navigasi Desktop & Mobile (Primary Navigation Container) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            {{-- Bagian Kiri: Logo Brand Sistem & Tautan Menu Navigasi Desktop --}}
            <div class="flex">
                <!-- Identitas Brand & Logo Sistem CAVA -->
                <div class="shrink-0 flex items-center">
                    {{-- 
                      Tautan Logo Mengarah ke Dasbor Utama:
                      Menggunakan helper route('dashboard') untuk mengarahkan pengguna kembali ke ringkasan sistem.
                    --}}
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Tautan Menu Navigasi Desktop (Disembunyikan pada layar mobile dengan kelas 'hidden sm:flex') -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    {{-- 
                      Komponen Nav-Link:
                      - `:href="route('dashboard')"`: Mengikat atribut href secara dinamis ke URL rute 'dashboard'.
                      - `:active="request()->routeIs('dashboard')"`: Memeriksa apakah nama rute saat ini cocok dengan 'dashboard', 
                        memberikan gaya visual garis bawah aktif (active border indicator).
                      - `{{ __('Dashboard') }}`: String multibahasa Laravel localization untuk label dasbor.
                    --}}
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Bagian Kanan: Menu Dropdown Pengaturan & Akun Pengguna (Desktop Only) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                {{-- 
                  Komponen Dropdown:
                  - `align="right"`: Menyelaraskan kotak menu dropdown ke sisi kanan pemicu tombol.
                  - `width="48"`: Lebar kontainer dropdown sebesar 12rem (w-48).
                --}}
                <x-dropdown align="right" width="48">
                    {{-- Slot Trigger: Elemen tombol yang memicu pembukaan menu dropdown saat diklik --}}
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            {{-- Menampilkan Nama Pengguna yang Sedang Terotentikasi Sesi Saat Ini --}}
                            <div>{{ Auth::user()->name }}</div>

                            {{-- Ikon Caret / Chevron Bawah (SVG) --}}
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    {{-- Slot Content: Isi menu popover dropdown saat kondisi variabel `open` bernilai true --}}
                    <x-slot name="content">
                        {{-- Tautan Halaman Pengaturan & Pengeditan Profil Akun --}}
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Aksi Logout / Keluar Sesi Aplikasi -->
                        <form method="POST" action="{{ route('logout') }}">
                            {{-- @csrf: Token perlindungan terhadap Cross-Site Request Forgery --}}
                            @csrf

                            {{-- 
                              Tautan Logout Berbasis JavaScript:
                              - `event.preventDefault()`: Mencegah navigasi tautan link GET biasa secara default.
                              - `this.closest('form').submit()`: Mengirim form terdekat secara method POST untuk mematikan sesi otentikasi secara aman.
                            --}}
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Tombol Hamburger Responsif Layar Mobile (Ditampilkan hanya pada layar < 640px / sm:hidden) -->
            <div class="-me-2 flex items-center sm:hidden">
                {{-- 
                  Tombol Pengalih Menu Mobile:
                  - `@click="open = ! open"`: Event listener Alpine.js untuk membalik nilai boolean `open` (toggle).
                  - Menampilkan efek hover dan fokus dengan palet warna abu-abu responsif.
                --}}
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        {{-- Garis Ikon Hamburger (3 Baris): Disembunyikan saat drawer terbuka (`hidden: open`) --}}
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        {{-- Garis Ikon Silang / Close (X): Ditampilkan hanya saat drawer terbuka (`inline-flex: open`) --}}
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Drawer Navigasi Responsif untuk Layar Mobile (Responsive Navigation Menu) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        {{-- Tautan Menu Utama Versi Mobile --}}
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Detail Informasi Pengguna & Aksi Akun Versi Mobile -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            {{-- Menampilkan Nama Lengkap dan Alamat Email Sivitas Terdaftar --}}
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                {{-- Tautan Edit Profil Versi Mobile --}}
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Form Logout Sesi Versi Mobile -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    {{-- Menjalankan Submit Form POST Logout pada Tampilan Mobile --}}
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
