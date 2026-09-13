{{-- 
  NAMA FILE      : header.blade.php
  FUNGSIONALITAS : Header Tetap Atas Universal (Fixed Global Masthead)
  DESKRIPSI      : Menampilkan sub-bar indikator sistem + bar utama identitas aplikasi CAVA, live status ketersediaan ruang, dan profil pengguna aktif.
  CARA KERJA     : Props mengatur visibilitas profil pengguna (showProfile), nama, peran, dan NIP/NIM.
--}}

@props([
    'showProfile' => true,
    'userName' => 'Dr. Ir. Hendra Prasetyo',
    'userRole' => 'Petugas Sarpras Zona A',
    'userIdentifier' => 'NIP 198402112009121003',
    'title' => 'CAVA - Campus Venue Access',
    'subtitle' => 'Sistem Otomasi Reservasi Fasilitas Terpadu'
])

<!-- 
  ELEMEN       : Universal Fixed Top Header (Tinggi Total 100px: Sub-bar 36px + Main Bar 64px)
  KEGUNAAN     : Tetap berada di bagian paling atas layar (z-50) saat pengguna melakukan scroll.
-->
<header class="fixed top-0 left-0 right-0 z-50 bg-surface-container-lowest border-b border-outline-variant">
    {{-- Sub-bar Atas (Tinggi 36px / h-9) --}}
    <div class="h-9 bg-primary-container text-on-primary px-margin-lg flex items-center justify-between">
        <div class="flex items-center gap-space-md">
            <span class="font-data-mono text-[11px] text-on-primary-container flex items-center gap-1.5 font-semibold">
                <span class="w-2 h-2 rounded-full bg-secondary-fixed animate-pulse"></span>
                SIM-SARPRAS CLOUD ENGINE ONLINE
            </span>
        </div>
        <div class="flex items-center gap-space-lg">
            <span class="font-label-sm text-label-sm text-on-primary-container font-data-mono">TA 2024/2025 GANJIL</span>
        </div>
    </div>

    {{-- Main Bar Header (Tinggi 64px / h-16) --}}
    <div class="h-16 px-margin-lg flex items-center justify-between bg-surface-container-lowest">
        {{-- Sisi Kiri: Logo & Identitas Brand --}}
        <div class="flex items-center gap-space-xl">
            <a href="{{ url('/') }}" class="flex items-center gap-space-md group">
                <div class="w-10 h-10 rounded-lg bg-primary text-on-primary flex items-center justify-center font-bold text-lg shadow-sm group-hover:bg-primary-container transition-colors">
                    <span class="material-symbols-outlined text-[24px]">apartment</span>
                </div>
                <div class="flex flex-col">
                    <span class="font-headline-sm text-headline-sm text-primary tracking-tight font-bold group-hover:text-primary-container transition-colors">{{ $title }}</span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">{{ $subtitle }}</span>
                </div>
            </a>
        </div>

        {{-- Sisi Kanan: Status Ruang Hari Ini & Profil --}}
        <div class="flex items-center gap-space-lg">
            <div class="hidden sm:flex items-center gap-space-md bg-surface-container-low px-space-md py-1 rounded-lg border border-outline-variant shadow-sm">
                <span class="font-label-sm text-label-sm text-on-surface-variant">STATUS RUANG HARI INI:</span>
                <span class="font-label-sm text-label-sm text-secondary font-bold">42 TERSEDIA</span>
                <span class="text-outline">|</span>
                <span class="font-label-sm text-label-sm text-on-surface-variant font-bold">18 TERPAKAI</span>
            </div>

            @if($showProfile)
                <div class="flex items-center gap-space-md">
                    <div class="flex flex-col text-right">
                        <span class="font-label-lg text-label-lg text-on-surface font-semibold">{{ $userName }}</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant font-data-mono">{{ $userIdentifier }}</span>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center shadow-sm">
                        <span class="material-symbols-outlined text-on-primary text-[20px]">person</span>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-space-sm">
                    <a href="{{ route('login') }}" class="px-space-md py-1.5 rounded-lg bg-primary text-on-primary hover:bg-primary-container transition-colors font-label-md text-label-md flex items-center gap-1 shadow-sm font-semibold">
                        <span class="material-symbols-outlined text-[16px]">login</span>
                        Masuk SSO
                    </a>
                </div>
            @endif
        </div>
    </div>
</header>
