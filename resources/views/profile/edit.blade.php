{{-- 
====================================================================================================
SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
MODUL 17: MANAJEMEN AKUN PROFIL PENGGUNA (PROFILE SETTINGS & PASSWORD)
BERKAS: resources/views/profile/edit.blade.php
====================================================================================================

DESKRIPSI BERKAS:
Halaman Induk Pengaturan Profil Akun dan Keamanan Pengguna CAVA.
Menyediakan wadah modular yang merangkum formulir pembaruan data identitas dasar, ubah kata sandi,
serta opsi penghapusan akun permanen. Menggunakan komponen dinamis `<x-dynamic-component>` sehingga
secara otomatis menyesuaikan master layout sesuai peran pengguna yang sedang login.

PERAN ARSITEKTURAL DALAM SISTEM CAVA:
1. Resolusi Tata Letak Dinamis (Dynamic Multi-Role Layout):
   Mendeteksi peran pengguna yang sedang aktif via Spatie RBAC:
   - Jika memiliki role 'admin'    -> Memuat komponen master layout `admin-layout`.
   - Jika memiliki role 'petugas'  -> Memuat komponen master layout `petugas-layout`.
   - Peran sivitas lainnya         -> Memuat komponen master layout default `app-layout`.
2. Struktur Modular Bento 2-Kolom:
   Membagi panel pengaturan menjadi tata letak 2 kolom di layar desktop (grid-cols-2):
   - Kolom Kiri: Form Informasi Dasar & Zona Bahaya Penghapusan Akun.
   - Kolom Kanan: Form Perubahan Kata Sandi demi keamanan terisolasi.
3. Inklusi Parsial Terpisah (@include):
   Memisahkan tanggung jawab visual formulir ke dalam file parsial independen di folder `profile.partials/`.
====================================================================================================
--}}

{{--
  PENENTUAN MASTER LAYOUT SECARA DINAMIS:
  Memeriksa role pengguna saat ini untuk membungkus halaman profil dalam sidebar dan header yang relevan.
--}}
@php
    $layout = 'app-layout';
    if(auth()->user()->hasRole('admin')) {
        $layout = 'admin-layout';
    } elseif(auth()->user()->hasRole('petugas')) {
        $layout = 'petugas-layout';
    }
@endphp

{{--
  KOMPONEN DINAMIS LARAVEL BLADE:
  Syntax: <x-dynamic-component :component="$layout" title="...">
  Merender komponen layout secara dinamis ('admin-layout', 'petugas-layout', atau 'app-layout').
--}}
<x-dynamic-component :component="$layout" title="Pengaturan Profil Akun">

    {{-- Header Halaman & Breadcrumb Navigasi --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
            <a href="{{ url('/dashboard') }}" class="hover:text-slate-900 transition">Dasbor</a>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-slate-900">Manajemen Profil</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Profil & Keamanan Akun</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Perbarui informasi identitas profil dan ubah kata sandi untuk menjaga keamanan akun Anda.</p>
    </div>

    {{-- Grid 2-Kolom Modular Bento UI --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        {{-- Kolom Kiri: Informasi Profil & Zona Bahaya Penghapusan Akun --}}
        <div class="space-y-6">
            {{-- Kartu Bento 1: Informasi Dasar (Nama & Email) --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
                @include('profile.partials.update-profile-information-form')
            </div>

            {{-- Kartu Bento 2: Zona Bahaya (Penghapusan Akun Permanen) --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-xs border-l-4 border-l-rose-500">
                @include('profile.partials.delete-user-form')
            </div>
        </div>

        {{-- Kolom Kanan: Pengaturan Kata Sandi Akun --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
            @include('profile.partials.update-password-form')
        </div>
    </div>
</x-dynamic-component>
