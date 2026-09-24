{{-- 
  NAMA FILE      : header.blade.php
  FUNGSIONALITAS : Header Tetap Universal (Modern Single-Bar Masthead 64px)
  DESKRIPSI      : Menampilkan logo CAVA, nama sistem, indikator semester aktif, notifikasi cepat, dan profil pengguna aktif tanpa bilah ganda yang berantakan.
  CARA KERJA     : Menerima props showProfile, userName, userRole, userIdentifier, title, dan subtitle. Menggunakan Tailwind CSS minimalis.
--}}

@props([
    'showProfile' => true,
    'userName' => 'Dr. Ir. Hendra Prasetyo',
    'userRole' => 'Petugas Sarpras Zona A',
    'userIdentifier' => 'NIP 198402112009121003',
    'title' => 'CAVA',
    'subtitle' => 'Campus Venue Access'
])

<!-- 
  ELEMEN       : Modern Single-Bar Fixed Header (Tinggi 64px / h-16)
  KEGUNAAN     : Menyediakan navigasi atas yang ramping, bersih, dan hemat ruang vertikal.
  CARA KERJA   : Menempel di atas layar (sticky top-0 z-40) dengan efek blur halus saat scrolling.
-->
<header class="fixed top-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-sm border-b border-slate-200/80 h-16">
    <div class="h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between">
        {{-- Sisi Kiri: Brand & Nama Sistem --}}
        <div class="flex items-center gap-4">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-lg shadow-sm group-hover:bg-slate-800 transition-colors">
                    <span class="material-symbols-outlined text-[22px]">apartment</span>
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-900 text-base leading-tight tracking-tight group-hover:text-blue-900 transition-colors">{{ $title }}</span>
                    </div>
                    <span class="text-xs text-slate-500 font-medium">{{ $subtitle }}</span>
                </div>
            </a>
        </div>

        {{-- Sisi Kanan: Status Kampus, Notifikasi, & Profil --}}
        <div class="flex items-center gap-3 sm:gap-4">
            {{-- Indikator Semester Aktif --}}
            <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200/60 text-xs font-medium text-slate-600">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>TA 2024/2025 Ganjil</span>
            </div>

            @if($showProfile)
                {{-- Profil Pengguna Aktif --}}
                <div class="flex items-center gap-3 pl-2 sm:pl-3 border-l border-slate-200">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-sm font-semibold text-slate-800 leading-tight">{{ $userName }}</span>
                        <span class="text-[11px] text-slate-500 font-mono">{{ $userIdentifier }}</span>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-sm shadow-sm" title="{{ $userName }}">
                        <span class="material-symbols-outlined text-[18px]">person</span>
                    </div>
                </div>
            @else
                {{-- Tombol Masuk SSO untuk Pengunjung --}}
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-slate-900 text-white text-xs sm:text-sm font-medium hover:bg-slate-800 transition shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">login</span>
                        <span>Masuk SSO</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</header>
