{{-- 
  ==============================================================================
  NAMA FILE      : forgot-password.blade.php
  MODUL          : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
  KODE FITUR     : AUTH-01 (Formulir Permohonan Pemulihan Kata Sandi Akun)
  ==============================================================================
  DESKRIPSI:
  Tampilan visual formulir permohonan tautan pemulihan kata sandi (Forgot Password).
  Menyajikan field input email kampus terdaftar dan tombol untuk memicu pengiriman
  tautan reset sandi bertoken aman melalui surat elektronik.

  STRUKTUR HIERARKI:
  <x-guest-layout>
    ├── Tautan Kembali ke Login
    ├── Header Judul & Deskripsi Alur Reset
    ├── <x-auth-session-status> (Pesan Hijau Status Pengiriman Email Berhasil)
    └── <form method="POST" action="password.email">
         ├── Input Email Kampus Terdaftar
         └── Tombol Submit "Kirim Tautan Reset Sandi"
  ==============================================================================
--}}

<x-guest-layout>
    {{-- Tautan Navigasi Kembali ke Halaman Login --}}
    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-[14px] text-[#86868B] hover:text-[#1D1D1F] font-medium transition-colors mb-10 group">
        <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
        <span>Kembali ke Login</span>
    </a>

    {{-- Judul Halaman & Penjelasan Ringkas --}}
    <div class="mb-10">
        <h1 class="text-[32px] md:text-[36px] font-extrabold tracking-tight text-[#1D1D1F] mb-2 leading-tight">Lupa Sandi?</h1>
        <p class="text-[15px] text-[#86868B] leading-relaxed max-w-sm">
            Tidak masalah. Masukkan alamat email kampus Anda yang terdaftar, dan sistem akan mengirimkan tautan reset kata sandi ke kotak masuk Anda.
        </p>
    </div>

    {{-- Komponen Penampil Pesan Status Sesi (Misal: 'Tautan reset sandi telah dikirim ke email Anda.') --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- 
      FORMULIR PERMINTAAN TAUTAN RESET SANDI:
      - Method : POST
      - Action : route('password.email') ➔ Ditangani oleh PasswordResetLinkController@store
      - @csrf  : Token proteksi CSRF wajib
    --}}
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        {{-- 1. Input Alamat Email Terdaftar --}}
        <div>
            <label for="email" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">Alamat Email Terdaftar</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autofocus class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]" placeholder="nama@kampus.ac.id">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        {{-- Tombol Kirim Permintaan Tautan Reset --}}
        <button type="submit" class="w-full bg-[#1D1D1F] text-white px-8 py-4 rounded-2xl font-semibold hover:bg-gray-800 transition shadow-[0_8px_20px_rgb(0,0,0,0.08)] mt-6 text-[15px] cursor-pointer">
            Kirim Tautan Reset Sandi
        </button>
    </form>
</x-guest-layout>