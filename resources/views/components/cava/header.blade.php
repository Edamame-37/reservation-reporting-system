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
  ELEMEN       : Sticky Header (Tinggi 64px / h-16)
  KEGUNAAN     : Header dalam layout App Shell, posisinya bergeser mengikuti Sidebar.
  CARA KERJA   : Berada di dalam main flex wrapper (sticky top-0 z-40).
-->
<header class="sticky top-0 z-40 bg-white/95 backdrop-blur-sm border-b border-slate-200/80 h-16 shrink-0">
    <div class="h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between">
        {{-- Sisi Kiri: Breadcrumb / Halaman Aktif --}}
        <div class="flex items-center gap-3 sm:gap-4">
            <button type="button" @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900 rounded-lg p-1 transition-colors">
                <span class="material-symbols-outlined text-[24px]">menu</span>
            </button>
            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-900 text-sm sm:text-base leading-tight tracking-tight">{{ $title }}</span>
                </div>
                <span class="text-[10px] sm:text-xs text-slate-500 font-medium">{{ $subtitle }}</span>
            </div>
        </div>

        {{-- Sisi Kanan: Status Kampus, Notifikasi, & Profil --}}
        <div class="flex items-center gap-3 sm:gap-4">
            {{-- Indikator Semester Aktif --}}
            <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200/60 text-xs font-medium text-slate-600">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>TA 2024/2025 Ganjil</span>
            </div>

            @if($showProfile)
                {{-- Profil Pengguna Aktif (Bisa Diklik menuju Pengaturan Profil) --}}
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 pl-2 sm:pl-3 py-1 pr-1 border-l border-slate-200 hover:bg-slate-50 rounded-xl transition cursor-pointer group">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-sm font-semibold text-slate-800 leading-tight group-hover:text-blue-900 transition-colors">{{ $userName }}</span>
                        <span class="text-[11px] text-slate-500 font-mono">{{ $userIdentifier }}</span>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-sm shadow-sm group-hover:scale-105 transition-transform" title="Buka Pengaturan Profil">
                        <span class="material-symbols-outlined text-[18px]">person</span>
                    </div>
                </a>
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
