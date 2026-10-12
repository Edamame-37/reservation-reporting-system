{{-- 
  ==============================================================================
  NAMA FILE      : verify-email.blade.php
  MODUL          : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
  KODE FITUR     : AUTH-01 (Tampilan Antrean Notifikasi Verifikasi Email Sivitas)
  ==============================================================================
  DESKRIPSI:
  Tampilan visual prompt verifikasi alamat email pengguna setelah berhasil mendaftar
  atau ketika mengakses fitur yang mewajibkan email terverifikasi (middleware 'verified').
  Menyajikan penjelasan langkah verifikasi via kotak masuk email, tombol kirim ulang tautan,
  serta opsi untuk keluar (logout) dari sesi saat ini.

  STRUKTUR HIERARKI:
  <x-guest-layout>
    ├── Tautan Kembali ke Beranda
    ├── Header Judul & Instruksi Verifikasi Email
    ├── Banner Sukses Pengiriman Ulang (Status 'verification-link-sent')
    └── <div class="space-y-4">
         ├── <form method="POST" action="verification.send"> (Kirim Ulang Email)
         └── <form method="POST" action="logout"> (Keluar dari Akun)
  ==============================================================================
--}}

<x-guest-layout>
    {{-- Tautan Navigasi Kembali ke Halaman Muka Publik --}}
    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-[14px] text-[#86868B] hover:text-[#1D1D1F] font-medium transition-colors mb-10 group">
        <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
        <span>Kembali ke Beranda</span>
    </a>

    {{-- Judul Halaman & Penjelasan Panduan --}}
    <div class="mb-10">
        <h1 class="text-[32px] md:text-[36px] font-extrabold tracking-tight text-[#1D1D1F] mb-2 leading-tight">Verifikasi Email</h1>
        <p class="text-[15px] text-[#86868B] leading-relaxed max-w-sm">
            Terima kasih telah bergabung! Sebelum memulai, silakan verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan. Jika tidak menerima email tersebut, kami dapat mengirimkannya kembali.
        </p>
    </div>

    {{-- Banner Notifikasi Jika Pengiriman Ulang Tautan Berhasil Dilakukan --}}
    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-100 text-[14px] font-medium">
            Tautan verifikasi baru telah berhasil dikirimkan ke alamat email kampus yang Anda daftarkan.
        </div>
    @endif

    <div class="space-y-4">
        {{-- 1. Formulir Kirim Ulang Email Verifikasi (EmailVerificationNotificationController@store) --}}
        <form method="POST" action="{{ route('verification.send') }}" class="w-full">
            @csrf
            <button type="submit" class="w-full bg-[#1D1D1F] text-white px-8 py-4 rounded-2xl font-semibold hover:bg-gray-800 transition shadow-[0_8px_20px_rgb(0,0,0,0.08)] text-[15px] cursor-pointer">
                Kirim Ulang Email Verifikasi
            </button>
        </form>

        {{-- 2. Formulir Logout (AuthenticatedSessionController@destroy) --}}
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="w-full bg-white text-[#1D1D1F] px-8 py-4 rounded-2xl font-semibold hover:bg-gray-50 border border-gray-200 transition text-[15px] cursor-pointer">
                Keluar dari Akun
            </button>
        </form>
    </div>
</x-guest-layout>