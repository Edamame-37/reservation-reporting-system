{{-- 
  ==============================================================================
  NAMA FILE      : reset-password.blade.php
  MODUL          : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
  KODE FITUR     : AUTH-01 (Formulir Pembaruan Kata Sandi Baru via Token Kriptografis)
  ==============================================================================
  DESKRIPSI:
  Tampilan visual formulir pengisian kata sandi baru bagi pengguna yang membuka tautan
  reset kata sandi dari email mereka.
  Secara otomatis mengikat (bind) token rahasia dari route parameter dan alamat email
  pemohon, lalu memvalidasi masukan kata sandi baru beserta konfirmasinya sebelum
  disimpan ke database oleh NewPasswordController@store.

  STRUKTUR HIERARKI:
  <x-guest-layout>
    ├── Tautan Kembali ke Login
    ├── Header Judul & Deskripsi Pemulihan Akses
    └── <form method="POST" action="password.store">
         ├── Input Hidden Token Rahasia
         ├── Input Alamat Email Terkait
         ├── Input Kata Sandi Baru (Min. 8 Karakter)
         ├── Input Konfirmasi Kata Sandi Baru
         └── Tombol Submit "Reset Kata Sandi"
  ==============================================================================
--}}

<x-guest-layout>
    {{-- Tautan Navigasi Kembali ke Halaman Login --}}
    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-[14px] text-[#86868B] hover:text-[#1D1D1F] font-medium transition-colors mb-10 group">
        <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
        <span>Kembali ke Login</span>
    </a>

    {{-- Judul Halaman & Subjudul --}}
    <div class="mb-10">
        <h1 class="text-[32px] md:text-[36px] font-extrabold tracking-tight text-[#1D1D1F] mb-2 leading-tight">Reset Sandi</h1>
        <p class="text-[15px] text-[#86868B] leading-relaxed max-w-sm">Silakan atur kata sandi baru Anda untuk memulihkan akses ke sistem.</p>
    </div>

    {{-- 
      FORMULIR PENETAPAN KATA SANDI BARU:
      - Method : POST
      - Action : route('password.store') ➔ Ditangani oleh NewPasswordController@store
      - @csrf  : Token proteksi CSRF wajib
    --}}
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        {{-- 1. Input Hidden Token Reset (Diambil dari Route Parameter {token}) --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- 2. Input Alamat Email Terkait --}}
        <div>
            <label for="email" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">Email Sivitas</label>
            {{-- Mengisi otomatis email dari query string ($request->email) jika tersedia --}}
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required maxlength="255" autofocus autocomplete="username" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        {{-- 3. Input Kata Sandi Baru --}}
        <div>
            <label for="password" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">Kata Sandi Baru</label>
            <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]" placeholder="Minimal 8 karakter">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        {{-- 4. Input Konfirmasi Kata Sandi Baru --}}
        <div>
            <label for="password_confirmation" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">Konfirmasi Kata Sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]" placeholder="Ulangi kata sandi">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        {{-- Tombol Eksekusi Reset Kata Sandi --}}
        <button type="submit" class="w-full bg-[#1D1D1F] text-white px-8 py-4 rounded-2xl font-semibold hover:bg-gray-800 transition shadow-[0_8px_20px_rgb(0,0,0,0.08)] mt-6 text-[15px] cursor-pointer">
            Simpan Kata Sandi Baru
        </button>
    </form>
</x-guest-layout>