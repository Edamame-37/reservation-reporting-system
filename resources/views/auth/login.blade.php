{{-- 
  ==============================================================================
  NAMA FILE      : login.blade.php
  MODUL          : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
  KODE FITUR     : AUTH-01 (Formulir Masuk Akun Pengguna / CAVA Single Sign-On)
  ==============================================================================
  DESKRIPSI:
  Tampilan visual formulir otentikasi login pengguna aplikasi CAVA (Campus Venue Access).
  Menyajikan input email institusi sivitas, kata sandi, opsi "Ingat Saya" (Remember Me),
  tautan pemulihan kata sandi lupa, pesan notifikasi status sesi (termasuk pemberitahuan
  bahwa akun baru masih pending verifikasi admin), serta tombol navigasi pendaftaran mandiri.

  STRUKTUR HIERARKI:
  <x-guest-layout>
    ├── <x-auth-session-status> (Pesan status sesi reset sandi)
    ├── Banner Notifikasi Sukses Pendaftaran Pending
    ├── Tautan Kembali ke Beranda Publik
    ├── Judul & Deskripsi Sapaan
    └── <form method="POST" action="login">
         ├── Input Email Sivitas + Pesan Error Validasi
         ├── Input Kata Sandi + Tautan Lupa Sandi + Pesan Error
         ├── Checkbox Ingat Saya (Remember Me)
         ├── Tombol Submit "Masuk ke Akun"
         └── Tautan Registrasi Akun Baru
  ==============================================================================
--}}

<x-guest-layout>
    {{-- Komponen Penampil Pesan Status Sesi (Misal: setelah reset password sukses) --}}
    <x-auth-session-status class="mb-6" :status="session('status')" />

    {{-- Banner Notifikasi Sukses Pendaftaran Mandiri (Dari RegisteredUserController@store) --}}
    @if (session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-start gap-3">
            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
            <div>
                <h3 class="text-[14px] font-semibold text-emerald-800">Informasi</h3>
                <p class="text-[13px] text-emerald-600 mt-1">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- Tautan Navigasi Kembali ke Landing Page Beranda Publik --}}
    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-[14px] text-[#86868B] hover:text-[#1D1D1F] font-medium transition-colors mb-10 group">
        <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
        <span>Kembali ke Beranda</span>
    </a>

    {{-- Header Judul & Slogan Form Login --}}
    <div class="mb-10">
        <h1 class="text-[32px] md:text-[36px] font-extrabold tracking-tight text-[#1D1D1F] mb-2 leading-tight">Welcome Back</h1>
        <p class="text-[15px] text-[#86868B] leading-relaxed max-w-sm">Akses sistem reservasi dan laporkan kendala fasilitas kampus dengan mudah.</p>
    </div>

    {{-- 
      FORMULIR LOGIN:
      - Method : POST
      - Action : route('login') ➔ Ditangani oleh AuthenticatedSessionController@store & LoginRequest
      - @csrf  : Token proteksi Cross-Site Request Forgery wajib Laravel
    --}}
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- 1. Input Alamat Email Sivitas --}}
        <div>
            <label for="email" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">Email Sivitas</label>
            {{-- Menggunakan old('email') untuk mempertahankan input pengguna jika terjadi kegagalan login --}}
            <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autofocus autocomplete="username" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]" placeholder="nama@kampus.ac.id">
            {{-- Menampilkan pesan error kegagalan login atau status akun pending/rejected --}}
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        {{-- 2. Input Kata Sandi --}}
        <div>
            <div class="flex justify-between items-center mb-2">
                <label for="password" class="block text-[14px] font-semibold text-[#1D1D1F]">Kata Sandi</label>
                {{-- Tautan Menuju Formulir Pemulihan Sandi Lupa --}}
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-[13px] text-[#1D1D1F] font-semibold hover:underline">Lupa Sandi?</a>
                @endif
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]" placeholder="Minimal 8 karakter">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        {{-- 3. Checkbox Ingat Saya (Remember Me Cookie) --}}
        <div class="flex items-center gap-3 pt-2">
            <input id="remember_me" type="checkbox" name="remember" class="rounded-[4px] border-gray-300 text-[#1D1D1F] focus:ring-[#1D1D1F]/20 cursor-pointer w-4 h-4">
            <label for="remember_me" class="text-[14px] font-medium text-[#86868B] cursor-pointer select-none">Ingat saya di perangkat ini</label>
        </div>

        {{-- Tombol Aksi Masuk Akun --}}
        <button type="submit" class="w-full bg-[#1D1D1F] text-white px-8 py-4 rounded-2xl font-semibold hover:bg-gray-800 transition shadow-[0_8px_20px_rgb(0,0,0,0.08)] mt-6 text-[15px] cursor-pointer">
            Masuk ke Akun
        </button>
        
        {{-- Tautan Menuju Formulir Registrasi Sivitas Baru --}}
        <div class="mt-10 pt-6 border-t border-gray-100/50 flex items-center justify-center gap-1.5 text-[14px] text-[#86868B]">
            <span>Belum memiliki akun?</span>
            <a href="{{ route('register') }}" class="text-[#1D1D1F] font-semibold hover:underline">Daftar sekarang</a>
        </div>
    </form>
</x-guest-layout>