{{-- 
  ==============================================================================
  NAMA FILE      : confirm-password.blade.php
  MODUL          : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
  KODE FITUR     : AUTH-01 (Formulir Konfirmasi Kata Sandi untuk Area & Tindakan Sensitif)
  ==============================================================================
  DESKRIPSI:
  Tampilan visual formulir konfirmasi ulang kata sandi saat pengguna aktif hendak mengeksekusi
  tindakan berisiko tinggi atau mengakses rute terlindung middleware 'password.confirm'.
  Mewajibkan verifikasi sandi sebelum timestamp `auth.password_confirmed_at` diterbitkan
  di sesi server oleh ConfirmablePasswordController@store.

  STRUKTUR HIERARKI:
  <x-guest-layout>
    ├── Tautan Kembali
    ├── Header Judul "Area Aman" & Deskripsi Proteksi
    └── <form method="POST" action="password.confirm">
         ├── Input Kata Sandi Pengguna Aktif
         └── Tombol Submit "Konfirmasi Sandi"
  ==============================================================================
--}}

<x-guest-layout>
    {{-- Tautan Navigasi Kembali ke Halaman Sebelumnya --}}
    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-[14px] text-[#86868B] hover:text-[#1D1D1F] font-medium transition-colors mb-10 group">
        <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
        <span>Kembali</span>
    </a>

    {{-- Judul Halaman & Peringatan Area Keamanan Khusus --}}
    <div class="mb-10">
        <h1 class="text-[32px] md:text-[36px] font-extrabold tracking-tight text-[#1D1D1F] mb-2 leading-tight">Area Aman</h1>
        <p class="text-[15px] text-[#86868B] leading-relaxed max-w-sm">
            Ini adalah area aman aplikasi CAVA. Silakan konfirmasikan kata sandi akun Anda sebelum melanjutkan.
        </p>
    </div>

    {{-- 
      FORMULIR KONFIRMASI SANDI:
      - Method : POST
      - Action : route('password.confirm') ➔ Ditangani oleh ConfirmablePasswordController@store
      - @csrf  : Token proteksi CSRF wajib
    --}}
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        {{-- Input Kata Sandi --}}
        <div>
            <label for="password" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">Kata Sandi Akun</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]" placeholder="••••••••">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        {{-- Tombol Konfirmasi --}}
        <button type="submit" class="w-full bg-[#1D1D1F] text-white px-8 py-4 rounded-2xl font-semibold hover:bg-gray-800 transition shadow-[0_8px_20px_rgb(0,0,0,0.08)] mt-6 text-[15px] cursor-pointer">
            Konfirmasi Sandi
        </button>
    </form>
</x-guest-layout>