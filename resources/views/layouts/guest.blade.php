{{-- 
  ==============================================================================
  NAMA FILE      : guest.blade.php
  MODUL          : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
  KODE FITUR     : AUTH-01 & AUTH-02 (Master Layout Khusus Halaman Tamu & Autentikasi)
  ==============================================================================
  DESKRIPSI:
  Master layout khusus untuk halaman-halaman pengunjung luar / tamu (guest):
  - Formulir Masuk SSO (Login)
  - Formulir Registrasi Sivitas Kampus (Register)
  - Formulir Permintaan Tautan Lupa Kata Sandi (Forgot Password)
  - Formulir Masukan Kata Sandi Baru (Reset Password)
  - Formulir Konfirmasi Kata Sandi (Confirm Password)
  - Formulir Notifikasi Verifikasi Email (Verify Email)

  DESAIN & ESTETIKA:
  Mengusung estetika minimalis modern bergaya Apple dengan kartu tengah melengkung
  (rounded-[2.5rem]), bayangan lembut multi-layer, tipografi Inter berkelas tinggi,
  serta latar belakang netral abu-abu terang (#FAFAFC).
  ==============================================================================
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        {{-- Pengaturan Viewport Responsif untuk Ponsel & Desktop --}}
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {{-- CSRF Token untuk Keamanan Request Form HTTP POST --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CAVA - Reservasi') }} | CAVA</title>
        {{-- Ikon Tab Browser Format Ringan WebP --}}
        <link rel="icon" type="image/webp" href="{{ asset('assets/images/logo.webp') }}">

        {{-- Optimasi DNS Handshake & Preconnect Font Google --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        {{-- Google Fonts Inter & Material Symbols Outlined --}}
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">

        {{-- Asset Bundler Vite: Memuat CSS Terkompilasi Tailwind & Script JS --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        {{-- Utilitas CSS Khusus --}}
        <style>
            body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        </style>
    </head>
    <body class="font-sans text-slate-800 antialiased selection:bg-slate-800 selection:text-white bg-[#FAFAFC]">
        {{-- WADAH FLEKSIBEL TERPUSAT: Menengahkan kartu di tengah layar vertikal & horizontal --}}
        <div class="min-h-screen flex items-center justify-center p-4">
            {{-- KARTU PUTIH MINIMALIS BERGAYA APPLE (Apple-esque Card Container) --}}
            <div class="bg-white w-full max-w-[460px] rounded-[2.5rem] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.05)] p-10 sm:p-12 border border-gray-100/80 relative my-4">
                {{-- Injeksi Konten Halaman Autentikasi Spesifik ($slot) --}}
                {{ $slot }}
            </div>
        </div>
    </body>
</html>